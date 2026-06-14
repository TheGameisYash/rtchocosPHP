/* admin/editor.js - Core Engine for Vanilla JS Block-Based Rich Editor */

(function() {
    // Global editor state
    const state = {
        blocks: [],
        focusedIndex: null,
        undoStack: [],
        redoStack: [],
        autosaveKey: 'rt_blog_editor_autosave'
    };

    // DOM references
    const container = document.getElementById('editorBlocksContainer');
    const hiddenContentInput = document.getElementById('content');
    const charCountSpan = document.getElementById('char-count');
    const wordCountSpan = document.getElementById('word-count');
    const formatBar = document.getElementById('floatingFormatBar');
    const linkInputWrap = formatBar ? formatBar.querySelector('.format-bar-link-input') : null;
    const linkInput = formatBar ? formatBar.querySelector('.format-bar-link-input input') : null;

    // Initialize the editor
    window.initBlockEditor = function(initialMarkdown) {
        state.blocks = markdownToBlocks(initialMarkdown);
        if (state.blocks.length === 0) {
            state.blocks.push({ type: 'paragraph', content: '' });
        }
        renderBlocks();
        updateHiddenInput();
        setupAutosaveListener();
        checkAutosaveRecovery();
    };

    // SETUP EVENT LISTENERS
    document.addEventListener('mouseup', handleTextSelection);
    document.addEventListener('keyup', handleTextSelection);
    if (formatBar) {
        setupFormatBarListeners();
    }

    // MARKDOWN DESERIALIZATION (Markdown -> JSON Blocks)
    function markdownToBlocks(markdown) {
        if (!markdown) return [];
        
        // Clean line endings
        markdown = markdown.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
        
        const blocks = [];
        const lines = markdown.split('\n');
        let inCodeBlock = false;
        let codeContent = '';
        let codeLang = '';
        
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            
            // Code block logic
            if (line.startsWith('```')) {
                if (inCodeBlock) {
                    blocks.push({ type: 'code', content: codeContent.trim(), lang: codeLang });
                    codeContent = '';
                    inCodeBlock = false;
                } else {
                    inCodeBlock = true;
                    codeLang = line.substring(3).trim();
                }
                continue;
            }
            
            if (inCodeBlock) {
                codeContent += line + '\n';
                continue;
            }

            let trimmed = line.trim();
            if (trimmed === '') continue;

            // Headings
            if (trimmed.startsWith('## ')) {
                blocks.push({ type: 'heading-2', content: mdInlineToHtml(trimmed.substring(3)) });
            } else if (trimmed.startsWith('### ')) {
                blocks.push({ type: 'heading-3', content: mdInlineToHtml(trimmed.substring(4)) });
            } else if (trimmed.startsWith('#### ')) {
                blocks.push({ type: 'heading-4', content: mdInlineToHtml(trimmed.substring(5)) });
            }
            // Divider
            else if (trimmed === '---') {
                blocks.push({ type: 'divider' });
            }
            // Blockquotes & Callouts
            else if (trimmed.startsWith('> ')) {
                let quoteText = trimmed.substring(2);
                // Group contiguous quote lines
                while (i + 1 < lines.length && lines[i + 1].trim().startsWith('> ')) {
                    i++;
                    quoteText += ' ' + lines[i].trim().substring(2);
                }
                
                if (quoteText.startsWith('[!NOTE]') || quoteText.startsWith('[!TIP]') || quoteText.startsWith('[!WARNING]')) {
                    const clean = quoteText.replace(/\[!(NOTE|TIP|WARNING)\]/i, '').trim();
                    blocks.push({ type: 'callout', content: mdInlineToHtml(clean) });
                } else {
                    blocks.push({ type: 'quote', content: mdInlineToHtml(quoteText) });
                }
            }
            // YouTube embeds
            else if (trimmed.startsWith('[youtube](') && trimmed.endsWith(')')) {
                const url = trimmed.match(/\[youtube\]\((.*?)\)/)[1];
                blocks.push({ type: 'youtube', url: url });
            }
            else if (trimmed.includes('youtube.com/embed/')) {
                // If it is iframe html
                const srcMatch = trimmed.match(/src="(.*?)"/);
                const url = srcMatch ? srcMatch[1] : trimmed;
                blocks.push({ type: 'youtube', url: url });
            }
            // Lists (Contiguous grouping)
            else if (trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
                blocks.push({ type: 'list-bullet', content: mdInlineToHtml(trimmed.substring(2)) });
            }
            else if (/^\d+\.\s+/.test(trimmed)) {
                const numMatch = trimmed.match(/^(\d+)\.\s+(.+)$/);
                blocks.push({ type: 'list-ordered', content: mdInlineToHtml(numMatch[2]), index: numMatch[1] });
            }
            // Images: ![alt](src)
            else if (trimmed.startsWith('![') && trimmed.includes('](')) {
                const altMatch = trimmed.match(/!\[(.*?)\]\((.*?)\)/);
                if (altMatch) {
                    blocks.push({ type: 'image', url: altMatch[2], caption: altMatch[1] });
                }
            }
            // Regular Paragraph (contiguous lines)
            else {
                let pText = trimmed;
                while (i + 1 < lines.length && lines[i + 1].trim() !== '' && 
                       !lines[i + 1].trim().startsWith('##') && 
                       !lines[i + 1].trim().startsWith('- ') && 
                       !lines[i + 1].trim().startsWith('* ') && 
                       !/^\d+\.\s+/.test(lines[i + 1].trim()) &&
                       !lines[i + 1].trim().startsWith('> ') && 
                       !lines[i + 1].trim().startsWith('```')) {
                    i++;
                    pText += '\n' + lines[i].trim();
                }
                blocks.push({ type: 'paragraph', content: mdInlineToHtml(pText) });
            }
        }
        
        return blocks;
    }

    // Inline Markdown Parser to basic HTML tags (bold, italic, links)
    function mdInlineToHtml(text) {
        if (!text) return '';
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/_(.*?)_/g, '<em>$1</em>')
            .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2">$1</a>');
    }

    // MARKDOWN SERIALIZATION (JSON Blocks -> Markdown)
    window.blocksToMarkdown = function(blocksList = state.blocks) {
        let md = [];
        
        blocksList.forEach((block, idx) => {
            switch(block.type) {
                case 'paragraph':
                    if (block.content.trim() !== '') {
                        md.push(htmlToMdInline(block.content));
                    }
                    break;
                case 'heading-2':
                    md.push('## ' + htmlToMdInline(block.content));
                    break;
                case 'heading-3':
                    md.push('### ' + htmlToMdInline(block.content));
                    break;
                case 'heading-4':
                    md.push('#### ' + htmlToMdInline(block.content));
                    break;
                case 'quote':
                    md.push('> ' + htmlToMdInline(block.content));
                    break;
                case 'callout':
                    md.push('> [!NOTE]\n> ' + htmlToMdInline(block.content));
                    break;
                case 'divider':
                    md.push('---');
                    break;
                case 'list-bullet':
                    md.push('- ' + htmlToMdInline(block.content));
                    break;
                case 'list-ordered':
                    const index = block.index || (idx > 0 && blocksList[idx-1].type === 'list-ordered' ? parseInt(blocksList[idx-1].index) + 1 : 1);
                    block.index = index; // Keep cache updated
                    md.push(`${index}. ` + htmlToMdInline(block.content));
                    break;
                case 'code':
                    md.push('```' + (block.lang || '') + '\n' + block.content + '\n```');
                    break;
                case 'image':
                    if (block.url) {
                        md.push(`![${htmlToMdInline(block.caption || '')}](${block.url})`);
                    }
                    break;
                case 'youtube':
                    if (block.url) {
                        md.push(`[youtube](${block.url})`);
                    }
                    break;
            }
        });
        
        return md.join('\n\n');
    };

    function htmlToMdInline(html) {
        if (!html) return '';
        // Create an offline div to parse DOM nicely
        const div = document.createElement('div');
        div.innerHTML = html;
        
        // Convert strong tags to **
        const strongs = div.querySelectorAll('strong, b');
        strongs.forEach(s => { s.outerHTML = `**${s.innerHTML}**`; });
        
        // Convert em tags to *
        const ems = div.querySelectorAll('em, i');
        ems.forEach(e => { e.outerHTML = `*${e.innerHTML}*`; });
        
        // Convert links to [text](href)
        const links = div.querySelectorAll('a');
        links.forEach(l => { l.outerHTML = `[${l.innerHTML}](${l.getAttribute('href')})`; });
        
        // Convert linebreaks inside blocks
        const brs = div.querySelectorAll('br');
        brs.forEach(b => { b.outerHTML = '\n'; });
        
        return div.textContent.trim();
    }

    // RENDER BLOCKS TO EDITOR DOM
    function renderBlocks() {
        container.innerHTML = '';
        
        state.blocks.forEach((block, index) => {
            const blockWrapper = document.createElement('div');
            blockWrapper.className = 'editor-block-wrapper';
            blockWrapper.dataset.index = index;
            
            // Add Side Controls
            const controls = createBlockControls(index, block.type);
            blockWrapper.appendChild(controls);
            
            // Add Block Contents depending on type
            const content = createBlockContentElement(block, index);
            blockWrapper.appendChild(content);
            
            container.appendChild(blockWrapper);
        });

        // Add an extra "+" adder block at the bottom
        const adder = createBottomAdder();
        container.appendChild(adder);
        
        updateStats();
    }

    function createBlockControls(index, type) {
        const controls = document.createElement('div');
        controls.className = 'editor-block-controls';
        
        // Block type icon/selector btn
        const dragBtn = document.createElement('button');
        dragBtn.type = 'button';
        dragBtn.className = 'block-ctrl-btn';
        dragBtn.title = 'Change block type';
        dragBtn.innerHTML = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>`;
        dragBtn.addEventListener('click', (e) => toggleBlockConverterMenu(e, index, dragBtn));
        controls.appendChild(dragBtn);

        // Move Up button
        const upBtn = document.createElement('button');
        upBtn.type = 'button';
        upBtn.className = 'block-ctrl-btn';
        upBtn.title = 'Move Block Up';
        upBtn.innerHTML = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"></path></svg>`;
        upBtn.addEventListener('click', () => moveBlock(index, -1));
        if (index === 0) upBtn.style.opacity = '0.3';
        controls.appendChild(upBtn);

        // Move Down button
        const downBtn = document.createElement('button');
        downBtn.type = 'button';
        downBtn.className = 'block-ctrl-btn';
        downBtn.title = 'Move Block Down';
        downBtn.innerHTML = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"></path></svg>`;
        downBtn.addEventListener('click', () => moveBlock(index, 1));
        if (index === state.blocks.length - 1) downBtn.style.opacity = '0.3';
        controls.appendChild(downBtn);

        // Delete button
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'block-ctrl-btn btn-delete';
        delBtn.title = 'Delete Block';
        delBtn.innerHTML = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>`;
        delBtn.addEventListener('click', () => deleteBlock(index));
        controls.appendChild(delBtn);
        
        return controls;
    }

    function createBlockContentElement(block, index) {
        // Handle custom widget elements (Images, Dividers, YouTube, Code)
        if (block.type === 'divider') {
            const hr = document.createElement('hr');
            hr.className = 'block-divider';
            return hr;
        }
        
        if (block.type === 'image') {
            return createImageBlockElement(block, index);
        }

        if (block.type === 'youtube') {
            return createYoutubeBlockElement(block, index);
        }

        // Standard textable blocks (Paragraph, Headings, Quote, Lists, Code, Callout)
        const el = document.createElement('div');
        el.className = 'editor-block-content';
        el.contentEditable = true;
        
        // Add classes matching block type
        switch(block.type) {
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
                el.dataset.index = block.index || (index > 0 && state.blocks[index-1].type === 'list-ordered' ? parseInt(state.blocks[index-1].index) + 1 : 1);
                break;
        }

        el.innerHTML = block.content || '';
        
        // Placeholder helper
        if (el.innerHTML === '') {
            if (block.type === 'paragraph') el.setAttribute('placeholder', 'Write text here... Press / for blocks');
            else if (block.type.startsWith('heading')) el.setAttribute('placeholder', 'Heading');
        }

        // Keep local content synchronized
        el.addEventListener('input', (e) => {
            block.content = el.innerHTML;
            saveUndoState();
            updateHiddenInput();
            updateStats();
        });

        // NOTION-STYLE TYPING UX
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                // If in code block, allow normal newlines
                if (block.type === 'code') return;
                
                e.preventDefault();
                insertNewBlockAfter(index, 'paragraph');
            } 
            else if (e.key === 'Backspace' && el.innerHTML.trim() === '') {
                e.preventDefault();
                deleteBlock(index, true); // Move focus to previous
            }
        });

        el.addEventListener('focus', () => {
            state.focusedIndex = index;
        });

        return el;
    }

    // IMAGE BLOCK HTML BUILDER
    function createImageBlockElement(block, index) {
        const wrap = document.createElement('div');
        wrap.className = 'editor-block-content';

        if (!block.url) {
            // Un-uploaded placeholder grid
            const uploadBox = document.createElement('div');
            uploadBox.className = 'block-image-upload';
            uploadBox.innerHTML = `
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
                <p>Click or drag image here to upload</p>
                <span>PNG, JPG, WEBP &bull; Max 5MB</span>
                <input type="file" style="display:none;" accept="image/*">
            `;
            
            const fileInput = uploadBox.querySelector('input');
            uploadBox.addEventListener('click', () => fileInput.click());
            
            fileInput.addEventListener('change', (e) => {
                if (e.target.files && e.target.files[0]) {
                    uploadImageFile(e.target.files[0], block, index);
                }
            });
            
            // Drag and Drop
            uploadBox.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadBox.style.borderColor = 'var(--gold)';
            });
            uploadBox.addEventListener('dragleave', () => {
                uploadBox.style.borderColor = '';
            });
            uploadBox.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadBox.style.borderColor = '';
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    uploadImageFile(e.dataTransfer.files[0], block, index);
                }
            });

            wrap.appendChild(uploadBox);
        } else {
            // Already uploaded representation
            const displayBox = document.createElement('div');
            displayBox.className = 'block-image-uploaded';
            displayBox.innerHTML = `
                <img src="../${block.url}" alt="">
                <div class="block-image-details">
                    <input type="text" class="img-caption" placeholder="Write image caption..." value="${block.caption || ''}">
                </div>
            `;
            
            const captionInput = displayBox.querySelector('.img-caption');
            captionInput.addEventListener('input', (e) => {
                block.caption = e.target.value;
                updateHiddenInput();
            });

            wrap.appendChild(displayBox);
        }
        return wrap;
    }

    // AJAX IMAGE FILE UPLOAD HANDLER
    function uploadImageFile(file, block, index) {
        const formData = new FormData();
        formData.append('inline_image', file);
        formData.append('action', 'upload_inline_image');
        
        // Obtain token from post form
        const tokenEl = document.querySelector('input[name="csrf_token"]');
        if (tokenEl) {
            formData.append('csrf_token', tokenEl.value);
        }

        // Show uploading visual spinner state
        const wrapper = container.querySelector(`[data-index="${index}"] .editor-block-content`);
        wrapper.innerHTML = `
            <div style="padding: 30px; text-align: center; color: var(--text-light);">
                <div style="border: 3px solid rgba(199,166,106,0.2); border-top-color: var(--gold); border-radius: 50%; width: 24px; height: 24px; animation: spin 0.8s linear infinite; margin: 0 auto 12px;"></div>
                <span>Uploading Image...</span>
            </div>
        `;

        fetch('blog-editor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                block.url = data.url;
                block.caption = file.name.split('.')[0];
                renderBlocks();
                updateHiddenInput();
                showToast('Image uploaded successfully', 'success');
            } else {
                showToast(data.message || 'Image upload failed', 'danger');
                // Revert
                block.url = '';
                renderBlocks();
            }
        })
        .catch(err => {
            showToast('Network error during image upload', 'danger');
            block.url = '';
            renderBlocks();
        });
    }

    // YOUTUBE BLOCK HTML BUILDER
    function createYoutubeBlockElement(block, index) {
        const wrap = document.createElement('div');
        wrap.className = 'editor-block-content';

        if (!block.url) {
            const inputWidget = document.createElement('div');
            inputWidget.className = 'block-yt-embed-widget';
            inputWidget.innerHTML = `
                <div style="display:flex; gap:10px;">
                    <input type="text" placeholder="Paste YouTube URL here (e.g. https://www.youtube.com/watch?v=...)" style="flex-grow:1; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                    <button type="button" class="btn btn-secondary btn-sm" style="padding:8px 16px;">Embed</button>
                </div>
            `;
            const input = inputWidget.querySelector('input');
            const btn = inputWidget.querySelector('button');
            
            const handleEmbedSubmit = () => {
                const url = input.value.trim();
                const ytId = getYoutubeId(url);
                if (ytId) {
                    block.url = url;
                    renderBlocks();
                    updateHiddenInput();
                } else {
                    showToast('Invalid YouTube URL', 'danger');
                }
            };
            btn.addEventListener('click', handleEmbedSubmit);
            input.addEventListener('keydown', (e) => { if (e.key === 'Enter') handleEmbedSubmit(); });

            wrap.appendChild(inputWidget);
        } else {
            const ytId = getYoutubeId(block.url);
            const previewWidget = document.createElement('div');
            previewWidget.className = 'block-yt-embed-widget';
            previewWidget.innerHTML = `
                <div class="yt-preview-box">
                    <iframe src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen></iframe>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11.5px; color:var(--text-light); font-family: monospace;">Video ID: ${ytId}</span>
                    <button type="button" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:11px;">Change Video</button>
                </div>
            `;
            
            const changeBtn = previewWidget.querySelector('button');
            changeBtn.addEventListener('click', () => {
                block.url = '';
                renderBlocks();
            });

            wrap.appendChild(previewWidget);
        }
        return wrap;
    }

    function getYoutubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    // FLOATING PLUS (+) ADDER AT BOTTOM
    function createBottomAdder() {
        const wrap = document.createElement('div');
        wrap.className = 'block-adder-wrap';
        wrap.innerHTML = `
            <button type="button" class="block-adder-btn" title="Add Block">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            </button>
            <div class="block-selector-menu">
                <div class="block-selector-item" data-type="paragraph"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7"></path></svg> Paragraph</div>
                <div class="block-selector-item" data-type="heading-2"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg> Heading 2</div>
                <div class="block-selector-item" data-type="heading-3"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg> Heading 3</div>
                <div class="block-selector-item" data-type="list-bullet"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg> Bullet List</div>
                <div class="block-selector-item" data-type="list-ordered"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg> Numbered List</div>
                <div class="block-selector-item" data-type="quote"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg> Blockquote</div>
                <div class="block-selector-item" data-type="image"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Image File</div>
                <div class="block-selector-item" data-type="youtube"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 10l-4 4v-4l4 4zm6-2.582A9.002 9.002 0 0012 3a9.002 9.002 0 00-9 9 9.002 9.002 0 009 9c4.97 0 9-4.03 9-9 0-1.89-.582-3.645-1.582-5.1z"></path></svg> YT Embed</div>
                <div class="block-selector-item" data-type="code"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Code Block</div>
                <div class="block-selector-item" data-type="callout"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Alert Callout</div>
                <div class="block-selector-item" data-type="divider"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 12H6"></path></svg> Divider Line</div>
            </div>
        `;
        
        const btn = wrap.querySelector('.block-adder-btn');
        const menu = wrap.querySelector('.block-selector-menu');
        
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            btn.classList.toggle('active');
            menu.style.display = btn.classList.contains('active') ? 'grid' : 'none';
        });

        // Click outside closes menu
        window.addEventListener('click', () => {
            btn.classList.remove('active');
            menu.style.display = 'none';
        });

        const items = menu.querySelectorAll('.block-selector-item');
        items.forEach(item => {
            item.addEventListener('click', () => {
                const type = item.getAttribute('data-type');
                insertNewBlockAfter(state.blocks.length - 1, type);
            });
        });

        return wrap;
    }

    // BLOCK CONVERTER POPUP MENU
    function toggleBlockConverterMenu(e, index, triggerBtn) {
        e.stopPropagation();
        
        // Remove active menus first
        const prior = document.querySelector('.block-converter-dropdown');
        if (prior) {
            prior.parentNode.removeChild(prior);
            if (prior.dataset.index === String(index)) return; // Toggle close
        }

        const drop = document.createElement('div');
        drop.className = 'more-actions-dropdown block-converter-dropdown';
        drop.style.display = 'block';
        drop.dataset.index = index;
        drop.style.bottom = 'auto';
        drop.style.top = '28px';
        
        const types = [
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

        types.forEach(t => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = t.name;
            btn.addEventListener('click', () => {
                convertBlockType(index, t.type);
                drop.parentNode.removeChild(drop);
            });
            drop.appendChild(btn);
        });

        triggerBtn.parentNode.appendChild(drop);
        
        window.addEventListener('click', () => {
            if (drop.parentNode) drop.parentNode.removeChild(drop);
        }, { once: true });
    }

    // INTERNAL STATE MANIPULATORS (CRUD)
    function insertNewBlockAfter(index, type) {
        saveUndoState();
        const newBlock = { type: type, content: '' };
        if (type === 'list-ordered') {
            newBlock.index = (index >= 0 && state.blocks[index].type === 'list-ordered') ? parseInt(state.blocks[index].index) + 1 : 1;
        }
        
        state.blocks.splice(index + 1, 0, newBlock);
        renderBlocks();
        updateHiddenInput();
        
        // Shift focus to new block content
        setTimeout(() => {
            const targetEl = container.querySelector(`[data-index="${index + 1}"] .editor-block-content`);
            if (targetEl) {
                targetEl.focus();
            }
        }, 50);
    }

    function convertBlockType(index, nextType) {
        saveUndoState();
        state.blocks[index].type = nextType;
        if (nextType === 'list-ordered') {
            state.blocks[index].index = (index > 0 && state.blocks[index-1].type === 'list-ordered') ? parseInt(state.blocks[index-1].index) + 1 : 1;
        }
        renderBlocks();
        updateHiddenInput();
        
        // Focus converted item
        setTimeout(() => {
            const targetEl = container.querySelector(`[data-index="${index}"] .editor-block-content`);
            if (targetEl) targetEl.focus();
        }, 50);
    }

    function moveBlock(index, offset) {
        const nextIndex = index + offset;
        if (nextIndex < 0 || nextIndex >= state.blocks.length) return;
        
        saveUndoState();
        const temp = state.blocks[index];
        state.blocks[index] = state.blocks[nextIndex];
        state.blocks[nextIndex] = temp;
        
        renderBlocks();
        updateHiddenInput();
        
        // Focus moved block
        setTimeout(() => {
            const targetEl = container.querySelector(`[data-index="${nextIndex}"] .editor-block-content`);
            if (targetEl) targetEl.focus();
        }, 50);
    }

    function deleteBlock(index, focusPrevious = false) {
        if (state.blocks.length <= 1) {
            // Keep at least 1 empty block
            saveUndoState();
            state.blocks[0] = { type: 'paragraph', content: '' };
            renderBlocks();
            updateHiddenInput();
            return;
        }

        saveUndoState();
        state.blocks.splice(index, 1);
        renderBlocks();
        updateHiddenInput();

        // Focus logic
        if (focusPrevious) {
            const prevIndex = Math.max(0, index - 1);
            setTimeout(() => {
                const targetEl = container.querySelector(`[data-index="${prevIndex}"] .editor-block-content`);
                if (targetEl) {
                    targetEl.focus();
                    // Place caret at end of text
                    placeCaretAtEnd(targetEl);
                }
            }, 50);
        }
    }

    function placeCaretAtEnd(el) {
        el.focus();
        if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
            const range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    // STATS GENERATOR
    function updateStats() {
        const markdown = blocksToMarkdown();
        const charCount = markdown.length;
        const wordCount = markdown.trim() === '' ? 0 : markdown.trim().split(/\s+/).length;
        
        if (charCountSpan) charCountSpan.innerText = charCount + ' character' + (charCount !== 1 ? 's' : '');
        if (wordCountSpan) wordCountSpan.innerText = wordCount + ' word' + (wordCount !== 1 ? 's' : '');

        // Update read time estimate inside details input if empty or auto-updating
        const readTimeInput = document.getElementById('read_time');
        if (readTimeInput) {
            const mins = Math.max(1, Math.ceil(wordCount / 200));
            readTimeInput.placeholder = mins + ' min';
        }

        // Live compile HTML preview inside iframe/preview area if present
        const livePreviewEl = document.getElementById('preview');
        if (livePreviewEl && typeof parseMarkdown === 'function') {
            livePreviewEl.innerHTML = parseMarkdown(markdown);
        }
    }

    function updateHiddenInput() {
        if (hiddenContentInput) {
            hiddenContentInput.value = blocksToMarkdown();
        }
    }

    // UNDO/REDO LOGIC STACKS
    function saveUndoState() {
        // Simple deep clone state
        const snap = JSON.stringify(state.blocks);
        if (state.undoStack.length === 0 || state.undoStack[state.undoStack.length - 1] !== snap) {
            state.undoStack.push(snap);
            if (state.undoStack.length > 30) state.undoStack.shift(); // Cap undo memory
            state.redoStack = []; // Clear redo
        }
    }

    window.triggerUndo = function() {
        if (state.undoStack.length > 0) {
            const snap = state.undoStack.pop();
            state.redoStack.push(JSON.stringify(state.blocks));
            state.blocks = JSON.parse(snap);
            renderBlocks();
            updateHiddenInput();
        } else {
            showToast('Nothing to undo', 'info');
        }
    };

    window.triggerRedo = function() {
        if (state.redoStack.length > 0) {
            const snap = state.redoStack.pop();
            state.undoStack.push(JSON.stringify(state.blocks));
            state.blocks = JSON.parse(snap);
            renderBlocks();
            updateHiddenInput();
        } else {
            showToast('Nothing to redo', 'info');
        }
    };

    // Keyboard shortcuts listeners
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'z') {
            e.preventDefault();
            window.triggerUndo();
        }
        else if ((e.ctrlKey || e.metaKey) && e.key === 'y') {
            e.preventDefault();
            window.triggerRedo();
        }
    });

    // TEXT SELECTION FORMATTING POPUP TOOLBAR
    function handleTextSelection() {
        const selection = window.getSelection();
        if (!selection.rangeCount || selection.isCollapsed) {
            if (formatBar) formatBar.style.display = 'none';
            return;
        }

        // Check if selection is within a contenteditable block
        const containerNode = selection.anchorNode.parentNode;
        const blockEl = containerNode.closest('.editor-block-content');
        
        if (!blockEl || blockEl.closest('.block-code')) {
            if (formatBar) formatBar.style.display = 'none';
            return;
        }

        // Show toolbar and calculate coordinates
        const range = selection.getRangeAt(0);
        const rect = range.getBoundingClientRect();
        
        if (formatBar) {
            formatBar.style.display = 'flex';
            linkInputWrap.style.display = 'none'; // Reset link panel
            
            const x = rect.left + window.pageXOffset + (rect.width / 2) - (formatBar.offsetWidth / 2);
            const y = rect.top + window.pageYOffset - formatBar.offsetHeight - 10;
            
            formatBar.style.left = Math.max(10, x) + 'px';
            formatBar.style.top = Math.max(10, y) + 'px';
            
            // Check active button tags
            updateFormatBtnStates();
        }
    }

    function updateFormatBtnStates() {
        const btnBold = formatBar.querySelector('[data-cmd="bold"]');
        const btnItalic = formatBar.querySelector('[data-cmd="italic"]');
        const btnUnderline = formatBar.querySelector('[data-cmd="underline"]');
        
        if (btnBold) btnBold.classList.toggle('active', document.queryCommandState('bold'));
        if (btnItalic) btnItalic.classList.toggle('active', document.queryCommandState('italic'));
        if (btnUnderline) btnUnderline.classList.toggle('active', document.queryCommandState('underline'));
    }

    function setupFormatBarListeners() {
        const btns = formatBar.querySelectorAll('.format-bar-btn[data-cmd]');
        btns.forEach(btn => {
            btn.addEventListener('mousedown', (e) => {
                e.preventDefault(); // Stop focus change
                const cmd = btn.getAttribute('data-cmd');
                
                if (cmd === 'createLink') {
                    // Show Link input box
                    linkInputWrap.style.display = 'flex';
                    linkInput.focus();
                    linkInput.value = '';
                } else {
                    document.execCommand(cmd, false, null);
                    updateFormatBtnStates();
                    // Sync focused block content
                    if (state.focusedIndex !== null) {
                        const targetEl = container.querySelector(`[data-index="${state.focusedIndex}"] .editor-block-content`);
                        if (targetEl) {
                            state.blocks[state.focusedIndex].content = targetEl.innerHTML;
                            updateHiddenInput();
                            updateStats();
                        }
                    }
                }
            });
        });

        // Link submit
        const linkSubmit = formatBar.querySelector('.link-submit');
        if (linkSubmit) {
            linkSubmit.addEventListener('mousedown', (e) => {
                e.preventDefault();
                const url = linkInput.value.trim();
                if (url) {
                    document.execCommand('createLink', false, url);
                    if (state.focusedIndex !== null) {
                        const targetEl = container.querySelector(`[data-index="${state.focusedIndex}"] .editor-block-content`);
                        if (targetEl) {
                            state.blocks[state.focusedIndex].content = targetEl.innerHTML;
                            updateHiddenInput();
                            updateStats();
                        }
                    }
                }
                formatBar.style.display = 'none';
            });
        }
    }

    // AUTOSAVE BACKUP LOGIC
    function setupAutosaveListener() {
        // Save state to localStorage every 20 seconds if changes occur
        let lastState = JSON.stringify(state.blocks);
        setInterval(() => {
            const currentState = JSON.stringify(state.blocks);
            if (currentState !== lastState) {
                const autoSaveObj = {
                    blocks: state.blocks,
                    timestamp: new Date().getTime(),
                    id: document.getElementById('blogId') ? document.getElementById('blogId').value : 0
                };
                localStorage.setItem(state.autosaveKey, JSON.stringify(autoSaveObj));
                lastState = currentState;
                
                // Show tiny indicator in top layout header
                const indicator = document.getElementById('autosaveIndicator');
                if (indicator) {
                    indicator.innerText = 'Draft autosaved at ' + new Date().toLocaleTimeString();
                    indicator.style.opacity = 1;
                    setTimeout(() => { indicator.style.opacity = 0.5; }, 3000);
                }
            }
        }, 20000);
    }

    function checkAutosaveRecovery() {
        const raw = localStorage.getItem(state.autosaveKey);
        if (!raw) return;
        
        try {
            const saved = JSON.parse(raw);
            const currentId = document.getElementById('blogId') ? document.getElementById('blogId').value : 0;
            
            // Validate backup fits current edit ID and is recent (within 24 hours)
            if (saved.id == currentId && (new Date().getTime() - saved.timestamp < 86400000)) {
                // Check if different from loaded database values
                if (JSON.stringify(saved.blocks) !== JSON.stringify(state.blocks)) {
                    // Render recovery banner notice
                    const banner = document.createElement('div');
                    banner.className = 'alert alert-info';
                    banner.style.display = 'flex';
                    banner.style.justify = 'space-between';
                    banner.style.alignItems = 'center';
                    banner.style.marginBottom = '24px';
                    banner.innerHTML = `
                        <span><strong>Recover Unsaved Changes?</strong> We found a backup of this draft from ${new Date(saved.timestamp).toLocaleTimeString()}.</span>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm" id="btnRecoverAutosave">Recover</button>
                            <button type="button" class="btn btn-outline btn-sm" id="btnDismissAutosave" style="margin-left:8px;">Dismiss</button>
                        </div>
                    `;
                    
                    const topbar = document.querySelector('main.admin-main');
                    if (topbar) {
                        topbar.insertBefore(banner, topbar.querySelector('.editor-canvas-wrapper'));
                    }
                    
                    document.getElementById('btnRecoverAutosave').addEventListener('click', () => {
                        saveUndoState();
                        state.blocks = saved.blocks;
                        renderBlocks();
                        updateHiddenInput();
                        banner.parentNode.removeChild(banner);
                        showToast('Draft restored from local backup', 'success');
                    });
                    
                    document.getElementById('btnDismissAutosave').addEventListener('click', () => {
                        localStorage.removeItem(state.autosaveKey);
                        banner.parentNode.removeChild(banner);
                    });
                }
            }
        } catch (e) {
            // Error parsing autosave
        }
    }

})();
