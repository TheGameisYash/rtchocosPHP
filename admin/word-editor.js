/* admin/word-editor.js - Standard Continuous Document (Word-Style) Rich Text Editor
   Features: Freeform block reordering, Drag & Drop with gold insertion line,
   Caret-preserved smart block insertion after paragraphs,
   Interactive Image corner resize handles & presets,
   Interactive Table column drag-to-resize & table width controls,
   Move Up / Move Down buttons, and Clean HTML serialization.
*/

(function () {
    'use strict';

    let editorDoc = null;
    let hiddenContent = null;
    let formatSelect = null;
    let charCountEl = null;
    let wordCountEl = null;
    let readTimeEl = null;
    let autosaveIndicator = null;

    // Active selection & caret tracking
    let savedSelectionRange = null;
    let savedActiveBlock = null;

    // Context references for floating toolbars
    let currentActiveTable = null;
    let currentActiveCell = null;
    let currentActiveFigure = null;

    // Drag-and-drop state
    let currentDraggedBlock = null;

    // =========================================================================
    // 1. INITIALIZATION
    // =========================================================================
    window.initWordEditor = function (initialContent) {
        editorDoc = document.getElementById('wordEditorDoc');
        hiddenContent = document.getElementById('content');
        formatSelect = document.getElementById('formatBlockSelect');
        charCountEl = document.getElementById('char-count');
        wordCountEl = document.getElementById('word-count');
        readTimeEl = document.getElementById('read-time-calc');
        autosaveIndicator = document.getElementById('autosaveIndicator');

        if (!editorDoc) return;

        // Convert initial content (Markdown or HTML) to clean editable HTML
        const html = convertToEditableHtml(initialContent || '');
        editorDoc.innerHTML = html.trim() || '<p><br></p>';

        // Attach interactive controls to existing figures & tables
        attachAllControls();

        // Set initial hidden input value (cleaned)
        if (hiddenContent) {
            hiddenContent.value = cleanHtmlForSave(editorDoc.innerHTML);
        }

        // Enforce standard paragraph creation on Enter
        try {
            document.execCommand('defaultParagraphSeparator', false, 'p');
        } catch (e) { }

        // Setup event listeners
        setupEditorEvents();
        setupToolbarEvents();
        setupImageUploadEvents();
        updateStats();
        updateToolbarState();

        // Autosave recovery check
        checkAutosaveRecovery();
    };

    // =========================================================================
    // 2. CARET & SELECTION TRACKING
    // =========================================================================
    function getDirectBlockChild(node) {
        if (!node || !editorDoc) return null;
        let curr = node.nodeType === 3 ? node.parentNode : node;
        while (curr && curr.parentNode !== editorDoc) {
            if (curr.parentNode === document.body || !curr.parentNode) return null;
            curr = curr.parentNode;
        }
        return (curr && curr.parentNode === editorDoc) ? curr : null;
    }

    function saveCurrentCaret() {
        if (!editorDoc) return;
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            if (editorDoc.contains(range.commonAncestorContainer)) {
                savedSelectionRange = range.cloneRange();
                const block = getDirectBlockChild(range.startContainer);
                if (block) {
                    savedActiveBlock = block;
                }
            }
        }
    }

    function placeCaretAtEnd(el) {
        if (!el) return;
        el.focus();
        if (typeof window.getSelection !== "undefined" && typeof document.createRange !== "undefined") {
            const range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    function flashElement(el) {
        if (!el) return;
        el.classList.remove('just-moved');
        void el.offsetWidth; // trigger reflow
        el.classList.add('just-moved');
        setTimeout(() => {
            if (el) el.classList.remove('just-moved');
        }, 1200);
    }

    // =========================================================================
    // 3. SMART BLOCK INSERTION (After Paragraph / At Caret)
    // =========================================================================
    function insertBlockElement(elementOrHtml) {
        if (!editorDoc) return;
        editorDoc.focus();

        let elem = null;
        if (typeof elementOrHtml === 'string') {
            const temp = document.createElement('div');
            temp.innerHTML = elementOrHtml.trim();
            elem = temp.firstElementChild;
        } else {
            elem = elementOrHtml;
        }
        if (!elem) return;

        // Determine target block
        let targetBlock = savedActiveBlock;
        if (!targetBlock || !editorDoc.contains(targetBlock)) {
            const sel = window.getSelection();
            if (sel && sel.rangeCount > 0) {
                targetBlock = getDirectBlockChild(sel.anchorNode);
            }
        }

        const newParagraph = document.createElement('p');
        newParagraph.innerHTML = '<br>';

        if (targetBlock && editorDoc.contains(targetBlock)) {
            const isBlank = targetBlock.tagName === 'P' && (targetBlock.innerHTML.trim() === '<br>' || targetBlock.innerText.trim() === '');
            if (isBlank) {
                // Replace empty paragraph with the block element
                editorDoc.insertBefore(elem, targetBlock);
                targetBlock.remove();
                editorDoc.insertBefore(newParagraph, elem.nextSibling);
            } else {
                // Insert immediately AFTER the target paragraph!
                editorDoc.insertBefore(elem, targetBlock.nextSibling);
                editorDoc.insertBefore(newParagraph, elem.nextSibling);
            }
        } else {
            // Append to document
            editorDoc.appendChild(elem);
            editorDoc.appendChild(newParagraph);
        }

        // Attach controls to newly inserted element
        if (elem.tagName === 'FIGURE' || elem.classList.contains('blog-figure')) {
            attachFigureControls(elem);
            deselectAllBlocks();
            elem.classList.add('is-selected');
            currentActiveFigure = elem;
            currentActiveTable = null;
            currentActiveCell = null;
            const imgToolbar = document.getElementById('floatingImageToolbar');
            if (imgToolbar) positionFloatingToolbar(imgToolbar, elem);
        } else if (elem.classList.contains('table-responsive-wrapper')) {
            attachTableControls(elem);
            const tbl = elem.querySelector('table');
            if (tbl) {
                deselectAllBlocks();
                elem.classList.add('is-selected');
                currentActiveTable = tbl;
                currentActiveCell = tbl.querySelector('td') || tbl.querySelector('th');
                currentActiveFigure = null;
                const tblToolbar = document.getElementById('floatingTableToolbar');
                if (tblToolbar) positionFloatingToolbar(tblToolbar, tbl);
            }
        }

        // Smooth scroll into view & flash
        elem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        flashElement(elem);

        // Place caret in the following paragraph so author can type immediately
        placeCaretAtEnd(newParagraph);
        savedActiveBlock = newParagraph;

        syncContent();
    }

    // =========================================================================
    // 4. ATTACHING INTERACTIVE CONTROLS (Handles, Resizers, Listeners)
    // =========================================================================
    function attachAllControls() {
        if (!editorDoc) return;
        editorDoc.querySelectorAll('figure.blog-figure').forEach(attachFigureControls);
        editorDoc.querySelectorAll('.table-responsive-wrapper').forEach(attachTableControls);
    }
    window.attachAllControls = attachAllControls;
    window.attachFigureControls = attachFigureControls;
    window.attachTableControls = attachTableControls;

    function deselectAllBlocks() {
        if (!editorDoc) return;
        editorDoc.querySelectorAll('.is-selected').forEach(el => el.classList.remove('is-selected'));
    }

    // Attach Figure Drag & Resize Controls
    function attachFigureControls(figure) {
        if (!figure || figure.dataset.controlsAttached === 'true') return;
        figure.dataset.controlsAttached = 'true';
        figure.setAttribute('contenteditable', 'false');

        if (!figure.style.maxWidth) {
            figure.style.maxWidth = '100%';
        }

        // 1. In-Canvas Attached Figure Action Topbar
        let topbar = figure.querySelector('.editor-figure-topbar');
        if (!topbar) {
            topbar = document.createElement('div');
            topbar.className = 'editor-figure-topbar';
            topbar.setAttribute('contenteditable', 'false');
            topbar.innerHTML = `
                <div class="editor-block-drag-handle" draggable="true" title="Drag to reorder image anywhere in article">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor">
                        <circle cx="9" cy="5" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="9" cy="19" r="2"/>
                        <circle cx="15" cy="5" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="15" cy="19" r="2"/>
                    </svg>
                    <span>Move</span>
                </div>
                <div class="topbar-actions">
                    <button type="button" class="topbar-btn" data-action="moveUp" title="Move Up One Paragraph">&uarr;</button>
                    <button type="button" class="topbar-btn" data-action="moveDown" title="Move Down One Paragraph">&darr;</button>
                    <span class="topbar-sep"></span>
                    <button type="button" class="topbar-btn" data-action="alignLeft" title="Float Left with text wrap">Left</button>
                    <button type="button" class="topbar-btn" data-action="alignCenter" title="Center Block">Center</button>
                    <button type="button" class="topbar-btn" data-action="alignRight" title="Float Right with text wrap">Right</button>
                    <button type="button" class="topbar-btn" data-action="alignWide" title="Full Bleed Wide">Wide</button>
                    <span class="topbar-sep"></span>
                    <button type="button" class="topbar-btn" data-action="size25" title="Small (25%)">25%</button>
                    <button type="button" class="topbar-btn" data-action="size33" title="One-Third (33%)">33%</button>
                    <button type="button" class="topbar-btn" data-action="size50" title="Medium (50%)">50%</button>
                    <button type="button" class="topbar-btn" data-action="size75" title="Large (75%)">75%</button>
                    <button type="button" class="topbar-btn" data-action="size100" title="Full Width (100%)">100%</button>
                    <span class="topbar-sep"></span>
                    <button type="button" class="topbar-btn" data-action="insertParagraphBelow" title="Insert paragraph below">+ Para</button>
                    <button type="button" class="topbar-btn" data-action="editCaption" title="Edit Caption">Caption</button>
                    <button type="button" class="topbar-btn topbar-btn-danger" data-action="deleteImage" title="Delete Image">&times;</button>
                </div>
            `;

            topbar.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();

                currentActiveFigure = figure;
                deselectAllBlocks();
                figure.classList.add('is-selected');

                const action = btn.getAttribute('data-action');
                if (action.startsWith('size')) {
                    const val = action.replace('size', '') + '%';
                    window.imageAction('setSize', val);
                } else {
                    window.imageAction(action);
                }
                updateFigureTopbarState(figure);
            });

            figure.insertBefore(topbar, figure.firstChild);
        }

        // Setup drag events on the topbar drag handle
        const dragHandle = topbar.querySelector('.editor-block-drag-handle');
        if (dragHandle) {
            setupBlockDragEvents(dragHandle, figure);
        }

        // Also setup drag events on the image itself for natural dragging
        const img = figure.querySelector('img');
        if (img) {
            setupBlockDragEvents(img, figure);
        }

        // 2. Corner & Edge Resize Handles
        if (!figure.querySelector('.editor-resize-handle')) {
            const handlePositions = ['nw', 'ne', 'se', 'sw', 'e', 'w'];
            handlePositions.forEach(pos => {
                const handle = document.createElement('div');
                handle.className = `editor-resize-handle handle-${pos}`;
                handle.dataset.handle = pos;
                setupImageResizeEvents(handle, figure);
                figure.appendChild(handle);
            });

            // Size badge
            const badge = document.createElement('div');
            badge.className = 'editor-size-badge';
            figure.appendChild(badge);
        }

        // 3. Figcaption editable
        const cap = figure.querySelector('figcaption');
        if (cap) {
            cap.setAttribute('contenteditable', 'true');
            cap.addEventListener('input', () => {
                syncContent();
            });
        }

        updateFigureTopbarState(figure);
    }

    function updateFigureTopbarState(figure) {
        if (!figure) return;
        const topbar = figure.querySelector('.editor-figure-topbar');
        if (!topbar) return;

        const isLeft = figure.classList.contains('blog-figure-left');
        const isRight = figure.classList.contains('blog-figure-right');
        const isWide = figure.classList.contains('blog-figure-wide');
        const isCenter = figure.classList.contains('blog-figure-center') || (!isLeft && !isRight && !isWide);

        const btnLeft = topbar.querySelector('[data-action="alignLeft"]');
        const btnCenter = topbar.querySelector('[data-action="alignCenter"]');
        const btnRight = topbar.querySelector('[data-action="alignRight"]');
        const btnWide = topbar.querySelector('[data-action="alignWide"]');

        if (btnLeft) btnLeft.classList.toggle('active', isLeft);
        if (btnCenter) btnCenter.classList.toggle('active', isCenter);
        if (btnRight) btnRight.classList.toggle('active', isRight);
        if (btnWide) btnWide.classList.toggle('active', isWide);

        const currentW = figure.style.width || '100%';
        topbar.querySelectorAll('[data-action^="size"]').forEach(btn => {
            const sizeVal = btn.getAttribute('data-action').replace('size', '') + '%';
            btn.classList.toggle('active', currentW === sizeVal);
        });
    }

    // Interactive Image Drag-to-Resize Mechanics
    function setupImageResizeEvents(handle, figure) {
        handle.addEventListener('mousedown', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const dir = handle.dataset.handle;
            const startX = e.clientX;
            const startWidth = figure.getBoundingClientRect().width;
            const editorWidth = editorDoc.clientWidth;
            const isCenter = figure.classList.contains('blog-figure-center') ||
                (!figure.classList.contains('blog-figure-left') && !figure.classList.contains('blog-figure-right'));
            const badge = figure.querySelector('.editor-size-badge');

            figure.classList.add('is-resizing');

            function onMouseMove(moveEvent) {
                moveEvent.preventDefault();
                const dx = moveEvent.clientX - startX;
                let newWidth = startWidth;

                if (dir === 'se' || dir === 'e' || dir === 'ne') {
                    newWidth = isCenter ? (startWidth + dx * 2) : (startWidth + dx);
                } else if (dir === 'sw' || dir === 'w' || dir === 'nw') {
                    newWidth = isCenter ? (startWidth - dx * 2) : (startWidth - dx);
                }

                // Bounds
                newWidth = Math.max(120, Math.min(newWidth, editorWidth));
                const pct = Math.min(100, Math.max(15, Math.round((newWidth / editorWidth) * 100)));

                figure.style.width = pct + '%';
                figure.style.maxWidth = '100%';

                if (badge) {
                    badge.style.display = 'block';
                    badge.innerText = `${pct}% · ${Math.round(newWidth)}px`;
                }

                updateFigureTopbarState(figure);
                const imgToolbar = document.getElementById('floatingImageToolbar');
                if (imgToolbar) positionFloatingToolbar(imgToolbar, figure);
            }

            function onMouseUp() {
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
                figure.classList.remove('is-resizing');
                if (badge) {
                    setTimeout(() => { if (badge) badge.style.display = 'none'; }, 900);
                }
                updateFigureTopbarState(figure);
                syncContent();
            }

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        });
    }

    // Attach Table Drag & Column Resize Controls
    function attachTableControls(wrapper) {
        if (!wrapper || wrapper.dataset.controlsAttached === 'true') return;
        wrapper.dataset.controlsAttached = 'true';
        wrapper.setAttribute('contenteditable', 'false');

        // 1. In-Canvas Attached Table Action Topbar
        let topbar = wrapper.querySelector('.editor-table-topbar');
        if (!topbar) {
            topbar = document.createElement('div');
            topbar.className = 'editor-table-topbar';
            topbar.setAttribute('contenteditable', 'false');
            topbar.innerHTML = `
                <div class="editor-block-drag-handle editor-table-drag-handle" draggable="true" title="Drag to reorder table anywhere in article">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor">
                        <circle cx="9" cy="5" r="2"/><circle cx="9" cy="12" r="2"/><circle cx="9" cy="19" r="2"/>
                        <circle cx="15" cy="5" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="15" cy="19" r="2"/>
                    </svg>
                    <span>Move Table</span>
                </div>
                <div class="topbar-actions">
                    <button type="button" class="topbar-btn" data-action="moveUp" title="Move Up One Paragraph">&uarr;</button>
                    <button type="button" class="topbar-btn" data-action="moveDown" title="Move Down One Paragraph">&darr;</button>
                    <span class="topbar-sep"></span>
                    <button type="button" class="topbar-btn" data-action="tableWidth70" title="Table Width 70%">70%</button>
                    <button type="button" class="topbar-btn" data-action="tableWidth85" title="Table Width 85%">85%</button>
                    <button type="button" class="topbar-btn" data-action="tableWidth100" title="Table Width 100%">100%</button>
                    <button type="button" class="topbar-btn" data-action="equalCols" title="Distribute columns equally">Equal Cols</button>
                    <span class="topbar-sep"></span>
                    <button type="button" class="topbar-btn" data-action="addRowBelow" title="Add Row Below">+ Row</button>
                    <button type="button" class="topbar-btn" data-action="addColRight" title="Add Column Right">+ Col</button>
                    <button type="button" class="topbar-btn" data-action="insertParagraphBelow" title="Insert paragraph below">+ Para</button>
                    <button type="button" class="topbar-btn topbar-btn-danger" data-action="deleteTable" title="Delete Table">&times;</button>
                </div>
            `;

            topbar.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;
                e.preventDefault();
                e.stopPropagation();

                const table = wrapper.querySelector('table');
                if (table) currentActiveTable = table;

                deselectAllBlocks();
                wrapper.classList.add('is-selected');

                const action = btn.getAttribute('data-action');
                if (action.startsWith('tableWidth')) {
                    const val = action.replace('tableWidth', '') + '%';
                    window.tableAction('setWidth', val);
                } else {
                    window.tableAction(action);
                }
            });

            wrapper.insertBefore(topbar, wrapper.firstChild);
        }

        // Setup drag events on the table drag handle
        const dragHandle = topbar.querySelector('.editor-block-drag-handle');
        if (dragHandle) {
            setupBlockDragEvents(dragHandle, wrapper);
        }

        // 2. Table Width Resize Handle (bottom-right corner)
        if (!wrapper.querySelector('.editor-table-resize-handle')) {
            const resizeHandle = document.createElement('div');
            resizeHandle.className = 'editor-table-resize-handle';
            resizeHandle.setAttribute('title', 'Drag to resize table width');
            resizeHandle.innerHTML = '&#8690;';
            setupTableWidthResizeEvents(resizeHandle, wrapper);
            wrapper.appendChild(resizeHandle);
        }

        const table = wrapper.querySelector('table');
        if (table) {
            table.setAttribute('contenteditable', 'true');
            setupTableColumnResizing(table, wrapper);
        }
    }

    // Table Overall Width Resize by Drag Handle
    function setupTableWidthResizeEvents(handle, wrapper) {
        handle.addEventListener('mousedown', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const table = wrapper.querySelector('table');
            if (!table) return;

            const startX = e.clientX;
            const startWidth = table.getBoundingClientRect().width;
            const editorWidth = editorDoc.clientWidth;

            let badge = wrapper.querySelector('.editor-size-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'editor-size-badge';
                wrapper.appendChild(badge);
            }

            function onMouseMove(moveEvent) {
                moveEvent.preventDefault();
                const dx = moveEvent.clientX - startX;
                const newWidth = Math.max(200, Math.min(startWidth + dx, editorWidth));
                const pct = Math.min(100, Math.max(30, Math.round((newWidth / editorWidth) * 100)));

                table.style.width = pct + '%';
                if (badge) {
                    badge.style.display = 'block';
                    badge.innerText = `${pct}% · ${Math.round(newWidth)}px`;
                }

                const tblToolbar = document.getElementById('floatingTableToolbar');
                if (tblToolbar) positionFloatingToolbar(tblToolbar, table);
            }

            function onMouseUp() {
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
                if (badge) {
                    setTimeout(() => { if (badge) badge.style.display = 'none'; }, 900);
                }
                syncContent();
            }

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        });
    }

    // Interactive Column Drag-to-Resize
    function setupTableColumnResizing(table, wrapper) {
        table.style.tableLayout = 'fixed';
        let isResizing = false;
        let startX = 0;
        let startWidth = 0;
        let activeCell = null;
        let colIndex = -1;

        table.addEventListener('mousemove', (e) => {
            if (isResizing) return;
            const cell = e.target.closest('th, td');
            if (!cell || !table.contains(cell)) {
                table.style.cursor = '';
                activeCell = null;
                return;
            }

            const rect = cell.getBoundingClientRect();
            const isRightBorder = (e.clientX >= rect.right - 8 && e.clientX <= rect.right + 2);
            const row = cell.closest('tr');
            const isLastCol = row && (cell === row.lastElementChild);

            if (isRightBorder && !isLastCol) {
                table.style.cursor = 'col-resize';
                activeCell = cell;
            } else {
                table.style.cursor = '';
                activeCell = null;
            }
        });

        table.addEventListener('mousedown', (e) => {
            if (!activeCell || table.style.cursor !== 'col-resize') return;
            e.preventDefault();
            e.stopPropagation();

            isResizing = true;
            startX = e.clientX;
            startWidth = activeCell.getBoundingClientRect().width;
            const row = activeCell.closest('tr');
            colIndex = Array.from(row.children).indexOf(activeCell);

            // Vertical guide line
            let guide = wrapper.querySelector('.editor-col-guide');
            if (!guide) {
                guide = document.createElement('div');
                guide.className = 'editor-col-guide';
                wrapper.appendChild(guide);
            }
            guide.style.display = 'block';
            guide.style.left = (activeCell.offsetLeft + activeCell.offsetWidth) + 'px';

            function onMouseMove(moveEvent) {
                moveEvent.preventDefault();
                const dx = moveEvent.clientX - startX;
                const newW = Math.max(40, startWidth + dx);

                table.querySelectorAll('tr').forEach(r => {
                    if (r.children[colIndex]) {
                        r.children[colIndex].style.width = newW + 'px';
                    }
                });

                if (guide) {
                    guide.style.left = (activeCell.offsetLeft + activeCell.offsetWidth) + 'px';
                }
            }

            function onMouseUp() {
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
                isResizing = false;
                table.style.cursor = '';
                if (guide) guide.style.display = 'none';

                // Convert to responsive percentages
                const tblWidth = table.getBoundingClientRect().width;
                const firstRowCells = table.querySelectorAll('tr:first-child > *');
                firstRowCells.forEach(c => {
                    const cellW = c.getBoundingClientRect().width;
                    const pct = Math.max(5, Math.round((cellW / tblWidth) * 100));
                    c.style.width = pct + '%';
                });

                syncContent();
            }

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        });
    }

    // =========================================================================
    // 5. DRAG & DROP BLOCK REORDERING (With Gold Insertion Line)
    // =========================================================================
    function setupBlockDragEvents(dragTrigger, blockElement) {
        dragTrigger.setAttribute('draggable', 'true');

        dragTrigger.addEventListener('dragstart', (e) => {
            e.stopPropagation();
            currentDraggedBlock = blockElement;
            e.dataTransfer.setData('text/plain', 'editor-block');
            e.dataTransfer.effectAllowed = 'move';

            createAndSetDragGhost(e, blockElement);

            blockElement.classList.add('is-dragging');
            setTimeout(() => {
                if (blockElement && currentDraggedBlock === blockElement) {
                    blockElement.style.opacity = '0.35';
                }
            }, 10);
        });

        dragTrigger.addEventListener('dragend', () => {
            if (currentDraggedBlock) {
                currentDraggedBlock.classList.remove('is-dragging');
                currentDraggedBlock.style.opacity = '';
                currentDraggedBlock = null;
            }
            removeDropIndicator();
            removeDragGhost();
        });
    }

    function createAndSetDragGhost(e, blockElement) {
        let ghost = document.getElementById('editorDragGhost');
        if (!ghost) {
            ghost = document.createElement('div');
            ghost.id = 'editorDragGhost';
            ghost.className = 'editor-drag-ghost';
            document.body.appendChild(ghost);
        }
        const isFig = blockElement.tagName === 'FIGURE' || blockElement.classList.contains('blog-figure');
        ghost.innerHTML = isFig
            ? `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Moving Image...</span>`
            : `<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M4 3h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zm0 4h16V5H4v2zm0 4h7v4H4v-4zm9 0h7v4h-7v-4zm-9 6h7v2H4v-2zm9 0h7v2h-7v-2z"/></svg><span>Moving Table...</span>`;

        if (e.dataTransfer && e.dataTransfer.setDragImage) {
            try {
                e.dataTransfer.setDragImage(ghost, 25, 18);
            } catch (err) {}
        }
    }

    function removeDragGhost() {
        const ghost = document.getElementById('editorDragGhost');
        if (ghost) ghost.remove();
    }

    function getClosestDropTarget(clientY) {
        if (!editorDoc) return null;
        const children = Array.from(editorDoc.children).filter(el => 
            el !== currentDraggedBlock &&
            el.id !== 'editorDropLine' &&
            !el.classList.contains('editor-drop-line')
        );
        if (children.length === 0) return null;

        // Check above first child
        const firstRect = children[0].getBoundingClientRect();
        if (clientY < firstRect.top + firstRect.height / 2) {
            return { target: children[0], isAbove: true };
        }

        // Check below last child
        const lastRect = children[children.length - 1].getBoundingClientRect();
        if (clientY > lastRect.bottom - lastRect.height / 2) {
            return { target: children[children.length - 1], isAbove: false };
        }

        // Find closest block based on cursor Y and vertical midpoint
        let closestChild = null;
        let minDistance = Infinity;
        let isAbove = false;

        for (let i = 0; i < children.length; i++) {
            const child = children[i];
            const rect = child.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            const dist = Math.abs(clientY - midY);

            if (dist < minDistance) {
                minDistance = dist;
                closestChild = child;
                isAbove = (clientY < midY);
            }
        }

        return closestChild ? { target: closestChild, isAbove } : null;
    }

    function removeDropIndicator() {
        const existing = document.getElementById('editorDropLine');
        if (existing) existing.remove();
    }

    function showDropIndicator(targetBlock, isAbove) {
        if (!targetBlock || !targetBlock.parentNode) return;

        let indicator = document.getElementById('editorDropLine');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'editorDropLine';
            indicator.className = 'editor-drop-line';
            indicator.setAttribute('contenteditable', 'false');
            indicator.innerHTML = '<span>Drop Here</span>';
        }

        const desiredNextSibling = isAbove ? targetBlock : targetBlock.nextSibling;
        if (indicator.nextSibling !== desiredNextSibling) {
            targetBlock.parentNode.insertBefore(indicator, desiredNextSibling);
        }
    }

    // =========================================================================
    // 6. CONVERT INITIAL CONTENT (Markdown & HTML support)
    // =========================================================================
    function sanitizeHtmlContent(html) {
        if (!html) return '<p><br></p>';
        const temp = document.createElement('div');
        temp.innerHTML = html;

        // 1. Clean redundant or transparent background styles, preserving author colors
        temp.querySelectorAll('*').forEach(el => {
            if (el.style.backgroundColor) {
                const bg = el.style.backgroundColor.toLowerCase().replace(/\s+/g, '');
                if (bg.includes('rgba(0,0,0,0)') || bg === 'transparent') {
                    el.style.backgroundColor = '';
                }
            }
            if (!el.getAttribute('style') || !el.getAttribute('style').trim()) {
                el.removeAttribute('style');
            }
        });

        // 2. Headings: If a heading (h1-h6) has nested block children (like <p>, <div>),
        // flatten/unwrap the children SO THE HEADING ITSELF IS PRESERVED!
        temp.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach(h => {
            const innerBlocks = h.querySelectorAll('p, div, blockquote');
            if (innerBlocks.length > 0) {
                innerBlocks.forEach(inner => {
                    while (inner.firstChild) {
                        inner.parentNode.insertBefore(inner.firstChild, inner);
                    }
                    inner.remove();
                });
            }
        });

        return temp.innerHTML;
    }

    function convertToEditableHtml(raw) {
        if (!raw || !raw.trim()) return '<p><br></p>';

        if (/<(p|div|h[1-6]|ul|ol|blockquote|table|section|figure)/i.test(raw)) {
            return sanitizeHtmlContent(raw);
        }

        raw = raw.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        const blocks = raw.split(/\n\n+/);
        let html = '';

        blocks.forEach(block => {
            block = block.trim();
            if (!block) return;

            // Markdown Table block
            if (block.startsWith('|')) {
                const lines = block.split('\n');
                if (lines.length >= 2) {
                    let tableHtml = '<div class="table-responsive-wrapper" contenteditable="false"><table class="blog-custom-table blog-table-artisan" contenteditable="true" style="width:100%;">';
                    let hasHeader = false;
                    lines.forEach(line => {
                        const trimmed = line.trim().replace(/^\||\|$/g, '');
                        if (!trimmed || /^[:\-\s|]+$/.test(trimmed)) return;
                        const cols = trimmed.split('|');
                        let rowHtml = '<tr>';
                        cols.forEach(col => {
                            const tag = !hasHeader ? 'th' : 'td';
                            rowHtml += `<${tag}>${parseInlineMd(col.trim())}</${tag}>`;
                        });
                        rowHtml += '</tr>';
                        if (!hasHeader) {
                            tableHtml += `<thead>${rowHtml}</thead><tbody>`;
                            hasHeader = true;
                        } else {
                            tableHtml += rowHtml;
                        }
                    });
                    if (hasHeader) tableHtml += '</tbody>';
                    tableHtml += '</table></div><p><br></p>';
                    html += tableHtml;
                    return;
                }
            }

            // Headings
            const hMatch = block.match(/^(#{1,6})\s+(.+)$/);
            if (hMatch) {
                const level = Math.min(6, Math.max(2, hMatch[1].length));
                html += `<h${level}>${parseInlineMd(hMatch[2])}</h${level}>`;
                return;
            }

            // Blockquote / Callout
            if (block.startsWith('> ')) {
                let quoteText = block.split('\n').map(l => l.replace(/^>\s?/, '')).join(' ');
                if (/^\[!(NOTE|TIP|WARNING)\]/i.test(quoteText)) {
                    quoteText = quoteText.replace(/^\[!(NOTE|TIP|WARNING)\]\s*/i, '');
                    html += `<blockquote class="callout"><p>${parseInlineMd(quoteText)}</p></blockquote>`;
                } else {
                    html += `<blockquote><p>${parseInlineMd(quoteText)}</p></blockquote>`;
                }
                return;
            }

            // Unordered List
            if (/^[\*\-]\s+/m.test(block)) {
                const items = block.split('\n')
                    .filter(l => /^[\*\-]\s+/.test(l))
                    .map(l => `<li>${parseInlineMd(l.replace(/^[\*\-]\s+/, ''))}</li>`)
                    .join('');
                html += `<ul>${items}</ul>`;
                return;
            }

            // Ordered List
            if (/^\d+\.\s+/m.test(block)) {
                const items = block.split('\n')
                    .filter(l => /^\d+\.\s+/.test(l))
                    .map(l => `<li>${parseInlineMd(l.replace(/^\d+\.\s+/, ''))}</li>`)
                    .join('');
                html += `<ol>${items}</ol>`;
                return;
            }

            // Code block
            if (block.startsWith('```')) {
                const code = block.replace(/^```[a-z]*\n?/i, '').replace(/```$/, '');
                html += `<pre><code>${escapeHtml(code)}</code></pre>`;
                return;
            }

            // Image markdown: ![alt](url){pos}
            const imgMatch = block.match(/^!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|wide)\})?/);
            if (imgMatch) {
                const alt = imgMatch[1];
                const url = imgMatch[2];
                const pos = imgMatch[3] || 'center';
                html += `<figure class="blog-figure blog-figure-${pos}" style="width:100%; max-width:100%;" contenteditable="false"><img src="${url}" alt="${escapeHtml(alt)}" loading="lazy">${alt ? `<figcaption contenteditable="true">${escapeHtml(alt)}</figcaption>` : ''}</figure><p><br></p>`;
                return;
            }

            // YouTube embed
            if (block.startsWith('{{youtube:') && block.endsWith('}}')) {
                const id = block.replace('{{youtube:', '').replace('}}', '').trim();
                html += createYoutubeEmbedHtml(id);
                return;
            }
            if (block.startsWith('[youtube](') && block.endsWith(')')) {
                const url = block.match(/\[youtube\]\((.*?)\)/)[1];
                const id = extractYoutubeId(url);
                if (id) {
                    html += createYoutubeEmbedHtml(id);
                    return;
                }
            }

            // Divider
            if (block === '---' || block === '***') {
                html += '<hr>';
                return;
            }

            // Normal paragraph
            html += `<p>${parseInlineMd(block)}</p>`;
        });

        return html;
    }

    function parseInlineMd(text) {
        if (!text) return '';
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        text = text.replace(/_([^_]+)_/g, '<em>$1</em>');
        text = text.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
        return text;
    }

    function escapeHtml(str) {
        return (str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function extractYoutubeId(url) {
        if (!url) return null;
        const reg = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const m = url.match(reg);
        return (m && m[2].length === 11) ? m[2] : null;
    }

    function createYoutubeEmbedHtml(id) {
        return `<div class="blog-yt-embed" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;margin:24px 0;" contenteditable="false"><iframe src="https://www.youtube.com/embed/${id}" frameborder="0" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;border-radius:12px;"></iframe></div><p><br></p>`;
    }

    // =========================================================================
    // 7. CLEAN HTML SERIALIZATION (For Database & Frontend)
    // =========================================================================
    function cleanHtmlForSave(rawHtml) {
        if (!rawHtml) return '';
        const temp = document.createElement('div');
        temp.innerHTML = rawHtml;

        // Remove UI helper elements
        temp.querySelectorAll('.editor-figure-topbar, .editor-table-topbar, .editor-block-drag-handle, .editor-resize-handle, .editor-table-resize-handle, .editor-size-badge, .editor-drop-line, .editor-col-guide, .editor-drag-ghost').forEach(el => el.remove());

        // Clean editor-specific classes
        temp.querySelectorAll('.is-selected, .is-dragging, .is-resizing, .just-moved, .drag-over').forEach(el => {
            el.classList.remove('is-selected', 'is-dragging', 'is-resizing', 'just-moved', 'drag-over');
            if (!el.getAttribute('class') || !el.getAttribute('class').trim()) {
                el.removeAttribute('class');
            }
        });

        // Remove data-controls-attached
        temp.querySelectorAll('[data-controls-attached]').forEach(el => {
            el.removeAttribute('data-controls-attached');
        });

        // Remove temporary contenteditable
        temp.querySelectorAll('figure[contenteditable], .table-responsive-wrapper[contenteditable]').forEach(el => {
            el.removeAttribute('contenteditable');
        });

        // Clean empty style attributes
        temp.querySelectorAll('*').forEach(el => {
            if (!el.getAttribute('style') || !el.getAttribute('style').trim()) {
                el.removeAttribute('style');
            }
        });

        // Separate top-level block elements with double newlines for parser/database indexing
        const children = Array.from(temp.children);
        if (children.length > 0) {
            return children.map(c => c.outerHTML.trim()).filter(Boolean).join('\n\n');
        }

        return temp.innerHTML;
    }

    // =========================================================================
    // 8. EDITOR DOM & KEYBOARD EVENTS
    // =========================================================================
    function setupEditorEvents() {
        // Sync content & stats on input with lightweight debounce
        let inputDebounceTimer = null;
        editorDoc.addEventListener('input', () => {
            window.__isEditorDirty = true;
            saveCurrentCaret();
            updateStats();

            clearTimeout(inputDebounceTimer);
            inputDebounceTimer = setTimeout(() => {
                attachAllControls();
                syncContent();
            }, 600);
        });

        const handleInteraction = (e) => {
            saveCurrentCaret();
            updateToolbarState();
            checkFloatingContext(e ? e.target : null);
        };

        editorDoc.addEventListener('keyup', handleInteraction);
        editorDoc.addEventListener('mouseup', handleInteraction);
        editorDoc.addEventListener('click', handleInteraction);

        document.addEventListener('selectionchange', () => {
            if (document.activeElement === editorDoc || editorDoc.contains(document.activeElement)) {
                saveCurrentCaret();
                updateToolbarState();
                checkFloatingContext(null);
            }
        });

        // Click outside editor & floating toolbars closes floating toolbars & deselects
        document.addEventListener('mousedown', (e) => {
            const tableToolbar = document.getElementById('floatingTableToolbar');
            const imageToolbar = document.getElementById('floatingImageToolbar');
            const isInsideTableTb = tableToolbar && tableToolbar.contains(e.target);
            const isInsideImageTb = imageToolbar && imageToolbar.contains(e.target);
            const isInsideEditor = editorDoc.contains(e.target);

            if (!isInsideTableTb && !isInsideEditor) {
                if (tableToolbar) tableToolbar.style.display = 'none';
            }
            if (!isInsideImageTb && !isInsideEditor) {
                if (imageToolbar) imageToolbar.style.display = 'none';
            }
            if (!isInsideEditor && !isInsideTableTb && !isInsideImageTb) {
                deselectAllBlocks();
            }
        });

        // Update floating positions on scroll/resize
        window.addEventListener('scroll', () => {
            if (currentActiveTable && document.body.contains(currentActiveTable)) {
                positionFloatingToolbar(document.getElementById('floatingTableToolbar'), currentActiveTable);
            }
            if (currentActiveFigure && document.body.contains(currentActiveFigure)) {
                positionFloatingToolbar(document.getElementById('floatingImageToolbar'), currentActiveFigure);
            }
        }, { passive: true });

        // Tab key navigation inside table cells
        editorDoc.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                const sel = window.getSelection();
                if (sel && sel.anchorNode) {
                    let cell = sel.anchorNode;
                    if (cell.nodeType === 3) cell = cell.parentNode;
                    while (cell && cell !== editorDoc && cell.tagName !== 'TD' && cell.tagName !== 'TH') {
                        cell = cell.parentNode;
                    }
                    if (cell && (cell.tagName === 'TD' || cell.tagName === 'TH')) {
                        e.preventDefault();
                        const row = cell.closest('tr');
                        const table = cell.closest('table');
                        if (!row || !table) return;

                        const cells = Array.from(table.querySelectorAll('th, td'));
                        const currentIndex = cells.indexOf(cell);

                        if (e.shiftKey) {
                            if (currentIndex > 0) {
                                cells[currentIndex - 1].focus();
                                placeCaretAtEnd(cells[currentIndex - 1]);
                            }
                        } else {
                            if (currentIndex < cells.length - 1) {
                                cells[currentIndex + 1].focus();
                                placeCaretAtEnd(cells[currentIndex + 1]);
                            } else {
                                const tbody = table.querySelector('tbody') || table;
                                const colCount = row.children.length;
                                const newRow = document.createElement('tr');
                                for (let i = 0; i < colCount; i++) {
                                    const td = document.createElement('td');
                                    td.innerHTML = '<br>';
                                    newRow.appendChild(td);
                                }
                                tbody.appendChild(newRow);
                                syncContent();
                                const firstNewCell = newRow.firstElementChild;
                                if (firstNewCell) {
                                    firstNewCell.focus();
                                    placeCaretAtEnd(firstNewCell);
                                }
                            }
                        }
                        return;
                    }
                }
            }

            if ((e.ctrlKey || e.metaKey) && !e.shiftKey) {
                switch (e.key.toLowerCase()) {
                    case 'b':
                        e.preventDefault();
                        execFormat('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        execFormat('italic');
                        break;
                    case 'u':
                        e.preventDefault();
                        execFormat('underline');
                        break;
                    case 'k':
                        e.preventDefault();
                        openLinkModal();
                        break;
                    case 'z':
                        setTimeout(syncContent, 10);
                        break;
                    case 'y':
                        setTimeout(syncContent, 10);
                        break;
                }
            }
        });

        // Paste handling
        editorDoc.addEventListener('paste', (e) => {
            const clipboard = e.clipboardData;
            if (!clipboard) return;

            if (clipboard.files && clipboard.files.length > 0) {
                const file = clipboard.files[0];
                if (file.type.startsWith('image/')) {
                    e.preventDefault();
                    uploadAndInsertImage(file);
                    return;
                }
            }
            setTimeout(() => {
                attachAllControls();
                syncContent();
            }, 20);
        });

        // Dragover & Drop on EditorDoc for block reordering
        editorDoc.addEventListener('dragover', (e) => {
            if (!currentDraggedBlock) {
                // External file drag
                e.preventDefault();
                editorDoc.classList.add('drag-over');
                return;
            }
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const dropInfo = getClosestDropTarget(e.clientY);
            if (dropInfo && dropInfo.target) {
                showDropIndicator(dropInfo.target, dropInfo.isAbove);
            }
        });

        editorDoc.addEventListener('dragleave', (e) => {
            if (!currentDraggedBlock) {
                editorDoc.classList.remove('drag-over');
            }
        });

        editorDoc.addEventListener('drop', (e) => {
            editorDoc.classList.remove('drag-over');

            // Handle internal block drop
            if (currentDraggedBlock) {
                e.preventDefault();
                const indicator = document.getElementById('editorDropLine');
                if (indicator && indicator.parentNode) {
                    indicator.parentNode.insertBefore(currentDraggedBlock, indicator);
                    indicator.remove();

                    flashElement(currentDraggedBlock);
                    currentDraggedBlock.classList.remove('is-dragging');
                    currentDraggedBlock.style.opacity = '';

                    // Ensure there's a paragraph after the dropped block so user can continue writing easily
                    if (!currentDraggedBlock.nextElementSibling || 
                        currentDraggedBlock.nextElementSibling.tagName === 'FIGURE' || 
                        currentDraggedBlock.nextElementSibling.classList.contains('table-responsive-wrapper')) {
                        const newP = document.createElement('p');
                        newP.innerHTML = '<br>';
                        currentDraggedBlock.parentNode.insertBefore(newP, currentDraggedBlock.nextSibling);
                    }

                    // Selection & Floating Toolbar synchronization
                    deselectAllBlocks();
                    currentDraggedBlock.classList.add('is-selected');

                    if (currentDraggedBlock.tagName === 'FIGURE' || currentDraggedBlock.classList.contains('blog-figure')) {
                        currentActiveFigure = currentDraggedBlock;
                        currentActiveTable = null;
                        currentActiveCell = null;
                        updateFigureTopbarState(currentDraggedBlock);
                        const imgToolbar = document.getElementById('floatingImageToolbar');
                        if (imgToolbar) {
                            updateImageToolbarState(currentDraggedBlock);
                            positionFloatingToolbar(imgToolbar, currentDraggedBlock);
                        }
                    } else if (currentDraggedBlock.classList.contains('table-responsive-wrapper')) {
                        const tbl = currentDraggedBlock.querySelector('table');
                        if (tbl) {
                            currentActiveTable = tbl;
                            currentActiveCell = tbl.querySelector('td') || tbl.querySelector('th');
                            currentActiveFigure = null;
                            const tblToolbar = document.getElementById('floatingTableToolbar');
                            if (tblToolbar) {
                                updateTableToolbarState(tbl);
                                positionFloatingToolbar(tblToolbar, tbl);
                            }
                        }
                    }

                    currentDraggedBlock = null;
                    removeDragGhost();
                    syncContent();
                }
                return;
            }

            // Handle external image file drop
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const file = e.dataTransfer.files[0];
                if (file.type.startsWith('image/')) {
                    e.preventDefault();
                    uploadAndInsertImage(file);
                }
            }
        });

        // Ensure form submit cleans & syncs content
        const form = document.getElementById('editorForm');
        if (form) {
            form.addEventListener('submit', () => {
                window.__isEditorDirty = false;
                syncContent();
            });
        }
    }

    // =========================================================================
    // 9. TOOLBAR BUTTON EVENTS
    // =========================================================================
    function setupToolbarEvents() {
        const toolbar = document.getElementById('wordToolbar');
        if (!toolbar) return;

        toolbar.querySelectorAll('.word-btn[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                const action = btn.getAttribute('data-action');
                execFormat(action);
            });
        });

        if (formatSelect) {
            formatSelect.addEventListener('change', () => {
                saveCurrentCaret();
                const val = formatSelect.value;
                if (val === 'p') {
                    document.execCommand('formatBlock', false, '<p>');
                } else if (val.startsWith('h')) {
                    document.execCommand('formatBlock', false, `<${val}>`);
                } else if (val === 'blockquote') {
                    document.execCommand('formatBlock', false, '<blockquote>');
                } else if (val === 'pre') {
                    document.execCommand('formatBlock', false, '<pre>');
                }
                editorDoc.focus();
                syncContent();
                updateToolbarState();
            });
        }

        const quoteBtn = document.getElementById('insertQuoteBtn');
        if (quoteBtn) {
            quoteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                toggleQuote();
            });
        }

        const linkBtn = document.getElementById('insertLinkBtn');
        if (linkBtn) {
            linkBtn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                openLinkModal();
            });
        }

        const imageBtn = document.getElementById('insertImageBtn');
        if (imageBtn) {
            imageBtn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                openImageModal();
            });
        }

        const tableBtn = document.getElementById('insertTableBtn');
        if (tableBtn) {
            tableBtn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                openWordTableModal();
            });
        }

        const ytBtn = document.getElementById('insertYoutubeBtn');
        if (ytBtn) {
            ytBtn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                openYoutubeModal();
            });
        }

        // Color dropdown toggle helper
        window.toggleColorDropdown = function (dropdownId) {
            const dd = document.getElementById(dropdownId);
            if (!dd) return;
            const isShown = dd.style.display === 'block';
            document.querySelectorAll('.word-color-dropdown').forEach(d => d.style.display = 'none');
            if (!isShown) {
                dd.style.display = 'block';
            }
        };

        // Text color swatches
        toolbar.querySelectorAll('.color-swatch-item[data-color]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                const color = btn.getAttribute('data-color');
                const ind = document.getElementById('textColorIndicator');
                if (ind) ind.style.background = color || 'var(--gold)';
                editorDoc.focus();
                if (!color) {
                    document.execCommand('removeFormat', false, null);
                } else {
                    document.execCommand('foreColor', false, color);
                }
                syncContent();
                document.querySelectorAll('.word-color-dropdown').forEach(d => d.style.display = 'none');
            });
        });

        // Custom text color input
        const customColorInput = document.getElementById('customTextColorPicker');
        if (customColorInput) {
            customColorInput.addEventListener('input', (e) => {
                const color = e.target.value;
                const ind = document.getElementById('textColorIndicator');
                if (ind) ind.style.background = color;
                editorDoc.focus();
                document.execCommand('foreColor', false, color);
                syncContent();
            });
        }

        // Text highlight swatches
        toolbar.querySelectorAll('.color-swatch-item[data-highlight]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                saveCurrentCaret();
                const color = btn.getAttribute('data-highlight');
                const ind = document.getElementById('highlightColorIndicator');
                if (ind) ind.style.background = color || '#fff3b0';
                editorDoc.focus();
                if (!color) {
                    try {
                        document.execCommand('hiliteColor', false, 'transparent');
                    } catch (err) {
                        document.execCommand('backColor', false, 'transparent');
                    }
                } else {
                    try {
                        document.execCommand('hiliteColor', false, color);
                    } catch (err) {
                        document.execCommand('backColor', false, color);
                    }
                }
                syncContent();
                document.querySelectorAll('.word-color-dropdown').forEach(d => d.style.display = 'none');
            });
        });

        // Close color dropdowns when clicking outside
        document.addEventListener('mousedown', (e) => {
            if (!e.target.closest('.word-color-dropdown-wrapper')) {
                document.querySelectorAll('.word-color-dropdown').forEach(d => d.style.display = 'none');
            }
        });
    }

    function toggleQuote() {
        editorDoc.focus();
        const sel = window.getSelection();
        let isInsideQuote = false;
        if (sel && sel.anchorNode) {
            let node = sel.anchorNode;
            if (node.nodeType === 3) node = node.parentNode;
            while (node && node !== editorDoc) {
                if (node.tagName && node.tagName.toLowerCase() === 'blockquote') {
                    isInsideQuote = true;
                    break;
                }
                node = node.parentNode;
            }
        }

        if (isInsideQuote) {
            document.execCommand('formatBlock', false, '<p>');
        } else {
            document.execCommand('formatBlock', false, '<blockquote>');
        }
        syncContent();
        updateToolbarState();
    }
    window.toggleWordQuote = toggleQuote;

    function execFormat(command, value = null) {
        editorDoc.focus();
        document.execCommand(command, false, value);
        syncContent();
        updateToolbarState();
    }

    function updateToolbarState() {
        const commands = ['bold', 'italic', 'underline', 'strikeThrough', 'justifyLeft', 'justifyCenter', 'justifyRight', 'insertUnorderedList', 'insertOrderedList'];
        commands.forEach(cmd => {
            const btn = document.querySelector(`.word-btn[data-action="${cmd}"]`);
            if (btn) {
                try {
                    const active = document.queryCommandState(cmd);
                    btn.classList.toggle('active', !!active);
                } catch (err) {
                    btn.classList.remove('active');
                }
            }
        });

        let foundTag = 'p';
        const sel = window.getSelection();
        if (sel && sel.anchorNode) {
            let node = sel.anchorNode;
            if (node.nodeType === 3) node = node.parentNode;
            while (node && node !== editorDoc) {
                const tag = node.tagName ? node.tagName.toLowerCase() : '';
                if (['h2', 'h3', 'h4', 'blockquote', 'pre', 'p'].includes(tag)) {
                    foundTag = tag;
                    break;
                }
                node = node.parentNode;
            }
        }

        if (formatSelect) {
            formatSelect.value = foundTag;
        }

        const quoteBtn = document.getElementById('insertQuoteBtn');
        if (quoteBtn) {
            quoteBtn.classList.toggle('active', foundTag === 'blockquote');
        }
    }

    // =========================================================================
    // 10. SYNC CONTENT, STATS & AUTOSAVE
    // =========================================================================
    function syncContent() {
        if (!editorDoc || !hiddenContent) return;
        const cleaned = cleanHtmlForSave(editorDoc.innerHTML);
        hiddenContent.value = cleaned;
        updateStats();
        triggerAutosave(cleaned);
    }

    function updateStats() {
        const text = editorDoc.innerText || '';
        const charCount = text.length;
        const wordCount = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
        const readTime = Math.max(1, Math.ceil(wordCount / 200));

        if (charCountEl) charCountEl.innerText = charCount + ' character' + (charCount !== 1 ? 's' : '');
        if (wordCountEl) wordCountEl.innerText = wordCount + ' word' + (wordCount !== 1 ? 's' : '');
        if (readTimeEl) readTimeEl.innerText = readTime + ' min read';

        const readTimeInput = document.getElementById('read_time');
        if (readTimeInput && (!readTimeInput.value || readTimeInput.dataset.auto === 'true')) {
            readTimeInput.value = readTime + ' min';
            readTimeInput.dataset.auto = 'true';
        }
    }

    let autosaveTimer = null;
    function triggerAutosave(cleaned) {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => {
            const blogId = document.getElementById('blogId') ? document.getElementById('blogId').value : '0';
            const saveKey = 'rt_blog_word_autosave_' + blogId;
            const data = {
                content: cleaned || cleanHtmlForSave(editorDoc.innerHTML),
                timestamp: Date.now()
            };
            try {
                localStorage.setItem(saveKey, JSON.stringify(data));
                if (autosaveIndicator) {
                    autosaveIndicator.style.opacity = '1';
                    autosaveIndicator.innerHTML = '<span style="color:#2e7d32; font-weight:600;">✓</span> Saved to browser draft';
                    setTimeout(() => {
                        if (autosaveIndicator) autosaveIndicator.style.opacity = '0.7';
                    }, 2500);
                }
            } catch (e) { }
        }, 800);
    }

    function checkAutosaveRecovery() {
        const blogId = document.getElementById('blogId') ? document.getElementById('blogId').value : '0';
        const saveKey = 'rt_blog_word_autosave_' + blogId;
        const raw = localStorage.getItem(saveKey);
        if (!raw) return;

        try {
            const data = JSON.parse(raw);
            if (data && data.content && Date.now() - data.timestamp < 48 * 3600 * 1000) {
                if (data.content.trim() !== cleanHtmlForSave(editorDoc.innerHTML).trim() && data.content.length > 50) {
                    const notice = document.createElement('div');
                    notice.className = 'autosave-recovery-bar';
                    notice.innerHTML = `
                        <span>Found an unsaved local draft from ${new Date(data.timestamp).toLocaleTimeString()}.</span>
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="btn btn-secondary btn-sm" id="restoreDraftBtn">Restore Draft</button>
                            <button type="button" class="btn btn-outline btn-sm" id="discardDraftBtn">Discard</button>
                        </div>
                    `;
                    const canvas = document.querySelector('.editor-main-canvas');
                    if (canvas) {
                        canvas.insertBefore(notice, canvas.firstChild);
                        document.getElementById('restoreDraftBtn').addEventListener('click', () => {
                            editorDoc.innerHTML = data.content;
                            attachAllControls();
                            syncContent();
                            notice.remove();
                            if (window.showToast) showToast('Draft restored successfully', 'success');
                        });
                        document.getElementById('discardDraftBtn').addEventListener('click', () => {
                            localStorage.removeItem(saveKey);
                            notice.remove();
                        });
                    }
                }
            }
        } catch (e) { }
    }

    // =========================================================================
    // 11. MODALS: LINK, IMAGE, YOUTUBE
    // =========================================================================
    function openLinkModal() {
        const sel = window.getSelection();
        let existingUrl = '';

        if (sel.anchorNode) {
            let p = sel.anchorNode.parentNode;
            if (p && p.tagName === 'A') {
                existingUrl = p.getAttribute('href') || '';
            }
        }

        const url = prompt('Enter link URL (e.g. https://...):', existingUrl || 'https://');
        if (url && url.trim() && url !== 'https://') {
            document.execCommand('createLink', false, url.trim());
            syncContent();
        }
    }

    function openImageModal() {
        saveCurrentCaret();
        const modal = document.getElementById('wordEditorImageModal');
        if (modal) {
            if (modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
            const fileInput = document.getElementById('wordImageFileInput');
            if (fileInput) fileInput.value = '';
            const urlInput = document.getElementById('wordImageUrlInput');
            if (urlInput) urlInput.value = '';
        }
    }
    window.openWordImageModal = openImageModal;

    function openYoutubeModal() {
        saveCurrentCaret();
        const url = prompt('Enter YouTube Video URL (e.g. https://www.youtube.com/watch?v=...):');
        if (url && url.trim()) {
            const ytId = extractYoutubeId(url.trim());
            if (ytId) {
                insertBlockElement(createYoutubeEmbedHtml(ytId));
            } else {
                alert('Invalid YouTube URL. Please provide a standard YouTube video link.');
            }
        }
    }

    // =========================================================================
    // 12. FLOATING TOOLBARS CONTEXT & POSITIONING
    // =========================================================================
    function checkFloatingContext(target) {
        const tableToolbar = document.getElementById('floatingTableToolbar');
        const imageToolbar = document.getElementById('floatingImageToolbar');

        if (target && (target.closest('#floatingTableToolbar') || target.closest('#floatingImageToolbar'))) {
            return;
        }

        let node = target;
        if (!node) {
            const sel = window.getSelection();
            if (sel && sel.anchorNode) {
                node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
            }
        }

        let foundCell = null;
        let foundTable = null;
        let foundFigure = null;

        let curr = node;
        while (curr && curr !== editorDoc) {
            if (!foundCell && (curr.tagName === 'TD' || curr.tagName === 'TH')) {
                foundCell = curr;
            }
            if (!foundTable && (curr.tagName === 'TABLE' || curr.classList?.contains('blog-custom-table'))) {
                foundTable = curr;
            }
            if (!foundFigure && (curr.tagName === 'FIGURE' || curr.classList?.contains('blog-figure'))) {
                foundFigure = curr;
            }
            curr = curr.parentNode;
        }

        if (foundTable && foundCell) {
            deselectAllBlocks();
            const wrapper = foundTable.closest('.table-responsive-wrapper') || foundTable;
            wrapper.classList.add('is-selected');

            currentActiveTable = foundTable;
            currentActiveCell = foundCell;
            currentActiveFigure = null;
            if (imageToolbar) imageToolbar.style.display = 'none';

            // Sync table toolbar controls
            updateTableToolbarState(foundTable);
            positionFloatingToolbar(tableToolbar, foundTable);
        } else if (foundFigure) {
            deselectAllBlocks();
            foundFigure.classList.add('is-selected');

            currentActiveFigure = foundFigure;
            currentActiveTable = null;
            currentActiveCell = null;
            if (tableToolbar) tableToolbar.style.display = 'none';

            // Sync image toolbar controls
            updateImageToolbarState(foundFigure);
            positionFloatingToolbar(imageToolbar, foundFigure);
        } else {
            if (tableToolbar) tableToolbar.style.display = 'none';
            if (imageToolbar) imageToolbar.style.display = 'none';
        }
    }

    function positionFloatingToolbar(toolbar, targetEl) {
        if (!toolbar || !targetEl) return;
        toolbar.style.display = 'flex';
        const rect = targetEl.getBoundingClientRect();
        const toolbarHeight = toolbar.offsetHeight || 38;

        let top = rect.top + window.scrollY - toolbarHeight - 10;
        let left = rect.left + window.scrollX;

        if (top < window.scrollY + 60) {
            top = rect.top + window.scrollY + 10;
        }
        if (left < 10) left = 10;

        toolbar.style.top = top + 'px';
        toolbar.style.left = left + 'px';
    }

    function updateImageToolbarState(figure) {
        const toolbar = document.getElementById('floatingImageToolbar');
        if (!toolbar || !figure) return;

        // Alignment buttons
        const alignBtns = toolbar.querySelectorAll('[data-img-align]');
        alignBtns.forEach(btn => {
            const align = btn.getAttribute('data-img-align');
            btn.classList.toggle('active', figure.classList.contains(`blog-figure-${align}`));
        });

        // Size buttons
        const sizeBtns = toolbar.querySelectorAll('[data-img-size]');
        const currentW = figure.style.width || '100%';
        sizeBtns.forEach(btn => {
            const size = btn.getAttribute('data-img-size');
            btn.classList.toggle('active', currentW === size);
        });
    }

    function updateTableToolbarState(table) {
        const widthSelect = document.getElementById('floatingTableWidthSelect');
        if (widthSelect && table.style.width) {
            widthSelect.value = table.style.width;
        }
        const alignSelect = document.getElementById('floatingTableAlignSelect');
        if (alignSelect) {
            const m = table.style.margin || '';
            if (m.includes('auto 0 0')) alignSelect.value = 'left';
            else if (m.includes('0 0 0 auto')) alignSelect.value = 'right';
            else alignSelect.value = 'center';
        }
    }

    // =========================================================================
    // 13. TABLE ACTIONS (Move, Resize, Rows, Columns, Structure)
    // =========================================================================
    window.tableAction = function (action, value) {
        if (!currentActiveTable) return;
        const wrapper = currentActiveTable.closest('.table-responsive-wrapper') || currentActiveTable;
        const row = currentActiveCell ? currentActiveCell.closest('tr') : null;
        const colIndex = (row && currentActiveCell) ? Array.from(row.children).indexOf(currentActiveCell) : 0;

        switch (action) {
            // Reordering
            case 'moveUp': {
                if (wrapper.previousElementSibling) {
                    wrapper.parentNode.insertBefore(wrapper, wrapper.previousElementSibling);
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    flashElement(wrapper);
                    positionFloatingToolbar(document.getElementById('floatingTableToolbar'), currentActiveTable);
                }
                break;
            }
            case 'moveDown': {
                if (wrapper.nextElementSibling) {
                    wrapper.parentNode.insertBefore(wrapper, wrapper.nextElementSibling.nextElementSibling);
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    flashElement(wrapper);
                    positionFloatingToolbar(document.getElementById('floatingTableToolbar'), currentActiveTable);
                }
                break;
            }

            // Width & Alignment
            case 'setWidth': {
                currentActiveTable.style.width = value || '100%';
                break;
            }
            case 'setAlign': {
                if (value === 'left') {
                    currentActiveTable.style.margin = '28px auto 28px 0';
                } else if (value === 'right') {
                    currentActiveTable.style.margin = '28px 0 28px auto';
                } else {
                    currentActiveTable.style.margin = '28px auto';
                }
                break;
            }
            case 'equalCols': {
                const firstRowCells = currentActiveTable.querySelectorAll('tr:first-child > *');
                const count = firstRowCells.length;
                if (count > 0) {
                    const pct = (100 / count).toFixed(1) + '%';
                    currentActiveTable.querySelectorAll('th, td').forEach(c => {
                        c.style.width = pct;
                    });
                }
                break;
            }
            case 'insertParagraphBelow': {
                const newP = document.createElement('p');
                newP.innerHTML = '<br>';
                wrapper.parentNode.insertBefore(newP, wrapper.nextSibling);
                placeCaretAtEnd(newP);
                newP.scrollIntoView({ behavior: 'smooth', block: 'center' });
                break;
            }

            // Row manipulation
            case 'addRowAbove': {
                if (!row) return;
                const newRow = document.createElement('tr');
                const colCount = row.children.length;
                for (let i = 0; i < colCount; i++) {
                    const td = document.createElement('td');
                    td.innerHTML = '<br>';
                    newRow.appendChild(td);
                }
                row.parentNode.insertBefore(newRow, row);
                break;
            }
            case 'addRowBelow': {
                if (!row) return;
                const newRow = document.createElement('tr');
                const colCount = row.children.length;
                for (let i = 0; i < colCount; i++) {
                    const td = document.createElement('td');
                    td.innerHTML = '<br>';
                    newRow.appendChild(td);
                }
                row.parentNode.insertBefore(newRow, row.nextSibling);
                break;
            }
            case 'deleteRow': {
                if (!row) return;
                const allRows = currentActiveTable.querySelectorAll('tr');
                if (allRows.length <= 1) {
                    if (confirm('Delete entire table?')) {
                        deleteTableWrapper(currentActiveTable);
                    }
                } else {
                    row.remove();
                }
                break;
            }

            // Column manipulation
            case 'addColLeft': {
                currentActiveTable.querySelectorAll('tr').forEach(r => {
                    const isHeader = r.parentElement.tagName === 'THEAD';
                    const cell = document.createElement(isHeader ? 'th' : 'td');
                    cell.innerHTML = isHeader ? 'Header' : '<br>';
                    const refCell = r.children[colIndex];
                    if (refCell) {
                        r.insertBefore(cell, refCell);
                    } else {
                        r.appendChild(cell);
                    }
                });
                break;
            }
            case 'addColRight': {
                currentActiveTable.querySelectorAll('tr').forEach(r => {
                    const isHeader = r.parentElement.tagName === 'THEAD';
                    const cell = document.createElement(isHeader ? 'th' : 'td');
                    cell.innerHTML = isHeader ? 'Header' : '<br>';
                    const refCell = r.children[colIndex];
                    if (refCell && refCell.nextSibling) {
                        r.insertBefore(cell, refCell.nextSibling);
                    } else {
                        r.appendChild(cell);
                    }
                });
                break;
            }
            case 'deleteCol': {
                if (!row) return;
                const totalCols = row.children.length;
                if (totalCols <= 1) {
                    if (confirm('Delete entire table?')) {
                        deleteTableWrapper(currentActiveTable);
                    }
                } else {
                    currentActiveTable.querySelectorAll('tr').forEach(r => {
                        if (r.children[colIndex]) {
                            r.children[colIndex].remove();
                        }
                    });
                }
                break;
            }

            case 'toggleHeader': {
                let thead = currentActiveTable.querySelector('thead');
                if (thead) {
                    const tbody = currentActiveTable.querySelector('tbody') || currentActiveTable;
                    const headerRow = thead.querySelector('tr');
                    if (headerRow) {
                        const newRow = document.createElement('tr');
                        Array.from(headerRow.children).forEach(th => {
                            const td = document.createElement('td');
                            td.innerHTML = th.innerHTML;
                            newRow.appendChild(td);
                        });
                        tbody.insertBefore(newRow, tbody.firstChild);
                    }
                    thead.remove();
                } else {
                    const firstRow = currentActiveTable.querySelector('tr');
                    if (firstRow) {
                        thead = document.createElement('thead');
                        const headerRow = document.createElement('tr');
                        const colCount = firstRow.children.length;
                        for (let i = 0; i < colCount; i++) {
                            const th = document.createElement('th');
                            th.innerText = 'Header ' + (i + 1);
                            headerRow.appendChild(th);
                        }
                        thead.appendChild(headerRow);
                        currentActiveTable.insertBefore(thead, currentActiveTable.firstChild);
                    }
                }
                break;
            }
            case 'deleteTable': {
                deleteTableWrapper(currentActiveTable);
                break;
            }
        }

        syncContent();
        const tableToolbar = document.getElementById('floatingTableToolbar');
        if (tableToolbar && currentActiveTable && document.body.contains(currentActiveTable)) {
            positionFloatingToolbar(tableToolbar, currentActiveTable);
        } else if (tableToolbar) {
            tableToolbar.style.display = 'none';
        }
    };

    function deleteTableWrapper(table) {
        const wrapper = table.closest('.table-responsive-wrapper');
        if (wrapper) wrapper.remove();
        else table.remove();
        currentActiveTable = null;
        currentActiveCell = null;
        const tableToolbar = document.getElementById('floatingTableToolbar');
        if (tableToolbar) tableToolbar.style.display = 'none';
        syncContent();
    }

    // =========================================================================
    // 14. IMAGE ACTIONS (Move, Resize, Alignments, Caption, Delete)
    // =========================================================================
    window.imageAction = function (action, value) {
        if (!currentActiveFigure) return;

        switch (action) {
            // Reordering
            case 'moveUp': {
                if (currentActiveFigure.previousElementSibling) {
                    currentActiveFigure.parentNode.insertBefore(currentActiveFigure, currentActiveFigure.previousElementSibling);
                    currentActiveFigure.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    flashElement(currentActiveFigure);
                    positionFloatingToolbar(document.getElementById('floatingImageToolbar'), currentActiveFigure);
                }
                break;
            }
            case 'moveDown': {
                if (currentActiveFigure.nextElementSibling) {
                    currentActiveFigure.parentNode.insertBefore(currentActiveFigure, currentActiveFigure.nextElementSibling.nextElementSibling);
                    currentActiveFigure.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    flashElement(currentActiveFigure);
                    positionFloatingToolbar(document.getElementById('floatingImageToolbar'), currentActiveFigure);
                }
                break;
            }

            // Alignments
            case 'alignLeft':
                currentActiveFigure.className = 'blog-figure blog-figure-left is-selected';
                if (!currentActiveFigure.style.width || currentActiveFigure.style.width === '100%') {
                    currentActiveFigure.style.width = '48%';
                }
                break;
            case 'alignCenter':
                currentActiveFigure.className = 'blog-figure blog-figure-center is-selected';
                break;
            case 'alignRight':
                currentActiveFigure.className = 'blog-figure blog-figure-right is-selected';
                if (!currentActiveFigure.style.width || currentActiveFigure.style.width === '100%') {
                    currentActiveFigure.style.width = '48%';
                }
                break;
            case 'alignWide':
                currentActiveFigure.className = 'blog-figure blog-figure-wide is-selected';
                currentActiveFigure.style.width = '100%';
                break;

            // Sizing presets
            case 'setSize':
                currentActiveFigure.style.width = value || '100%';
                currentActiveFigure.style.maxWidth = '100%';
                break;

            case 'insertParagraphBelow': {
                const newP = document.createElement('p');
                newP.innerHTML = '<br>';
                currentActiveFigure.parentNode.insertBefore(newP, currentActiveFigure.nextSibling);
                placeCaretAtEnd(newP);
                newP.scrollIntoView({ behavior: 'smooth', block: 'center' });
                break;
            }

            case 'editCaption': {
                let figcaption = currentActiveFigure.querySelector('figcaption');
                if (!figcaption) {
                    figcaption = document.createElement('figcaption');
                    figcaption.setAttribute('contenteditable', 'true');
                    currentActiveFigure.appendChild(figcaption);
                }
                const currentCap = figcaption.innerText;
                const newCap = prompt('Enter image caption:', currentCap);
                if (newCap !== null) {
                    figcaption.innerText = newCap.trim();
                    if (!newCap.trim()) figcaption.remove();
                }
                break;
            }

            case 'deleteImage': {
                currentActiveFigure.remove();
                currentActiveFigure = null;
                const imageToolbar = document.getElementById('floatingImageToolbar');
                if (imageToolbar) imageToolbar.style.display = 'none';
                break;
            }
        }

        syncContent();
        const imageToolbar = document.getElementById('floatingImageToolbar');
        if (currentActiveFigure && document.body.contains(currentActiveFigure)) {
            updateFigureTopbarState(currentActiveFigure);
            if (imageToolbar) {
                updateImageToolbarState(currentActiveFigure);
                positionFloatingToolbar(imageToolbar, currentActiveFigure);
            }
        } else if (imageToolbar) {
            imageToolbar.style.display = 'none';
        }
    };

    // =========================================================================
    // 15. TABLE INSERTION MODAL LOGIC
    // =========================================================================
    window.openWordTableModal = function () {
        saveCurrentCaret();
        const modal = document.getElementById('wordEditorTableModal');
        if (modal) {
            if (modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
        }
    };

    window.confirmInsertTable = function () {
        const rows = parseInt(document.getElementById('tableRowsInput').value, 10) || 3;
        const cols = parseInt(document.getElementById('tableColsInput').value, 10) || 3;
        const style = document.getElementById('tableStyleSelect').value || 'artisan';
        const includeHeader = document.getElementById('tableHeaderRowCheckbox').checked;

        let tableHtml = `<div class="table-responsive-wrapper" contenteditable="false"><table class="blog-custom-table blog-table-${style}" contenteditable="true" style="width:100%;">`;
        if (includeHeader) {
            tableHtml += '<thead><tr>';
            for (let c = 1; c <= cols; c++) {
                tableHtml += `<th>Header ${c}</th>`;
            }
            tableHtml += '</tr></thead>';
        }
        tableHtml += '<tbody>';
        for (let r = 1; r <= rows; r++) {
            tableHtml += '<tr>';
            for (let c = 1; c <= cols; c++) {
                tableHtml += `<td>Data ${r}.${c}</td>`;
            }
            tableHtml += '</tr>';
        }
        tableHtml += '</tbody></table></div>';

        insertBlockElement(tableHtml);

        const modal = document.getElementById('wordEditorTableModal');
        if (modal) modal.style.display = 'none';
        if (window.showToast) showToast('Table inserted successfully', 'success');
    };

    // =========================================================================
    // 16. IMAGE INSERTION & UPLOAD LOGIC
    // =========================================================================
    function setupImageUploadEvents() {
        const modal = document.getElementById('wordEditorImageModal');
        if (!modal) return;

        const closeBtn = document.getElementById('closeWordImageModal');
        const submitUrlBtn = document.getElementById('submitWordImageUrlBtn');
        const fileInput = document.getElementById('wordImageFileInput');
        const dropzone = document.getElementById('wordImageDropzone');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => { modal.style.display = 'none'; });
        }

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.style.display = 'none';
        });

        if (submitUrlBtn) {
            submitUrlBtn.addEventListener('click', () => {
                const url = document.getElementById('wordImageUrlInput').value.trim();
                const alt = document.getElementById('wordImageAltInput').value.trim();
                const caption = document.getElementById('wordImageCaptionInput').value.trim();
                const align = document.getElementById('wordImageAlignSelect').value || 'center';
                const width = document.getElementById('wordImageWidthSelect').value || '100%';

                if (url) {
                    const fullUrl = (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/') || url.startsWith('../')) ? url : ('../' + url);
                    const figHtml = `<figure class="blog-figure blog-figure-${align}" style="width:${width}; max-width:100%;" contenteditable="false"><img src="${fullUrl}" alt="${escapeHtml(alt)}" loading="lazy">${caption ? `<figcaption contenteditable="true">${escapeHtml(caption)}</figcaption>` : ''}</figure>`;
                    insertBlockElement(figHtml);
                    modal.style.display = 'none';
                    if (window.showToast) showToast('Image inserted successfully!', 'success');
                } else {
                    alert('Please select an image or enter a URL first.');
                }
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files[0]) {
                    uploadAndInsertImage(fileInput.files[0]);
                }
            });
        }

        if (dropzone) {
            dropzone.addEventListener('click', () => { if (fileInput) fileInput.click(); });
            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('drag-over'); });
            dropzone.addEventListener('dragleave', () => { dropzone.classList.remove('drag-over'); });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('drag-over');
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    uploadAndInsertImage(e.dataTransfer.files[0]);
                }
            });
        }
    }

    function uploadAndInsertImage(file) {
        const csrfTokenEl = document.querySelector('input[name="csrf_token"]');
        const csrfToken = csrfTokenEl ? csrfTokenEl.value : '';

        const formData = new FormData();
        formData.append('action', 'upload_inline_image');
        formData.append('csrf_token', csrfToken);
        formData.append('inline_image', file);

        const statusEl = document.getElementById('imageUploadStatus');
        if (statusEl) {
            statusEl.style.display = 'block';
            statusEl.innerText = 'Uploading ' + file.name + '...';
        }

        if (autosaveIndicator) {
            autosaveIndicator.style.opacity = '1';
            autosaveIndicator.innerText = 'Uploading image...';
        }

        fetch('blog-editor.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (statusEl) statusEl.style.display = 'none';
                if (data.success && data.url) {
                    const alt = document.getElementById('wordImageAltInput') ? document.getElementById('wordImageAltInput').value.trim() : file.name;
                    const caption = document.getElementById('wordImageCaptionInput') ? document.getElementById('wordImageCaptionInput').value.trim() : '';
                    const align = document.getElementById('wordImageAlignSelect') ? document.getElementById('wordImageAlignSelect').value : 'center';
                    const width = document.getElementById('wordImageWidthSelect') ? document.getElementById('wordImageWidthSelect').value : '100%';

                    const figHtml = `<figure class="blog-figure blog-figure-${align}" style="width:${width}; max-width:100%;" contenteditable="false"><img src="../${data.url}" alt="${escapeHtml(alt || file.name)}" loading="lazy">${caption ? `<figcaption contenteditable="true">${escapeHtml(caption)}</figcaption>` : ''}</figure>`;
                    insertBlockElement(figHtml);

                    const modal = document.getElementById('wordEditorImageModal');
                    if (modal) modal.style.display = 'none';

                    if (autosaveIndicator) {
                        autosaveIndicator.innerText = 'Image uploaded!';
                        setTimeout(() => { if (autosaveIndicator) autosaveIndicator.style.opacity = '0.5'; }, 1500);
                    }
                    if (window.showToast) showToast('Image uploaded and inserted!', 'success');
                } else {
                    alert('Image upload failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                if (statusEl) statusEl.style.display = 'none';
                alert('Image upload failed: ' + err.message);
            });
    }

    // =========================================================================
    // 17. GLOBAL UNDO / REDO
    // =========================================================================
    window.triggerUndo = function () {
        if (editorDoc) {
            editorDoc.focus();
            document.execCommand('undo');
            attachAllControls();
            syncContent();
        }
    };

    window.triggerRedo = function () {
        if (editorDoc) {
            editorDoc.focus();
            document.execCommand('redo');
            attachAllControls();
            syncContent();
        }
    };

})();
