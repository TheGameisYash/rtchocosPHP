/* admin/word-editor.js - Standard Continuous Document (Word-Style) Rich Text Editor */

(function () {
    'use strict';

    let editorDoc = null;
    let hiddenContent = null;
    let formatSelect = null;
    let charCountEl = null;
    let wordCountEl = null;
    let readTimeEl = null;
    let autosaveIndicator = null;
    let lastSavedContent = '';

    // Initialize the Word Editor
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

        // Set initial hidden input value
        if (hiddenContent) {
            hiddenContent.value = editorDoc.innerHTML;
        }

        // Setup event listeners
        setupEditorEvents();
        setupToolbarEvents();
        setupImageUploadEvents();
        updateStats();
        updateToolbarState();

        // Autosave recovery check
        checkAutosaveRecovery();
    };

    // CONVERT INITIAL CONTENT (handles both Markdown and existing HTML)
    function convertToEditableHtml(raw) {
        if (!raw || !raw.trim()) return '<p><br></p>';

        // If it's already full HTML (contains <p, <div, <h1, <h2, etc.)
        if (/<(p|div|h[1-6]|ul|ol|blockquote|table|section|figure)/i.test(raw)) {
            return raw;
        }

        // Otherwise parse Markdown blocks to HTML
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
                    let tableHtml = '<div class="table-responsive-wrapper" contenteditable="false"><table class="blog-custom-table blog-table-artisan" contenteditable="true">';
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

            // Image markdown: ![alt](url)
            const imgMatch = block.match(/^!\[(.*?)\]\((.*?)\)/);
            if (imgMatch) {
                const alt = imgMatch[1];
                const url = imgMatch[2];
                html += `<figure class="blog-figure blog-figure-center" contenteditable="false"><img src="${url}" alt="${escapeHtml(alt)}" loading="lazy">${alt ? `<figcaption contenteditable="true">${escapeHtml(alt)}</figcaption>` : ''}</figure><p><br></p>`;
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
        // Bold: **text**
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic: *text* or _text_
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        text = text.replace(/_([^_]+)_/g, '<em>$1</em>');
        // Links: [text](url)
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

    // Context references for floating toolbars
    let currentActiveTable = null;
    let currentActiveCell = null;
    let currentActiveFigure = null;

    // Helper: place caret at end of element
    function placeCaretAtEnd(el) {
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

    // EDITOR DOM & KEYBOARD EVENTS
    function setupEditorEvents() {
        // Sync content & stats on input
        editorDoc.addEventListener('input', () => {
            syncContent();
        });

        // Update toolbar active states on selection / caret change & check floating toolbars
        const handleInteraction = (e) => {
            updateToolbarState();
            checkFloatingContext(e ? e.target : null);
        };

        editorDoc.addEventListener('keyup', handleInteraction);
        editorDoc.addEventListener('mouseup', handleInteraction);
        editorDoc.addEventListener('click', handleInteraction);
        
        document.addEventListener('selectionchange', () => {
            if (document.activeElement === editorDoc || editorDoc.contains(document.activeElement)) {
                updateToolbarState();
                checkFloatingContext(null);
            }
        });

        // Click outside editor & floating toolbars closes floating toolbars
        document.addEventListener('mousedown', (e) => {
            const tableToolbar = document.getElementById('floatingTableToolbar');
            const imageToolbar = document.getElementById('floatingImageToolbar');
            if (tableToolbar && !tableToolbar.contains(e.target) && !editorDoc.contains(e.target)) {
                tableToolbar.style.display = 'none';
            }
            if (imageToolbar && !imageToolbar.contains(e.target) && !editorDoc.contains(e.target)) {
                imageToolbar.style.display = 'none';
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

        // Keyboard shortcuts
        editorDoc.addEventListener('keydown', (e) => {
            // Tab key inside table cells for swift navigation and row creation
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
                            // Move to previous cell
                            if (currentIndex > 0) {
                                cells[currentIndex - 1].focus();
                                placeCaretAtEnd(cells[currentIndex - 1]);
                            }
                        } else {
                            // Move to next cell or create new row if at last cell
                            if (currentIndex < cells.length - 1) {
                                cells[currentIndex + 1].focus();
                                placeCaretAtEnd(cells[currentIndex + 1]);
                            } else {
                                // Last cell! Append a new row to tbody
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
                        // Let native undo work, or trigger sync after
                        setTimeout(syncContent, 10);
                        break;
                    case 'y':
                        // Let native redo work, or trigger sync after
                        setTimeout(syncContent, 10);
                        break;
                }
            }
        });

        // Paste Handling: Strip unwanted Microsoft Word / rich format junk while preserving structure
        editorDoc.addEventListener('paste', (e) => {
            const clipboard = e.clipboardData;
            if (!clipboard) return;

            // Check if pasting an image file directly
            if (clipboard.files && clipboard.files.length > 0) {
                const file = clipboard.files[0];
                if (file.type.startsWith('image/')) {
                    e.preventDefault();
                    uploadAndInsertImage(file);
                    return;
                }
            }
        });

        // Drag & drop images directly onto editor
        editorDoc.addEventListener('dragover', (e) => {
            e.preventDefault();
            editorDoc.classList.add('drag-over');
        });
        editorDoc.addEventListener('dragleave', () => {
            editorDoc.classList.remove('drag-over');
        });
        editorDoc.addEventListener('drop', (e) => {
            e.preventDefault();
            editorDoc.classList.remove('drag-over');
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const file = e.dataTransfer.files[0];
                if (file.type.startsWith('image/')) {
                    uploadAndInsertImage(file);
                }
            }
        });

        // Ensure form submit syncs content
        const form = document.getElementById('editorForm');
        if (form) {
            form.addEventListener('submit', () => {
                syncContent();
            });
        }
    }

    // TOOLBAR BUTTON EVENTS
    function setupToolbarEvents() {
        const toolbar = document.getElementById('wordToolbar');
        if (!toolbar) return;

        // Action buttons
        toolbar.querySelectorAll('.word-btn[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const action = btn.getAttribute('data-action');
                execFormat(action);
            });
        });

        // Format dropdown (Paragraph, Heading 2, 3, 4, Quote, Code)
        if (formatSelect) {
            formatSelect.addEventListener('change', () => {
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

        // Quote button
        const quoteBtn = document.getElementById('insertQuoteBtn');
        if (quoteBtn) {
            quoteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                toggleQuote();
            });
        }

        // Link button
        const linkBtn = document.getElementById('insertLinkBtn');
        if (linkBtn) {
            linkBtn.addEventListener('click', (e) => {
                e.preventDefault();
                openLinkModal();
            });
        }

        // Image button
        const imageBtn = document.getElementById('insertImageBtn');
        if (imageBtn) {
            imageBtn.addEventListener('click', (e) => {
                e.preventDefault();
                openImageModal();
            });
        }

        // YouTube button
        const ytBtn = document.getElementById('insertYoutubeBtn');
        if (ytBtn) {
            ytBtn.addEventListener('click', (e) => {
                e.preventDefault();
                openYoutubeModal();
            });
        }
    }

    // TOGGLE QUOTE / CALLOUT BOX
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

    // EXEC FORMAT COMMAND
    function execFormat(command, value = null) {
        editorDoc.focus();
        document.execCommand(command, false, value);
        syncContent();
        updateToolbarState();
    }

    // UPDATE ACTIVE TOOLBAR BUTTONS & DROPDOWN
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

        // Update format select dropdown & quote button
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

    // SYNC CONTENT & STATS
    function syncContent() {
        if (!editorDoc || !hiddenContent) return;
        const html = editorDoc.innerHTML;
        hiddenContent.value = html;

        updateStats();

        // Autosave debounce
        triggerAutosave();
    }

    function updateStats() {
        const text = editorDoc.innerText || '';
        const charCount = text.length;
        const wordCount = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
        const readTime = Math.max(1, Math.ceil(wordCount / 200));

        if (charCountEl) charCountEl.innerText = charCount + ' character' + (charCount !== 1 ? 's' : '');
        if (wordCountEl) wordCountEl.innerText = wordCount + ' word' + (wordCount !== 1 ? 's' : '');
        if (readTimeEl) readTimeEl.innerText = readTime + ' min read';

        // Auto-fill estimated read time in settings drawer if empty or auto-sync
        const readTimeInput = document.getElementById('read_time');
        if (readTimeInput && (!readTimeInput.value || readTimeInput.dataset.auto === 'true')) {
            readTimeInput.value = readTime + ' min';
            readTimeInput.dataset.auto = 'true';
        }
    }

    // AUTOSAVE LOGIC
    let autosaveTimer = null;
    function triggerAutosave() {
        if (autosaveIndicator) {
            autosaveIndicator.style.opacity = '1';
            autosaveIndicator.innerText = 'Saving changes...';
        }

        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => {
            const blogId = document.getElementById('blogId') ? document.getElementById('blogId').value : '0';
            const saveKey = 'rt_blog_word_autosave_' + blogId;
            const data = {
                content: editorDoc.innerHTML,
                timestamp: Date.now()
            };
            try {
                localStorage.setItem(saveKey, JSON.stringify(data));
                if (autosaveIndicator) {
                    autosaveIndicator.innerText = 'All changes saved locally';
                    setTimeout(() => {
                        if (autosaveIndicator) autosaveIndicator.style.opacity = '0.5';
                    }, 1500);
                }
            } catch (e) {
                // storage full fallback
            }
        }, 800);
    }

    function checkAutosaveRecovery() {
        const blogId = document.getElementById('blogId') ? document.getElementById('blogId').value : '0';
        const saveKey = 'rt_blog_word_autosave_' + blogId;
        const raw = localStorage.getItem(saveKey);
        if (!raw) return;

        try {
            const data = JSON.parse(raw);
            // If autosaved within last 48 hours and differs from current
            if (data && data.content && Date.now() - data.timestamp < 48 * 3600 * 1000) {
                if (data.content.trim() !== editorDoc.innerHTML.trim() && data.content.length > 50) {
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

    // MODALS: LINK, IMAGE, YOUTUBE
    function openLinkModal() {
        const sel = window.getSelection();
        let selectedText = sel.toString();
        let existingUrl = '';

        if (sel.anchorNode) {
            let p = sel.anchorNode.parentNode;
            if (p && p.tagName === 'A') {
                existingUrl = p.getAttribute('href') || '';
                selectedText = p.innerText;
            }
        }

        const url = prompt('Enter link URL (e.g. https://...):', existingUrl || 'https://');
        if (url && url.trim() && url !== 'https://') {
            document.execCommand('createLink', false, url.trim());
            syncContent();
        }
    }

    function openImageModal() {
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
        const url = prompt('Enter YouTube Video URL (e.g. https://www.youtube.com/watch?v=...):');
        if (url && url.trim()) {
            const ytId = extractYoutubeId(url.trim());
            if (ytId) {
                insertHtmlAtCursor(createYoutubeEmbedHtml(ytId));
                syncContent();
            } else {
                alert('Invalid YouTube URL. Please provide a standard YouTube video link.');
            }
        }
    }

    function insertHtmlAtCursor(html) {
        editorDoc.focus();
        const sel = window.getSelection();
        if (sel && sel.getRangeAt && sel.rangeCount) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            const el = document.createElement('div');
            el.innerHTML = html;
            const frag = document.createDocumentFragment();
            let node, lastNode;
            while ((node = el.firstChild)) {
                lastNode = frag.appendChild(node);
            }
            range.insertNode(frag);
            if (lastNode) {
                range.setStartAfter(lastNode);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        } else {
            editorDoc.innerHTML += html;
        }
        syncContent();
    }

    // FLOATING TOOLBARS CONTEXT & POSITIONING
    function checkFloatingContext(target) {
        const tableToolbar = document.getElementById('floatingTableToolbar');
        const imageToolbar = document.getElementById('floatingImageToolbar');

        // If clicking on the toolbar itself, don't dismiss
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
            currentActiveTable = foundTable;
            currentActiveCell = foundCell;
            currentActiveFigure = null;
            if (imageToolbar) imageToolbar.style.display = 'none';
            positionFloatingToolbar(tableToolbar, foundTable);
        } else if (foundFigure) {
            currentActiveFigure = foundFigure;
            currentActiveTable = null;
            currentActiveCell = null;
            if (tableToolbar) tableToolbar.style.display = 'none';
            positionFloatingToolbar(imageToolbar, foundFigure);
        } else {
            currentActiveTable = null;
            currentActiveCell = null;
            currentActiveFigure = null;
            if (tableToolbar) tableToolbar.style.display = 'none';
            if (imageToolbar) imageToolbar.style.display = 'none';
        }
    }

    function positionFloatingToolbar(toolbar, targetEl) {
        if (!toolbar || !targetEl) return;
        toolbar.style.display = 'flex';
        const rect = targetEl.getBoundingClientRect();
        const toolbarHeight = toolbar.offsetHeight || 38;
        
        let top = rect.top + window.scrollY - toolbarHeight - 8;
        let left = rect.left + window.scrollX;

        if (top < window.scrollY + 60) {
            top = rect.top + window.scrollY + 10;
        }
        if (left < 10) left = 10;

        toolbar.style.top = top + 'px';
        toolbar.style.left = left + 'px';
    }

    // TABLE ACTIONS
    window.tableAction = function(action) {
        if (!currentActiveTable || !currentActiveCell) return;
        const row = currentActiveCell.closest('tr');
        if (!row) return;

        const colIndex = Array.from(row.children).indexOf(currentActiveCell);

        switch(action) {
            case 'addRowAbove': {
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
    }

    // IMAGE ACTIONS
    window.imageAction = function(action) {
        if (!currentActiveFigure) return;
        switch(action) {
            case 'alignLeft':
                currentActiveFigure.className = 'blog-figure blog-figure-left';
                break;
            case 'alignCenter':
                currentActiveFigure.className = 'blog-figure blog-figure-center';
                break;
            case 'alignRight':
                currentActiveFigure.className = 'blog-figure blog-figure-right';
                break;
            case 'alignWide':
                currentActiveFigure.className = 'blog-figure blog-figure-wide';
                break;
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
        if (imageToolbar && currentActiveFigure && document.body.contains(currentActiveFigure)) {
            positionFloatingToolbar(imageToolbar, currentActiveFigure);
        }
    };

    // TABLE MODAL LOGIC
    window.openWordTableModal = function() {
        const modal = document.getElementById('wordEditorTableModal');
        if (modal) {
            if (modal.parentNode !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
        }
    };

    window.confirmInsertTable = function() {
        const rows = parseInt(document.getElementById('tableRowsInput').value, 10) || 3;
        const cols = parseInt(document.getElementById('tableColsInput').value, 10) || 3;
        const style = document.getElementById('tableStyleSelect').value || 'artisan';
        const includeHeader = document.getElementById('tableHeaderRowCheckbox').checked;

        let tableHtml = `<div class="table-responsive-wrapper" contenteditable="false"><table class="blog-custom-table blog-table-${style}" contenteditable="true">`;
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
        tableHtml += '</tbody></table></div><p><br></p>';

        insertHtmlAtCursor(tableHtml);
        const modal = document.getElementById('wordEditorTableModal');
        if (modal) modal.style.display = 'none';
        syncContent();
        if (window.showToast) showToast('Table inserted successfully', 'success');
    };

    // UPLOAD IMAGE HELPER (AJAX)
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
                    const figHtml = `<figure class="blog-figure blog-figure-${align}" style="width:${width};" contenteditable="false"><img src="${fullUrl}" alt="${escapeHtml(alt)}" loading="lazy">${caption ? `<figcaption contenteditable="true">${escapeHtml(caption)}</figcaption>` : ''}</figure><p><br></p>`;
                    insertHtmlAtCursor(figHtml);
                    modal.style.display = 'none';
                    syncContent();
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

                const figHtml = `<figure class="blog-figure blog-figure-${align}" style="width:${width};" contenteditable="false"><img src="../${data.url}" alt="${escapeHtml(alt || file.name)}" loading="lazy">${caption ? `<figcaption contenteditable="true">${escapeHtml(caption)}</figcaption>` : ''}</figure><p><br></p>`;
                insertHtmlAtCursor(figHtml);
                syncContent();
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

    // Global undo / redo triggers for header buttons
    window.triggerUndo = function () {
        if (editorDoc) {
            editorDoc.focus();
            document.execCommand('undo');
            syncContent();
        }
    };

    window.triggerRedo = function () {
        if (editorDoc) {
            editorDoc.focus();
            document.execCommand('redo');
            syncContent();
        }
    };

})();
