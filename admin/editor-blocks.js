/* admin/editor-blocks.js — Block Renderers, Image/YouTube/Table Builders, Controls */

window.EditorBlocks = (function () {
    'use strict';

    var E = window.EditorEngine;
    var S = window.EditorSerializer;

    // ═══════════════════════════════════════════════════════
    // BLOCK SIDE CONTROLS
    // ═══════════════════════════════════════════════════════
    function createBlockControls(index, type) {
        var controls = document.createElement('div');
        controls.className = 'editor-block-controls';

        // Add Plus button
        var addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'block-ctrl-btn block-ctrl-add';
        addBtn.title = 'Add block above';
        addBtn.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>';
        addBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleInlineBlockMenu(e, index, addBtn, 'before');
        });
        controls.appendChild(addBtn);

        // Drag handle / block type selector
        var dragBtn = document.createElement('button');
        dragBtn.type = 'button';
        dragBtn.className = 'block-ctrl-btn block-ctrl-drag';
        dragBtn.title = 'Drag to reorder · Click to change type';
        dragBtn.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"></path><circle cx="4" cy="8" r="1" fill="currentColor"/><circle cx="4" cy="16" r="1" fill="currentColor"/><circle cx="10" cy="8" r="1" fill="currentColor"/><circle cx="10" cy="16" r="1" fill="currentColor"/></svg>';
        dragBtn.addEventListener('click', function (e) { toggleBlockConverterMenu(e, index, dragBtn); });
        controls.appendChild(dragBtn);

        // Delete button
        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'block-ctrl-btn block-ctrl-delete';
        delBtn.title = 'Delete Block';
        delBtn.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>';
        delBtn.addEventListener('click', function () { E.deleteBlock(index); });
        controls.appendChild(delBtn);

        return controls;
    }

    // ═══════════════════════════════════════════════════════
    // BLOCK CONTENT ELEMENTS
    // ═══════════════════════════════════════════════════════
    function createBlockContentElement(block, index) {
        // Handle custom widget elements
        if (block.type === 'divider') {
            var hr = document.createElement('hr');
            hr.className = 'block-divider';
            return hr;
        }

        if (block.type === 'image') {
            return createImageBlockElement(block, index);
        }

        if (block.type === 'youtube') {
            return createYoutubeBlockElement(block, index);
        }

        if (block.type === 'table') {
            return createTableBlockElement(block, index);
        }

        // Standard textable blocks
        var el = document.createElement('div');
        el.className = 'editor-block-content';
        el.contentEditable = true;

        switch (block.type) {
            case 'paragraph': el.className += ' block-p'; break;
            case 'heading-2': el.className += ' block-h2'; break;
            case 'heading-3': el.className += ' block-h3'; break;
            case 'heading-4': el.className += ' block-h4'; break;
            case 'quote': el.className += ' block-quote'; break;
            case 'callout': el.className += ' block-callout'; break;
            case 'code':
                el.className += ' block-code';
                el.contentEditable = true;
                break;
            case 'list-bullet': el.className += ' block-list-bullet'; break;
            case 'list-ordered':
                el.className += ' block-list-ordered';
                el.dataset.index = block.index || 1;
                break;
        }

        // Apply alignment
        if (block.align) {
            el.style.textAlign = block.align;
        }

        el.innerHTML = block.content || '';

        // Placeholder helper
        if (el.innerHTML === '') {
            if (block.type === 'paragraph') el.setAttribute('placeholder', 'Type something, or press / for commands...');
            else if (block.type.startsWith('heading')) el.setAttribute('placeholder', 'Heading');
            else if (block.type === 'list-bullet' || block.type === 'list-ordered') el.setAttribute('placeholder', 'List item');
            else if (block.type === 'quote') el.setAttribute('placeholder', 'Write a quote...');
            else if (block.type === 'callout') el.setAttribute('placeholder', 'Write a callout note...');
        }

        // Content synchronization on input
        el.addEventListener('input', function () {
            block.content = el.innerHTML;

            // Recalculate ordered list indices
            if (block.type === 'list-ordered') {
                S.recalcListIndices(E.state.blocks);
                el.dataset.index = block.index || 1;
            }

            // Slash command detection
            var textContent = el.textContent;
            if (textContent.startsWith('/') && block.type === 'paragraph') {
                var filter = textContent.substring(1).toLowerCase();
                EditorToolbar.openSlashMenu(el, index, filter);
            } else if (E.state.slashMenuOpen) {
                EditorToolbar.closeSlashMenu();
            }

            E.saveUndoState();
            E.updateHiddenInput();
            E.updateStats();
        });

        // Rich paste handler
        el.addEventListener('paste', function (e) { EditorFeatures.handleRichPaste(e, block, index); });

        // Keyboard handling
        el.addEventListener('keydown', function (e) {
            // Slash menu keyboard navigation
            if (E.state.slashMenuOpen) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    EditorToolbar.navigateSlashMenu(1);
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    EditorToolbar.navigateSlashMenu(-1);
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    EditorToolbar.selectSlashMenuItem();
                    return;
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    EditorToolbar.closeSlashMenu();
                    return;
                }
            }

            if (e.key === 'Enter' && !e.shiftKey) {
                // If in code block, allow normal newlines
                if (block.type === 'code') return;

                e.preventDefault();

                // AUTO-CONTINUE: list items
                if (block.type === 'list-bullet') {
                    if (el.textContent.trim() === '') {
                        E.convertBlockType(index, 'paragraph');
                    } else {
                        E.insertNewBlockAfter(index, 'list-bullet');
                    }
                } else if (block.type === 'list-ordered') {
                    if (el.textContent.trim() === '') {
                        E.convertBlockType(index, 'paragraph');
                    } else {
                        E.insertNewBlockAfter(index, 'list-ordered');
                    }
                } else {
                    E.insertNewBlockAfter(index, 'paragraph');
                }
            }
            else if (e.key === 'Backspace' && el.innerHTML.trim() === '') {
                e.preventDefault();
                E.deleteBlock(index, true);
            }
            // Arrow key navigation between blocks
            else if (e.key === 'ArrowUp' && E.isCaretAtStart(el)) {
                e.preventDefault();
                E.focusBlock(index - 1, 'end');
            }
            else if (e.key === 'ArrowDown' && E.isCaretAtEnd(el)) {
                e.preventDefault();
                E.focusBlock(index + 1, 'start');
            }
            // Tab handling
            else if (e.key === 'Tab') {
                if (block.type === 'code') return;
                e.preventDefault();
            }
            // Markdown shortcuts
            else if (e.key === ' ' && block.type === 'paragraph') {
                var text = el.textContent;
                if (text === '##') {
                    e.preventDefault();
                    block.content = '';
                    E.convertBlockType(index, 'heading-2');
                } else if (text === '###') {
                    e.preventDefault();
                    block.content = '';
                    E.convertBlockType(index, 'heading-3');
                } else if (text === '####') {
                    e.preventDefault();
                    block.content = '';
                    E.convertBlockType(index, 'heading-4');
                } else if (text === '-' || text === '*') {
                    e.preventDefault();
                    block.content = '';
                    E.convertBlockType(index, 'list-bullet');
                } else if (/^\d+\.$/.test(text)) {
                    e.preventDefault();
                    block.content = '';
                    E.convertBlockType(index, 'list-ordered');
                } else if (text === '>') {
                    e.preventDefault();
                    block.content = '';
                    E.convertBlockType(index, 'quote');
                } else if (text === '---') {
                    e.preventDefault();
                    block.content = '';
                    E.convertBlockType(index, 'divider');
                }
            }
        });

        el.addEventListener('focus', function () {
            E.state.focusedIndex = index;
        });

        return el;
    }

    // ═══════════════════════════════════════════════════════
    // IMAGE BLOCK
    // ═══════════════════════════════════════════════════════
    function createImageBlockElement(block, index) {
        var wrap = document.createElement('div');
        wrap.className = 'editor-block-content';

        if (!block.url) {
            var uploadBox = document.createElement('div');
            uploadBox.className = 'block-image-upload';
            uploadBox.innerHTML =
                '<svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>' +
                '<p>Click or drag image here to upload</p>' +
                '<span>PNG, JPG, WEBP &bull; Max 5MB</span>' +
                '<input type="file" style="display:none;" accept="image/*">';

            var fileInput = uploadBox.querySelector('input');
            uploadBox.addEventListener('click', function () { fileInput.click(); });

            fileInput.addEventListener('change', function (e) {
                if (e.target.files && e.target.files[0]) {
                    uploadImageFile(e.target.files[0], block, index);
                }
            });

            // Drag and Drop
            uploadBox.addEventListener('dragover', function (e) {
                e.preventDefault();
                uploadBox.style.borderColor = 'var(--gold)';
            });
            uploadBox.addEventListener('dragleave', function () {
                uploadBox.style.borderColor = '';
            });
            uploadBox.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                uploadBox.style.borderColor = '';
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    uploadImageFile(e.dataTransfer.files[0], block, index);
                }
            });

            wrap.appendChild(uploadBox);
        } else {
            var displayBox = document.createElement('div');
            displayBox.className = 'block-image-uploaded';
            var position = block.position || 'center';
            displayBox.innerHTML =
                '<img src="../' + block.url + '" alt="" class="blog-img-' + position + '">' +
                '<div class="block-image-details" style="display:flex; gap:10px; margin-top:8px;">' +
                    '<input type="text" class="img-caption" placeholder="Write image caption..." value="' + (block.caption || '') + '" style="flex-grow:1;">' +
                    '<select class="img-position" style="padding:6px; border:1px solid var(--border-color); border-radius:6px; background:var(--bg-app); color:var(--text-main); font-size:12px;">' +
                        '<option value="center"' + (position === 'center' ? ' selected' : '') + '>Center</option>' +
                        '<option value="left"' + (position === 'left' ? ' selected' : '') + '>Left</option>' +
                        '<option value="right"' + (position === 'right' ? ' selected' : '') + '>Right</option>' +
                        '<option value="end"' + (position === 'end' ? ' selected' : '') + '>End of Blog</option>' +
                    '</select>' +
                '</div>';

            var captionInput = displayBox.querySelector('.img-caption');
            captionInput.addEventListener('input', function (e) {
                block.caption = e.target.value;
                E.updateHiddenInput();
            });

            var positionSelect = displayBox.querySelector('.img-position');
            positionSelect.addEventListener('change', function (e) {
                block.position = e.target.value;
                var img = displayBox.querySelector('img');
                img.className = '';
                img.classList.add('blog-img-' + e.target.value);
                E.updateHiddenInput();
            });

            wrap.appendChild(displayBox);
        }
        return wrap;
    }

    // AJAX image upload
    function uploadImageFile(file, block, index) {
        var formData = new FormData();
        formData.append('inline_image', file);
        formData.append('action', 'upload_inline_image');

        var tokenEl = document.querySelector('input[name="csrf_token"]');
        if (tokenEl) {
            formData.append('csrf_token', tokenEl.value);
        }

        var container = E.getContainer();
        var wrapper = container.querySelector('[data-index="' + index + '"] .editor-block-content');
        wrapper.innerHTML =
            '<div style="padding: 30px; text-align: center; color: var(--text-light);">' +
                '<div class="upload-spinner"></div>' +
                '<span>Uploading Image...</span>' +
            '</div>';

        fetch('blog-editor.php', {
            method: 'POST',
            body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                block.url = data.url;
                block.caption = file.name.split('.')[0];
                E.renderBlocks();
                E.updateHiddenInput();
                if (typeof showToast === 'function') showToast('Image uploaded successfully', 'success');
            } else {
                if (typeof showToast === 'function') showToast(data.message || 'Image upload failed', 'danger');
                block.url = '';
                E.renderBlocks();
            }
        })
        .catch(function () {
            if (typeof showToast === 'function') showToast('Network error during image upload', 'danger');
            block.url = '';
            E.renderBlocks();
        });
    }

    // ═══════════════════════════════════════════════════════
    // YOUTUBE BLOCK
    // ═══════════════════════════════════════════════════════
    function createYoutubeBlockElement(block, index) {
        var wrap = document.createElement('div');
        wrap.className = 'editor-block-content';

        if (!block.url) {
            var inputWidget = document.createElement('div');
            inputWidget.className = 'block-yt-embed-widget';
            inputWidget.innerHTML =
                '<div style="display:flex; gap:10px;">' +
                    '<input type="text" placeholder="Paste YouTube URL here (e.g. https://www.youtube.com/watch?v=...)" style="flex-grow:1; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">' +
                    '<button type="button" class="btn btn-secondary btn-sm" style="padding:8px 16px;">Embed</button>' +
                '</div>';

            var input = inputWidget.querySelector('input');
            var btn = inputWidget.querySelector('button');

            var handleEmbedSubmit = function () {
                var url = input.value.trim();
                var ytId = S.getYoutubeId(url);
                if (ytId) {
                    block.url = url;
                    E.renderBlocks();
                    E.updateHiddenInput();
                } else {
                    if (typeof showToast === 'function') showToast('Invalid YouTube URL', 'danger');
                }
            };
            btn.addEventListener('click', handleEmbedSubmit);
            input.addEventListener('keydown', function (e) { if (e.key === 'Enter') handleEmbedSubmit(); });

            wrap.appendChild(inputWidget);
        } else {
            var ytId = S.getYoutubeId(block.url);
            var previewWidget = document.createElement('div');
            previewWidget.className = 'block-yt-embed-widget';
            previewWidget.innerHTML =
                '<div class="yt-preview-box">' +
                    '<iframe src="https://www.youtube.com/embed/' + ytId + '" frameborder="0" allowfullscreen></iframe>' +
                '</div>' +
                '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                    '<span style="font-size:11.5px; color:var(--text-light); font-family: monospace;">Video ID: ' + ytId + '</span>' +
                    '<button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:11px;">Change Video</button>' +
                '</div>';

            var changeBtn = previewWidget.querySelector('button');
            changeBtn.addEventListener('click', function () {
                block.url = '';
                E.renderBlocks();
            });

            wrap.appendChild(previewWidget);
        }
        return wrap;
    }

    // ═══════════════════════════════════════════════════════
    // TABLE BLOCK
    // ═══════════════════════════════════════════════════════
    function createTableBlockElement(block, index) {
        var wrap = document.createElement('div');
        wrap.className = 'editor-block-content block-table-wrapper';

        var content = block.content || '| Header 1 | Header 2 | Header 3 |\n| --- | --- | --- |\n| Cell 1 | Cell 2 | Cell 3 |';
        block.content = content;

        var textarea = document.createElement('textarea');
        textarea.className = 'block-table-editor';
        textarea.value = content;
        textarea.placeholder = '| Header 1 | Header 2 |\n| --- | --- |\n| Cell 1 | Cell 2 |';
        textarea.rows = Math.max(3, content.split('\n').length + 1);

        textarea.addEventListener('input', function () {
            block.content = textarea.value;
            textarea.rows = Math.max(3, textarea.value.split('\n').length + 1);
            E.updateHiddenInput();
            E.updateStats();
        });

        textarea.addEventListener('focus', function () {
            E.state.focusedIndex = index;
        });

        wrap.appendChild(textarea);
        return wrap;
    }

    // ═══════════════════════════════════════════════════════
    // BOTTOM BLOCK ADDER (+)
    // ═══════════════════════════════════════════════════════
    function createBottomAdder() {
        var wrap = document.createElement('div');
        wrap.className = 'block-adder-wrap';

        var menuItems = '';
        E.BLOCK_TYPES.forEach(function (bt) {
            menuItems += '<div class="block-selector-item" data-type="' + bt.type + '">' + (E.BLOCK_ICONS[bt.icon] || '') + ' ' + bt.name + '</div>';
        });

        wrap.innerHTML =
            '<button type="button" class="block-adder-btn" title="Add Block">' +
                '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>' +
            '</button>' +
            '<div class="block-selector-menu">' + menuItems + '</div>';

        var btn = wrap.querySelector('.block-adder-btn');
        var menu = wrap.querySelector('.block-selector-menu');

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            btn.classList.toggle('active');
            menu.style.display = btn.classList.contains('active') ? 'grid' : 'none';
        });

        window.addEventListener('click', function () {
            btn.classList.remove('active');
            menu.style.display = 'none';
        });

        var items = menu.querySelectorAll('.block-selector-item');
        items.forEach(function (item) {
            item.addEventListener('click', function () {
                var type = item.getAttribute('data-type');
                E.insertNewBlockAfter(E.state.blocks.length - 1, type);
            });
        });

        return wrap;
    }

    // ═══════════════════════════════════════════════════════
    // INLINE BLOCK MENU (for + button on each block)
    // ═══════════════════════════════════════════════════════
    function toggleInlineBlockMenu(e, index, triggerBtn, position) {
        e.stopPropagation();

        var prior = document.querySelector('.inline-block-menu');
        if (prior) {
            prior.remove();
            if (parseInt(prior.dataset.index) === index) return;
        }

        var menu = document.createElement('div');
        menu.className = 'inline-block-menu block-selector-menu';
        menu.dataset.index = index;
        menu.style.display = 'grid';
        menu.style.position = 'absolute';
        menu.style.top = '100%';
        menu.style.left = '0';
        menu.style.zIndex = '600';

        E.BLOCK_TYPES.forEach(function (bt) {
            var item = document.createElement('div');
            item.className = 'block-selector-item';
            item.innerHTML = (E.BLOCK_ICONS[bt.icon] || '') + ' ' + bt.name;
            item.addEventListener('click', function (ev) {
                ev.stopPropagation();
                var insertIdx = position === 'before' ? index - 1 : index;
                E.insertNewBlockAfter(insertIdx, bt.type);
                menu.remove();
            });
            menu.appendChild(item);
        });

        triggerBtn.parentNode.style.position = 'relative';
        triggerBtn.parentNode.appendChild(menu);

        window.addEventListener('click', function () { menu.remove(); }, { once: true });
    }

    // ═══════════════════════════════════════════════════════
    // BLOCK CONVERTER POPUP MENU
    // ═══════════════════════════════════════════════════════
    function toggleBlockConverterMenu(e, index, triggerBtn) {
        e.stopPropagation();

        var prior = document.querySelector('.block-converter-dropdown');
        if (prior) {
            prior.parentNode.removeChild(prior);
            if (prior.dataset.index === String(index)) return;
        }

        var drop = document.createElement('div');
        drop.className = 'more-actions-dropdown block-converter-dropdown';
        drop.style.display = 'block';
        drop.dataset.index = index;
        drop.style.bottom = 'auto';
        drop.style.top = '28px';

        var types = [
            { type: 'paragraph', name: 'Paragraph' },
            { type: 'heading-2', name: 'Heading 2' },
            { type: 'heading-3', name: 'Heading 3' },
            { type: 'heading-4', name: 'Heading 4' },
            { type: 'quote', name: 'Blockquote' },
            { type: 'callout', name: 'Callout Box' },
            { type: 'list-bullet', name: 'Bullet List' },
            { type: 'list-ordered', name: 'Numbered List' },
            { type: 'code', name: 'Code Block' }
        ];

        types.forEach(function (t) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = t.name;
            if (E.state.blocks[index].type === t.type) {
                btn.style.color = 'var(--gold)';
                btn.style.fontWeight = '600';
            }
            btn.addEventListener('click', function () {
                E.convertBlockType(index, t.type);
                drop.parentNode.removeChild(drop);
            });
            drop.appendChild(btn);
        });

        triggerBtn.parentNode.appendChild(drop);

        window.addEventListener('click', function () {
            if (drop.parentNode) drop.parentNode.removeChild(drop);
        }, { once: true });
    }

    // Public API
    return {
        createBlockControls: createBlockControls,
        createBlockContentElement: createBlockContentElement,
        createBottomAdder: createBottomAdder,
        toggleInlineBlockMenu: toggleInlineBlockMenu,
        toggleBlockConverterMenu: toggleBlockConverterMenu
    };
})();
