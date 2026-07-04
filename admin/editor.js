/* admin/editor.js - Premium Rich Block Editor Engine v2 */

(function() {
    // Global editor state
    const state = {
        blocks: [],
        focusedIndex: null,
        undoStack: [],
        redoStack: [],
        autosaveKey: 'rt_blog_editor_autosave',
        slashMenuOpen: false,
        slashMenuFilter: '',
        slashMenuIndex: 0,
        dragSrcIndex: null
    };

    // DOM references
    const container = document.getElementById('editorBlocksContainer');
    const hiddenContentInput = document.getElementById('content');
    const charCountSpan = document.getElementById('char-count');
    const wordCountSpan = document.getElementById('word-count');
    const formatBar = document.getElementById('floatingFormatBar');
    const linkInputWrap = formatBar ? formatBar.querySelector('.format-bar-link-input') : null;
    const linkInput = formatBar ? formatBar.querySelector('.format-bar-link-input input') : null;

    // Block type definitions for menus
    const BLOCK_TYPES = [
        { type: 'paragraph', name: 'Paragraph', icon: 'text', shortcut: '/p', desc: 'Plain text block' },
        { type: 'heading-2', name: 'Heading 2', icon: 'h2', shortcut: '/h2', desc: 'Large section heading' },
        { type: 'heading-3', name: 'Heading 3', icon: 'h3', shortcut: '/h3', desc: 'Medium section heading' },
        { type: 'heading-4', name: 'Heading 4', icon: 'h4', shortcut: '/h4', desc: 'Small heading' },
        { type: 'list-bullet', name: 'Bullet List', icon: 'list', shortcut: '/ul', desc: 'Unordered list item' },
        { type: 'list-ordered', name: 'Numbered List', icon: 'olist', shortcut: '/ol', desc: 'Ordered list item' },
        { type: 'quote', name: 'Blockquote', icon: 'quote', shortcut: '/quote', desc: 'Quoted text' },
        { type: 'callout', name: 'Callout Box', icon: 'callout', shortcut: '/callout', desc: 'Highlighted info box' },
        { type: 'image', name: 'Image', icon: 'image', shortcut: '/img', desc: 'Upload an image' },
        { type: 'youtube', name: 'YouTube Embed', icon: 'youtube', shortcut: '/yt', desc: 'Embed a YouTube video' },
        { type: 'code', name: 'Code Block', icon: 'code', shortcut: '/code', desc: 'Monospace code block' },
        { type: 'divider', name: 'Divider', icon: 'divider', shortcut: '/hr', desc: 'Horizontal separator' },
        { type: 'table', name: 'Table', icon: 'table', shortcut: '/table', desc: 'Data table' }
    ];

    const BLOCK_ICONS = {
        text: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h7"></path></svg>',
        h2: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h10"></path></svg>',
        h3: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h12M4 12h8"></path></svg>',
        h4: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h8M4 12h6"></path></svg>',
        list: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="4" cy="6" r="1.5" fill="currentColor"/><path d="M9 6h11M9 12h11M9 18h11"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>',
        olist: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 6h11M9 12h11M9 18h11"/><text x="2" y="8" font-size="8" fill="currentColor" font-family="sans-serif">1</text><text x="2" y="14" font-size="8" fill="currentColor" font-family="sans-serif">2</text><text x="2" y="20" font-size="8" fill="currentColor" font-family="sans-serif">3</text></svg>',
        quote: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2v6l-2-2m18 0l-2 2V10l2 2M8 8h3v8H8zm5 0h3v8h-3z"></path></svg>',
        callout: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        image: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
        youtube: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        code: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
        divider: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14"></path></svg>',
        table: '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M3 14h18M8 4v16M16 4v16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z"></path></svg>'
    };

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
        setupToolbar();
    };

    // SETUP EVENT LISTENERS
    document.addEventListener('mouseup', handleTextSelection);
    document.addEventListener('keyup', handleTextSelection);
    if (formatBar) {
        setupFormatBarListeners();
    }

    // Close slash menu on outside click
    document.addEventListener('click', (e) => {
        if (state.slashMenuOpen && !e.target.closest('.slash-command-menu')) {
            closeSlashMenu();
        }
    });

    // ═══════════════════════════════════════════════════════
    // MARKDOWN DESERIALIZATION (Markdown -> JSON Blocks)
    // ═══════════════════════════════════════════════════════
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
            else if (trimmed.startsWith('{{youtube:') && trimmed.endsWith('}}')) {
                const ytId = trimmed.replace('{{youtube:', '').replace('}}', '').trim();
                blocks.push({ type: 'youtube', url: `https://www.youtube.com/watch?v=${ytId}` });
            }
            else if (trimmed.startsWith('[youtube](') && trimmed.endsWith(')')) {
                const url = trimmed.match(/\[youtube\]\((.*?)\)/)[1];
                blocks.push({ type: 'youtube', url: url });
            }
            else if (trimmed.includes('youtube.com/embed/')) {
                const srcMatch = trimmed.match(/src="(.*?)"/);
                const url = srcMatch ? srcMatch[1] : trimmed;
                blocks.push({ type: 'youtube', url: url });
            }
            // Table blocks
            else if (trimmed.startsWith('|')) {
                let tableContent = trimmed;
                while (i + 1 < lines.length && lines[i + 1].trim().startsWith('|')) {
                    i++;
                    tableContent += '\n' + lines[i].trim();
                }
                blocks.push({ type: 'table', content: tableContent });
            }
            // Lists (each item is a block)
            else if (trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
                blocks.push({ type: 'list-bullet', content: mdInlineToHtml(trimmed.substring(2)) });
            }
            else if (/^\d+\.\s+/.test(trimmed)) {
                const numMatch = trimmed.match(/^(\d+)\.\s+(.+)$/);
                blocks.push({ type: 'list-ordered', content: mdInlineToHtml(numMatch[2]), index: numMatch[1] });
            }
            // Images: ![alt](src){position}
            else if (trimmed.startsWith('![') && trimmed.includes('](')) {
                const altMatch = trimmed.match(/!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|end)\})?/);
                if (altMatch) {
                    blocks.push({ 
                        type: 'image', 
                        url: altMatch[2], 
                        caption: altMatch[1], 
                        position: altMatch[3] || 'center' 
                    });
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
                       !lines[i + 1].trim().startsWith('```') &&
                       !lines[i + 1].trim().startsWith('|') &&
                       !lines[i + 1].trim().startsWith('![') &&
                       !lines[i + 1].trim().startsWith('{{youtube:') &&
                       lines[i + 1].trim() !== '---') {
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

    // ═══════════════════════════════════════════════════════
    // MARKDOWN SERIALIZATION (JSON Blocks -> Markdown)
    // FIX: Consecutive list items joined with \n not \n\n
    // ═══════════════════════════════════════════════════════
    window.blocksToMarkdown = function(blocksList = state.blocks) {
        let result = '';
        
        // First, recalculate all ordered list indices
        recalcListIndices(blocksList);
        
        blocksList.forEach((block, idx) => {
            let line = '';
            
            switch(block.type) {
                case 'paragraph':
                    if (block.content.trim() !== '') {
                        line = htmlToMdInline(block.content);
                    }
                    break;
                case 'heading-2':
                    line = '## ' + htmlToMdInline(block.content);
                    break;
                case 'heading-3':
                    line = '### ' + htmlToMdInline(block.content);
                    break;
                case 'heading-4':
                    line = '#### ' + htmlToMdInline(block.content);
                    break;
                case 'quote':
                    line = '> ' + htmlToMdInline(block.content);
                    break;
                case 'callout':
                    line = '> [!NOTE]\n> ' + htmlToMdInline(block.content);
                    break;
                case 'divider':
                    line = '---';
                    break;
                case 'list-bullet':
                    line = '- ' + htmlToMdInline(block.content);
                    break;
                case 'list-ordered':
                    line = `${block.index || 1}. ` + htmlToMdInline(block.content);
                    break;
                case 'code':
                    line = '```' + (block.lang || '') + '\n' + block.content + '\n```';
                    break;
                case 'image':
                    if (block.url) {
                        const pos = block.position ? `{${block.position}}` : '';
                        line = `![${htmlToMdInline(block.caption || '')}](${block.url})${pos}`;
                    }
                    break;
                case 'youtube':
                    if (block.url) {
                        const ytId = getYoutubeId(block.url);
                        if (ytId) {
                            line = `{{youtube:${ytId}}}`;
                        } else {
                            line = `[youtube](${block.url})`;
                        }
                    }
                    break;
                case 'table':
                    line = block.content || '';
                    break;
            }
            
            if (line === '') return;
            
            // KEY FIX: Join consecutive list items with single newline
            // so the PHP parser treats them as one <ol>/<ul> block
            if (idx > 0 && result !== '') {
                const prevBlock = blocksList[idx - 1];
                const isContinuousList = 
                    (block.type === 'list-bullet' && prevBlock.type === 'list-bullet') ||
                    (block.type === 'list-ordered' && prevBlock.type === 'list-ordered');
                
                if (isContinuousList) {
                    result += '\n' + line;   // Single newline — same list
                } else {
                    result += '\n\n' + line;  // Double newline — new block
                }
            } else {
                result += line;
            }
        });
        
        return result;
    };

    // Recalculate ordered list indices for consecutive list-ordered blocks
    function recalcListIndices(blocksList) {
        let counter = 0;
        for (let i = 0; i < blocksList.length; i++) {
            if (blocksList[i].type === 'list-ordered') {
                if (i === 0 || blocksList[i - 1].type !== 'list-ordered') {
                    counter = 1; // Start new list
                } else {
                    counter++;
                }
                blocksList[i].index = counter;
            }
        }
    }

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
        
        // Convert underlines to <u> markdown-safe
        const underlines = div.querySelectorAll('u');
        underlines.forEach(u => { u.outerHTML = u.innerHTML; });
        
        // Convert links to [text](href)
        const links = div.querySelectorAll('a');
        links.forEach(l => { l.outerHTML = `[${l.innerHTML}](${l.getAttribute('href')})`; });
        
        // Convert linebreaks inside blocks
        const brs = div.querySelectorAll('br');
        brs.forEach(b => { b.outerHTML = '\n'; });
        
        return div.textContent.trim();
    }

    // ═══════════════════════════════════════════════════════
    // RENDER BLOCKS TO EDITOR DOM
    // ═══════════════════════════════════════════════════════
    function renderBlocks() {
        container.innerHTML = '';
        
        // Recalculate list indices before rendering
        recalcListIndices(state.blocks);
        
        state.blocks.forEach((block, index) => {
            const blockWrapper = document.createElement('div');
            blockWrapper.className = 'editor-block-wrapper';
            blockWrapper.dataset.index = index;
            
            // Add drag handle
            blockWrapper.draggable = true;
            blockWrapper.addEventListener('dragstart', (e) => handleDragStart(e, index));
            blockWrapper.addEventListener('dragover', (e) => handleDragOver(e, index));
            blockWrapper.addEventListener('drop', (e) => handleDrop(e, index));
            blockWrapper.addEventListener('dragend', handleDragEnd);
            blockWrapper.addEventListener('dragenter', (e) => handleDragEnter(e, index));
            blockWrapper.addEventListener('dragleave', handleDragLeave);
            
            // Add Side Controls
            const controls = createBlockControls(index, block.type);
            blockWrapper.appendChild(controls);
            
            // Add Block Contents depending on type
            const content = createBlockContentElement(block, index);
            blockWrapper.appendChild(content);
            
            container.appendChild(blockWrapper);
        });

        // Add the bottom adder
        const adder = createBottomAdder();
        container.appendChild(adder);
        
        updateStats();
    }

    // ═══════════════════════════════════════════════════════
    // DRAG AND DROP
    // ═══════════════════════════════════════════════════════
    function handleDragStart(e, index) {
        state.dragSrcIndex = index;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', index);
        
        // Add visual feedback
        setTimeout(() => {
            const wrapper = container.querySelector(`[data-index="${index}"]`);
            if (wrapper) wrapper.classList.add('dragging');
        }, 0);
    }

    function handleDragOver(e, index) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDragEnter(e, index) {
        e.preventDefault();
        const wrapper = container.querySelector(`[data-index="${index}"]`);
        if (wrapper && state.dragSrcIndex !== index) {
            wrapper.classList.add('drag-over');
        }
    }

    function handleDragLeave(e) {
        e.target.closest('.editor-block-wrapper')?.classList.remove('drag-over');
    }

    function handleDrop(e, targetIndex) {
        e.preventDefault();
        const srcIndex = state.dragSrcIndex;
        
        // Clean up visual states
        document.querySelectorAll('.drag-over, .dragging').forEach(el => {
            el.classList.remove('drag-over', 'dragging');
        });
        
        if (srcIndex === null || srcIndex === targetIndex) return;
        
        saveUndoState();
        const movedBlock = state.blocks.splice(srcIndex, 1)[0];
        state.blocks.splice(targetIndex, 0, movedBlock);
        
        renderBlocks();
        updateHiddenInput();
    }

    function handleDragEnd() {
        state.dragSrcIndex = null;
        document.querySelectorAll('.drag-over, .dragging').forEach(el => {
            el.classList.remove('drag-over', 'dragging');
        });
    }

    // ═══════════════════════════════════════════════════════
    // BLOCK CONTROLS (side buttons)
    // ═══════════════════════════════════════════════════════
    function createBlockControls(index, type) {
        const controls = document.createElement('div');
        controls.className = 'editor-block-controls';

        // Add Plus button to insert block before
        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'block-ctrl-btn block-ctrl-add';
        addBtn.title = 'Add block above';
        addBtn.innerHTML = `<svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>`;
        addBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleInlineBlockMenu(e, index, addBtn, 'before');
        });
        controls.appendChild(addBtn);
        
        // Drag handle / block type selector
        const dragBtn = document.createElement('button');
        dragBtn.type = 'button';
        dragBtn.className = 'block-ctrl-btn block-ctrl-drag';
        dragBtn.title = 'Drag to reorder · Click to change type';
        dragBtn.innerHTML = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 8h16M4 16h16"></path><circle cx="4" cy="8" r="1" fill="currentColor"/><circle cx="4" cy="16" r="1" fill="currentColor"/><circle cx="10" cy="8" r="1" fill="currentColor"/><circle cx="10" cy="16" r="1" fill="currentColor"/></svg>`;
        dragBtn.addEventListener('click', (e) => toggleBlockConverterMenu(e, index, dragBtn));
        controls.appendChild(dragBtn);

        // Delete button
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'block-ctrl-btn block-ctrl-delete';
        delBtn.title = 'Delete Block';
        delBtn.innerHTML = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>`;
        delBtn.addEventListener('click', () => deleteBlock(index));
        controls.appendChild(delBtn);
        
        return controls;
    }

    // ═══════════════════════════════════════════════════════
    // BLOCK CONTENT ELEMENTS
    // ═══════════════════════════════════════════════════════
    function createBlockContentElement(block, index) {
        // Handle custom widget elements (Images, Dividers, YouTube, Code, Table)
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

        if (block.type === 'table') {
            return createTableBlockElement(block, index);
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
                el.dataset.index = block.index || 1;
                break;
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

        // Keep local content synchronized
        el.addEventListener('input', (e) => {
            block.content = el.innerHTML;
            
            // Slash command detection
            const textContent = el.textContent;
            if (textContent.startsWith('/') && block.type === 'paragraph') {
                const filter = textContent.substring(1).toLowerCase();
                openSlashMenu(el, index, filter);
            } else if (state.slashMenuOpen) {
                closeSlashMenu();
            }
            
            saveUndoState();
            updateHiddenInput();
            updateStats();
        });

        // RICH PASTE HANDLER
        el.addEventListener('paste', (e) => handleRichPaste(e, block, index));

        // KEYBOARD HANDLING
        el.addEventListener('keydown', (e) => {
            // Slash menu keyboard navigation
            if (state.slashMenuOpen) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    navigateSlashMenu(1);
                    return;
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    navigateSlashMenu(-1);
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    selectSlashMenuItem();
                    return;
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    closeSlashMenu();
                    return;
                }
            }

            if (e.key === 'Enter' && !e.shiftKey) {
                // If in code block, allow normal newlines
                if (block.type === 'code') return;
                
                e.preventDefault();
                
                // AUTO-CONTINUE: If in a list, create next list item
                if (block.type === 'list-bullet') {
                    if (el.textContent.trim() === '') {
                        // Empty list item → convert to paragraph (exit list)
                        convertBlockType(index, 'paragraph');
                    } else {
                        insertNewBlockAfter(index, 'list-bullet');
                    }
                } else if (block.type === 'list-ordered') {
                    if (el.textContent.trim() === '') {
                        convertBlockType(index, 'paragraph');
                    } else {
                        insertNewBlockAfter(index, 'list-ordered');
                    }
                } else {
                    insertNewBlockAfter(index, 'paragraph');
                }
            } 
            else if (e.key === 'Backspace' && el.innerHTML.trim() === '') {
                e.preventDefault();
                deleteBlock(index, true);
            }
            // Arrow key navigation between blocks
            else if (e.key === 'ArrowUp' && isCaretAtStart(el)) {
                e.preventDefault();
                focusBlock(index - 1, 'end');
            }
            else if (e.key === 'ArrowDown' && isCaretAtEnd(el)) {
                e.preventDefault();
                focusBlock(index + 1, 'start');
            }
            // Tab handling for list indentation (future-proof, prevents default tab)
            else if (e.key === 'Tab') {
                if (block.type === 'code') return; // Allow tab in code blocks
                e.preventDefault();
                // For now, just insert spaces in code; lists can be extended later
            }
            // Markdown shortcuts
            else if (e.key === ' ' && block.type === 'paragraph') {
                const text = el.textContent;
                // Auto-convert ## to heading
                if (text === '##') {
                    e.preventDefault();
                    block.content = '';
                    convertBlockType(index, 'heading-2');
                } else if (text === '###') {
                    e.preventDefault();
                    block.content = '';
                    convertBlockType(index, 'heading-3');
                } else if (text === '####') {
                    e.preventDefault();
                    block.content = '';
                    convertBlockType(index, 'heading-4');
                } else if (text === '-' || text === '*') {
                    e.preventDefault();
                    block.content = '';
                    convertBlockType(index, 'list-bullet');
                } else if (text === '1.') {
                    e.preventDefault();
                    block.content = '';
                    convertBlockType(index, 'list-ordered');
                } else if (text === '>') {
                    e.preventDefault();
                    block.content = '';
                    convertBlockType(index, 'quote');
                } else if (text === '---') {
                    e.preventDefault();
                    block.content = '';
                    convertBlockType(index, 'divider');
                }
            }
        });

        el.addEventListener('focus', () => {
            state.focusedIndex = index;
        });

        return el;
    }

    // ═══════════════════════════════════════════════════════
    // RICH COPY-PASTE HANDLER
    // ═══════════════════════════════════════════════════════
    function handleRichPaste(e, block, index) {
        e.preventDefault();
        
        const clipboardData = e.clipboardData || window.clipboardData;
        const html = clipboardData.getData('text/html');
        const plainText = clipboardData.getData('text/plain');
        
        if (html && html.trim()) {
            // Parse pasted HTML into blocks
            const pastedBlocks = htmlToBlocks(html);
            
            if (pastedBlocks.length === 1) {
                // Single block paste — insert content into current block
                const cleanContent = pastedBlocks[0].content || '';
                document.execCommand('insertHTML', false, cleanContent);
                block.content = e.target.innerHTML;
                updateHiddenInput();
                updateStats();
            } else if (pastedBlocks.length > 1) {
                // Multi-block paste — insert all blocks after current
                saveUndoState();
                
                // Put first block content into current block if it's empty
                if (block.content.trim() === '' && pastedBlocks.length > 0) {
                    block.type = pastedBlocks[0].type;
                    block.content = pastedBlocks[0].content || '';
                    if (pastedBlocks[0].index) block.index = pastedBlocks[0].index;
                    pastedBlocks.shift();
                }
                
                // Insert remaining blocks after current index
                for (let i = 0; i < pastedBlocks.length; i++) {
                    state.blocks.splice(index + 1 + i, 0, pastedBlocks[i]);
                }
                
                renderBlocks();
                updateHiddenInput();
                
                // Focus last inserted block
                const lastIdx = index + pastedBlocks.length;
                setTimeout(() => focusBlock(lastIdx, 'end'), 50);
            }
        } else if (plainText) {
            // Plain text paste — check if it's markdown-like
            const lines = plainText.split('\n');
            const hasStructure = lines.some(l => 
                l.trim().startsWith('## ') || l.trim().startsWith('### ') ||
                l.trim().startsWith('- ') || l.trim().startsWith('* ') ||
                /^\d+\.\s+/.test(l.trim()) || l.trim().startsWith('> ')
            );
            
            if (hasStructure && lines.length > 1) {
                // Parse as markdown
                saveUndoState();
                const pastedBlocks = markdownToBlocks(plainText);
                
                if (block.content.trim() === '' && pastedBlocks.length > 0) {
                    block.type = pastedBlocks[0].type;
                    block.content = pastedBlocks[0].content || '';
                    if (pastedBlocks[0].index) block.index = pastedBlocks[0].index;
                    pastedBlocks.shift();
                }
                
                for (let i = 0; i < pastedBlocks.length; i++) {
                    state.blocks.splice(index + 1 + i, 0, pastedBlocks[i]);
                }
                
                renderBlocks();
                updateHiddenInput();
                const lastIdx = index + pastedBlocks.length;
                setTimeout(() => focusBlock(lastIdx, 'end'), 50);
            } else {
                // Just plain text — insert normally
                document.execCommand('insertText', false, plainText);
                block.content = e.target.innerHTML;
                updateHiddenInput();
                updateStats();
            }
        }
    }

    // Parse pasted HTML into block structures
    function htmlToBlocks(html) {
        const container = document.createElement('div');
        container.innerHTML = html;
        
        // Remove Word/Google Docs cruft
        container.querySelectorAll('style, script, meta, link, o\\:p, xml').forEach(el => el.remove());
        container.querySelectorAll('[class*="Mso"]').forEach(el => {
            el.removeAttribute('class');
            el.removeAttribute('style');
        });
        container.querySelectorAll('[style]').forEach(el => {
            // Keep only meaningful styles
            const style = el.getAttribute('style');
            if (style && !style.includes('font-weight') && !style.includes('font-style') && !style.includes('text-decoration')) {
                el.removeAttribute('style');
            }
        });
        
        const blocks = [];
        
        function processNode(node) {
            if (node.nodeType === 3) {
                // Text node
                const text = node.textContent.trim();
                if (text) {
                    blocks.push({ type: 'paragraph', content: text });
                }
                return;
            }
            
            if (node.nodeType !== 1) return;
            
            const tag = node.tagName.toLowerCase();
            
            switch(tag) {
                case 'h1':
                case 'h2':
                    blocks.push({ type: 'heading-2', content: cleanInlineHtml(node.innerHTML) });
                    break;
                case 'h3':
                    blocks.push({ type: 'heading-3', content: cleanInlineHtml(node.innerHTML) });
                    break;
                case 'h4':
                case 'h5':
                case 'h6':
                    blocks.push({ type: 'heading-4', content: cleanInlineHtml(node.innerHTML) });
                    break;
                case 'p':
                    const pContent = cleanInlineHtml(node.innerHTML).trim();
                    if (pContent) {
                        blocks.push({ type: 'paragraph', content: pContent });
                    }
                    break;
                case 'blockquote':
                    blocks.push({ type: 'quote', content: cleanInlineHtml(node.textContent) });
                    break;
                case 'ul':
                    node.querySelectorAll(':scope > li').forEach(li => {
                        blocks.push({ type: 'list-bullet', content: cleanInlineHtml(li.innerHTML) });
                    });
                    break;
                case 'ol':
                    let olIndex = parseInt(node.getAttribute('start')) || 1;
                    node.querySelectorAll(':scope > li').forEach(li => {
                        blocks.push({ type: 'list-ordered', content: cleanInlineHtml(li.innerHTML), index: olIndex });
                        olIndex++;
                    });
                    break;
                case 'pre':
                    blocks.push({ type: 'code', content: node.textContent, lang: '' });
                    break;
                case 'hr':
                    blocks.push({ type: 'divider' });
                    break;
                case 'img':
                    // Skip pasted images (data URIs etc)
                    break;
                case 'table':
                    // Convert table to markdown table format
                    const tableBlk = htmlTableToMarkdown(node);
                    if (tableBlk) blocks.push({ type: 'table', content: tableBlk });
                    break;
                case 'div':
                case 'section':
                case 'article':
                case 'main':
                case 'span':
                    // Recurse into container elements
                    Array.from(node.childNodes).forEach(child => processNode(child));
                    break;
                case 'br':
                    break;
                default:
                    // For other elements, try to extract text
                    const txt = cleanInlineHtml(node.innerHTML).trim();
                    if (txt) {
                        blocks.push({ type: 'paragraph', content: txt });
                    }
            }
        }
        
        Array.from(container.childNodes).forEach(child => processNode(child));
        
        return blocks;
    }

    function htmlTableToMarkdown(tableEl) {
        const rows = tableEl.querySelectorAll('tr');
        if (rows.length === 0) return null;
        
        let md = '';
        rows.forEach((row, idx) => {
            const cells = row.querySelectorAll('th, td');
            const cellValues = Array.from(cells).map(c => c.textContent.trim());
            md += '| ' + cellValues.join(' | ') + ' |\n';
            
            // Add separator after header row
            if (idx === 0) {
                md += '| ' + cellValues.map(() => '---').join(' | ') + ' |\n';
            }
        });
        
        return md.trim();
    }

    // Clean pasted HTML to only keep inline formatting
    function cleanInlineHtml(html) {
        if (!html) return '';
        const div = document.createElement('div');
        div.innerHTML = html;
        
        // Remove all block-level and non-inline elements
        div.querySelectorAll('div, p, span, br, img, table, tr, td, th, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, code, blockquote, hr, style, script').forEach(el => {
            if (['span'].includes(el.tagName.toLowerCase())) {
                // Unwrap spans but keep content
                el.outerHTML = el.innerHTML;
            }
        });
        
        // Keep: strong, b, em, i, a, u, s, code
        // Remove: everything else that's not inline formatting
        
        return div.innerHTML
            .replace(/<\/?(?!strong|b|em|i|a|u|s|code|\/strong|\/b|\/em|\/i|\/a|\/u|\/s|\/code)[^>]*>/gi, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    // ═══════════════════════════════════════════════════════
    // SLASH COMMAND MENU
    // ═══════════════════════════════════════════════════════
    function openSlashMenu(el, blockIndex, filter) {
        closeSlashMenu(); // Close existing
        
        state.slashMenuOpen = true;
        state.slashMenuFilter = filter;
        state.slashMenuIndex = 0;
        
        const menu = document.createElement('div');
        menu.className = 'slash-command-menu';
        menu.id = 'slashCommandMenu';
        
        const filteredTypes = BLOCK_TYPES.filter(bt => {
            if (!filter) return true;
            return bt.name.toLowerCase().includes(filter) || 
                   bt.shortcut.includes('/' + filter) ||
                   bt.desc.toLowerCase().includes(filter);
        });
        
        if (filteredTypes.length === 0) {
            const emptyEl = document.createElement('div');
            emptyEl.className = 'slash-menu-empty';
            emptyEl.textContent = 'No blocks found';
            menu.appendChild(emptyEl);
        } else {
            const header = document.createElement('div');
            header.className = 'slash-menu-header';
            header.textContent = 'Insert Block';
            menu.appendChild(header);
            
            filteredTypes.forEach((bt, i) => {
                const item = document.createElement('div');
                item.className = 'slash-menu-item' + (i === 0 ? ' active' : '');
                item.dataset.type = bt.type;
                item.dataset.menuIdx = i;
                item.innerHTML = `
                    <span class="slash-menu-icon">${BLOCK_ICONS[bt.icon] || ''}</span>
                    <span class="slash-menu-text">
                        <span class="slash-menu-name">${bt.name}</span>
                        <span class="slash-menu-desc">${bt.desc}</span>
                    </span>
                    <span class="slash-menu-shortcut">${bt.shortcut}</span>
                `;
                
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectSlashMenuItemByType(bt.type, blockIndex);
                });
                
                item.addEventListener('mouseenter', () => {
                    menu.querySelectorAll('.slash-menu-item.active').forEach(el => el.classList.remove('active'));
                    item.classList.add('active');
                    state.slashMenuIndex = i;
                });
                
                menu.appendChild(item);
            });
        }
        
        // Position relative to the block
        const rect = el.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.left = rect.left + 'px';
        menu.style.top = (rect.bottom + 4) + 'px';
        
        // Keep within viewport
        document.body.appendChild(menu);
        
        const menuRect = menu.getBoundingClientRect();
        if (menuRect.bottom > window.innerHeight) {
            menu.style.top = (rect.top - menuRect.height - 4) + 'px';
        }
        if (menuRect.right > window.innerWidth) {
            menu.style.left = (window.innerWidth - menuRect.width - 16) + 'px';
        }
    }

    function closeSlashMenu() {
        state.slashMenuOpen = false;
        const existing = document.getElementById('slashCommandMenu');
        if (existing) existing.remove();
    }

    function navigateSlashMenu(direction) {
        const menu = document.getElementById('slashCommandMenu');
        if (!menu) return;
        
        const items = menu.querySelectorAll('.slash-menu-item');
        if (items.length === 0) return;
        
        items[state.slashMenuIndex]?.classList.remove('active');
        state.slashMenuIndex = (state.slashMenuIndex + direction + items.length) % items.length;
        items[state.slashMenuIndex]?.classList.add('active');
        
        // Scroll into view
        items[state.slashMenuIndex]?.scrollIntoView({ block: 'nearest' });
    }

    function selectSlashMenuItem() {
        const menu = document.getElementById('slashCommandMenu');
        if (!menu) return;
        
        const activeItem = menu.querySelector('.slash-menu-item.active');
        if (activeItem) {
            const type = activeItem.dataset.type;
            selectSlashMenuItemByType(type, state.focusedIndex);
        }
    }

    function selectSlashMenuItemByType(type, blockIndex) {
        closeSlashMenu();
        
        saveUndoState();
        // Clear the slash command text
        state.blocks[blockIndex].content = '';
        state.blocks[blockIndex].type = type;
        
        if (type === 'list-ordered') {
            state.blocks[blockIndex].index = 1;
        }
        
        renderBlocks();
        updateHiddenInput();
        
        // Focus the converted block
        setTimeout(() => focusBlock(blockIndex, 'start'), 50);
    }

    // ═══════════════════════════════════════════════════════
    // STATIC TOOLBAR
    // ═══════════════════════════════════════════════════════
    function setupToolbar() {
        const toolbar = document.getElementById('editorToolbar');
        if (!toolbar) return;
        
        toolbar.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const action = btn.dataset.action;
                
                switch(action) {
                    case 'bold':
                        document.execCommand('bold', false, null);
                        break;
                    case 'italic':
                        document.execCommand('italic', false, null);
                        break;
                    case 'underline':
                        document.execCommand('underline', false, null);
                        break;
                    case 'strikethrough':
                        document.execCommand('strikeThrough', false, null);
                        break;
                    case 'link':
                        const url = prompt('Enter URL:');
                        if (url) document.execCommand('createLink', false, url);
                        break;
                    case 'heading-2':
                    case 'heading-3':
                    case 'list-bullet':
                    case 'list-ordered':
                    case 'quote':
                    case 'code':
                    case 'image':
                    case 'divider':
                    case 'callout':
                    case 'table':
                        if (state.focusedIndex !== null) {
                            insertNewBlockAfter(state.focusedIndex, action);
                        } else {
                            insertNewBlockAfter(state.blocks.length - 1, action);
                        }
                        break;
                    case 'clear-format':
                        document.execCommand('removeFormat', false, null);
                        break;
                }
                
                // Sync block content after format command
                if (['bold', 'italic', 'underline', 'strikethrough', 'link', 'clear-format'].includes(action)) {
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
    }

    // ═══════════════════════════════════════════════════════
    // IMAGE BLOCK HTML BUILDER
    // ═══════════════════════════════════════════════════════
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
                e.stopPropagation();
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
            const position = block.position || 'center';
            displayBox.innerHTML = `
                <img src="../${block.url}" alt="" class="blog-img-${position}">
                <div class="block-image-details" style="display:flex; gap:10px; margin-top:8px;">
                    <input type="text" class="img-caption" placeholder="Write image caption..." value="${block.caption || ''}" style="flex-grow:1;">
                    <select class="img-position" style="padding:6px; border:1px solid var(--border-color); border-radius:6px; background:var(--bg-app); color:var(--text-main); font-size:12px;">
                        <option value="center" ${position === 'center' ? 'selected' : ''}>Center</option>
                        <option value="left" ${position === 'left' ? 'selected' : ''}>Left</option>
                        <option value="right" ${position === 'right' ? 'selected' : ''}>Right</option>
                        <option value="end" ${position === 'end' ? 'selected' : ''}>End of Blog</option>
                    </select>
                </div>
            `;
            
            const captionInput = displayBox.querySelector('.img-caption');
            captionInput.addEventListener('input', (e) => {
                block.caption = e.target.value;
                updateHiddenInput();
            });

            const positionSelect = displayBox.querySelector('.img-position');
            positionSelect.addEventListener('change', (e) => {
                block.position = e.target.value;
                const img = displayBox.querySelector('img');
                img.className = '';
                img.classList.add(`blog-img-${e.target.value}`);
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
                <div class="upload-spinner"></div>
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

    // ═══════════════════════════════════════════════════════
    // YOUTUBE BLOCK HTML BUILDER
    // ═══════════════════════════════════════════════════════
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

    // ═══════════════════════════════════════════════════════
    // TABLE BLOCK HTML BUILDER
    // ═══════════════════════════════════════════════════════
    function createTableBlockElement(block, index) {
        const wrap = document.createElement('div');
        wrap.className = 'editor-block-content block-table-wrapper';
        
        // Parse markdown table to HTML for editing
        const content = block.content || '| Header 1 | Header 2 | Header 3 |\n| --- | --- | --- |\n| Cell 1 | Cell 2 | Cell 3 |';
        block.content = content;
        
        const textarea = document.createElement('textarea');
        textarea.className = 'block-table-editor';
        textarea.value = content;
        textarea.placeholder = '| Header 1 | Header 2 |\n| --- | --- |\n| Cell 1 | Cell 2 |';
        textarea.rows = Math.max(3, content.split('\n').length + 1);
        
        textarea.addEventListener('input', () => {
            block.content = textarea.value;
            textarea.rows = Math.max(3, textarea.value.split('\n').length + 1);
            updateHiddenInput();
            updateStats();
        });

        textarea.addEventListener('focus', () => {
            state.focusedIndex = index;
        });
        
        wrap.appendChild(textarea);
        return wrap;
    }

    function getYoutubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    // ═══════════════════════════════════════════════════════
    // BOTTOM BLOCK ADDER (+)
    // ═══════════════════════════════════════════════════════
    function createBottomAdder() {
        const wrap = document.createElement('div');
        wrap.className = 'block-adder-wrap';
        
        let menuItems = '';
        BLOCK_TYPES.forEach(bt => {
            menuItems += `<div class="block-selector-item" data-type="${bt.type}">${BLOCK_ICONS[bt.icon] || ''} ${bt.name}</div>`;
        });
        
        wrap.innerHTML = `
            <button type="button" class="block-adder-btn" title="Add Block">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            </button>
            <div class="block-selector-menu">${menuItems}</div>
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

    // ═══════════════════════════════════════════════════════
    // INLINE BLOCK MENU (for + button on each block)
    // ═══════════════════════════════════════════════════════
    function toggleInlineBlockMenu(e, index, triggerBtn, position) {
        e.stopPropagation();
        
        const prior = document.querySelector('.inline-block-menu');
        if (prior) {
            prior.remove();
            if (parseInt(prior.dataset.index) === index) return;
        }

        const menu = document.createElement('div');
        menu.className = 'inline-block-menu block-selector-menu';
        menu.dataset.index = index;
        menu.style.display = 'grid';
        menu.style.position = 'absolute';
        menu.style.top = '100%';
        menu.style.left = '0';
        menu.style.zIndex = '600';
        
        BLOCK_TYPES.forEach(bt => {
            const item = document.createElement('div');
            item.className = 'block-selector-item';
            item.innerHTML = `${BLOCK_ICONS[bt.icon] || ''} ${bt.name}`;
            item.addEventListener('click', (ev) => {
                ev.stopPropagation();
                const insertIdx = position === 'before' ? index - 1 : index;
                insertNewBlockAfter(insertIdx, bt.type);
                menu.remove();
            });
            menu.appendChild(item);
        });
        
        triggerBtn.parentNode.style.position = 'relative';
        triggerBtn.parentNode.appendChild(menu);
        
        window.addEventListener('click', () => menu.remove(), { once: true });
    }

    // ═══════════════════════════════════════════════════════
    // BLOCK CONVERTER POPUP MENU
    // ═══════════════════════════════════════════════════════
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
            if (state.blocks[index].type === t.type) {
                btn.style.color = 'var(--gold)';
                btn.style.fontWeight = '600';
            }
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

    // ═══════════════════════════════════════════════════════
    // INTERNAL STATE MANIPULATORS (CRUD)
    // ═══════════════════════════════════════════════════════
    function insertNewBlockAfter(index, type) {
        saveUndoState();
        const newBlock = { type: type, content: '' };
        if (type === 'list-ordered') {
            newBlock.index = (index >= 0 && state.blocks[index]?.type === 'list-ordered') ? (parseInt(state.blocks[index].index) || 0) + 1 : 1;
        }
        if (type === 'table') {
            newBlock.content = '| Header 1 | Header 2 | Header 3 |\n| --- | --- | --- |\n| Cell 1 | Cell 2 | Cell 3 |';
        }
        
        state.blocks.splice(index + 1, 0, newBlock);
        renderBlocks();
        updateHiddenInput();
        
        // Shift focus to new block content
        setTimeout(() => focusBlock(index + 1, 'start'), 50);
    }

    function convertBlockType(index, nextType) {
        saveUndoState();
        state.blocks[index].type = nextType;
        if (nextType === 'list-ordered') {
            recalcListIndices(state.blocks);
        }
        renderBlocks();
        updateHiddenInput();
        
        // Focus converted item
        setTimeout(() => focusBlock(index, 'start'), 50);
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
        setTimeout(() => focusBlock(nextIndex, 'start'), 50);
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
            setTimeout(() => focusBlock(prevIndex, 'end'), 50);
        }
    }

    // ═══════════════════════════════════════════════════════
    // FOCUS & CARET HELPERS
    // ═══════════════════════════════════════════════════════
    function focusBlock(index, position = 'end') {
        if (index < 0 || index >= state.blocks.length) return;
        
        const targetEl = container.querySelector(`[data-index="${index}"] .editor-block-content`);
        if (targetEl && targetEl.contentEditable === 'true') {
            targetEl.focus();
            if (position === 'end') {
                placeCaretAtEnd(targetEl);
            } else {
                placeCaretAtStart(targetEl);
            }
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

    function placeCaretAtStart(el) {
        el.focus();
        if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
            const range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(true);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    function isCaretAtStart(el) {
        const sel = window.getSelection();
        if (!sel.rangeCount) return false;
        const range = sel.getRangeAt(0);
        return range.startOffset === 0 && range.startContainer === el || range.startContainer === el.firstChild && range.startOffset === 0;
    }

    function isCaretAtEnd(el) {
        const sel = window.getSelection();
        if (!sel.rangeCount) return false;
        const range = sel.getRangeAt(0);
        const lastChild = el.lastChild || el;
        if (range.endContainer === el) return range.endOffset === el.childNodes.length;
        if (range.endContainer.nodeType === 3) return range.endOffset === range.endContainer.textContent.length && range.endContainer === lastChild;
        return false;
    }

    // ═══════════════════════════════════════════════════════
    // STATS GENERATOR
    // ═══════════════════════════════════════════════════════
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

    // ═══════════════════════════════════════════════════════
    // UNDO/REDO LOGIC STACKS
    // ═══════════════════════════════════════════════════════
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
        // Save shortcut
        else if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const form = document.getElementById('editorForm');
            if (form) form.submit();
        }
    });

    // ═══════════════════════════════════════════════════════
    // TEXT SELECTION FORMATTING POPUP TOOLBAR
    // ═══════════════════════════════════════════════════════
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
            if (linkInputWrap) linkInputWrap.style.display = 'none'; // Reset link panel
            
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

    // ═══════════════════════════════════════════════════════
    // AUTOSAVE BACKUP LOGIC
    // ═══════════════════════════════════════════════════════
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
                    banner.style.justifyContent = 'space-between';
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
