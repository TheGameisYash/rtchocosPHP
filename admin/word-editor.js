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
        if (/<(p|div|h[1-6]|ul|ol|blockquote|table|section)/i.test(raw)) {
            return raw;
        }

        // Otherwise parse Markdown blocks to HTML
        raw = raw.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        const blocks = raw.split(/\n\n+/);
        let html = '';

        blocks.forEach(block => {
            block = block.trim();
            if (!block) return;

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
                html += `<p><img src="${imgMatch[2]}" alt="${escapeHtml(imgMatch[1])}"></p>`;
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

    // EDITOR DOM & KEYBOARD EVENTS
    function setupEditorEvents() {
        // Sync content & stats on input
        editorDoc.addEventListener('input', () => {
            syncContent();
        });

        // Update toolbar active states on selection / caret change
        editorDoc.addEventListener('keyup', updateToolbarState);
        editorDoc.addEventListener('mouseup', updateToolbarState);
        document.addEventListener('selectionchange', () => {
            if (document.activeElement === editorDoc || editorDoc.contains(document.activeElement)) {
                updateToolbarState();
            }
        });

        // Keyboard shortcuts
        editorDoc.addEventListener('keydown', (e) => {
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
                if (url) {
                    insertHtmlAtCursor(`<p><img src="${url}" alt="${escapeHtml(alt)}" style="max-width:100%; border-radius:12px; margin:20px 0;"></p>`);
                    modal.style.display = 'none';
                }
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', () => {
                if (fileInput.files && fileInput.files[0]) {
                    uploadAndInsertImage(fileInput.files[0]);
                    modal.style.display = 'none';
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
                    modal.style.display = 'none';
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
            if (data.success && data.url) {
                insertHtmlAtCursor(`<p><img src="../${data.url}" alt="${escapeHtml(file.name)}" style="max-width:100%; border-radius:12px; margin:20px 0;"></p><p><br></p>`);
                syncContent();
                if (autosaveIndicator) {
                    autosaveIndicator.innerText = 'Image uploaded!';
                    setTimeout(() => { if (autosaveIndicator) autosaveIndicator.style.opacity = '0.5'; }, 1500);
                }
                if (window.showToast) showToast('Image inserted successfully!', 'success');
            } else {
                alert('Image upload failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
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
