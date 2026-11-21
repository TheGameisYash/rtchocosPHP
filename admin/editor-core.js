/* admin/editor-core.js — Shared State, Constants, CRUD, Focus, Undo/Redo */

// ═══════════════════════════════════════════════════════
// GLOBAL SHARED STATE (all modules read/write this)
// ═══════════════════════════════════════════════════════
window.EditorEngine = (function () {
    'use strict';

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

    // DOM references (populated on init)
    let container = null;
    let hiddenContentInput = null;
    let charCountSpan = null;
    let wordCountSpan = null;
    let formatBar = null;
    let linkInputWrap = null;
    let linkInput = null;

    // ═══════════════════════════════════════════════════════
    // INITIALIZATION
    // ═══════════════════════════════════════════════════════
    function initBlockEditor(initialMarkdown) {
        // Grab DOM refs
        container = document.getElementById('editorBlocksContainer');
        hiddenContentInput = document.getElementById('content');
        charCountSpan = document.getElementById('char-count');
        wordCountSpan = document.getElementById('word-count');
        formatBar = document.getElementById('floatingFormatBar');
        linkInputWrap = formatBar ? formatBar.querySelector('.format-bar-link-input') : null;
        linkInput = formatBar ? formatBar.querySelector('.format-bar-link-input input') : null;

        state.blocks = EditorSerializer.markdownToBlocks(initialMarkdown);
        if (state.blocks.length === 0) {
            state.blocks.push({ type: 'paragraph', content: '' });
        }
        renderBlocks();
        updateHiddenInput();

        // Setup features from other modules
        EditorFeatures.setupAutosaveListener();
        EditorFeatures.checkAutosaveRecovery();
        EditorToolbar.setupToolbar();
        EditorFeatures.setupGlobalListeners();
    }

    // ═══════════════════════════════════════════════════════
    // RENDER BLOCKS TO EDITOR DOM
    // ═══════════════════════════════════════════════════════
    function renderBlocks() {
        if (!container) return;
        container.innerHTML = '';

        // Recalculate list indices before rendering
        EditorSerializer.recalcListIndices(state.blocks);

        state.blocks.forEach(function (block, index) {
            var blockWrapper = document.createElement('div');
            blockWrapper.className = 'editor-block-wrapper';
            blockWrapper.dataset.index = index;

            // Drag handle
            blockWrapper.draggable = true;
            blockWrapper.addEventListener('dragstart', function (e) { EditorFeatures.handleDragStart(e, index); });
            blockWrapper.addEventListener('dragover', function (e) { EditorFeatures.handleDragOver(e, index); });
            blockWrapper.addEventListener('drop', function (e) { EditorFeatures.handleDrop(e, index); });
            blockWrapper.addEventListener('dragend', EditorFeatures.handleDragEnd);
            blockWrapper.addEventListener('dragenter', function (e) { EditorFeatures.handleDragEnter(e, index); });
            blockWrapper.addEventListener('dragleave', EditorFeatures.handleDragLeave);

            // Side Controls
            var controls = EditorBlocks.createBlockControls(index, block.type);
            blockWrapper.appendChild(controls);

            // Block Content
            var content = EditorBlocks.createBlockContentElement(block, index);
            blockWrapper.appendChild(content);

            container.appendChild(blockWrapper);
        });

        // Bottom adder
        var adder = EditorBlocks.createBottomAdder();
        container.appendChild(adder);

        updateStats();
    }

    // ═══════════════════════════════════════════════════════
    // BLOCK CRUD OPERATIONS
    // ═══════════════════════════════════════════════════════
    function insertNewBlockAfter(index, type) {
        saveUndoState();
        var newBlock = { type: type, content: '' };
        if (type === 'list-ordered') {
            newBlock.index = (index >= 0 && state.blocks[index] && state.blocks[index].type === 'list-ordered')
                ? (parseInt(state.blocks[index].index) || 0) + 1 : 1;
        }
        if (type === 'table') {
            newBlock.content = '| Header 1 | Header 2 | Header 3 |\n| --- | --- | --- |\n| Cell 1 | Cell 2 | Cell 3 |';
        }

        state.blocks.splice(index + 1, 0, newBlock);
        EditorSerializer.recalcListIndices(state.blocks);

        renderBlocks();
        updateHiddenInput();

        // Focus new block
        setTimeout(function () { focusBlock(index + 1, 'start'); }, 50);
    }

    function convertBlockType(index, nextType) {
        saveUndoState();
        state.blocks[index].type = nextType;
        if (nextType === 'list-ordered') {
            // Ensure index is set
            EditorSerializer.recalcListIndices(state.blocks);
        } else {
            EditorSerializer.recalcListIndices(state.blocks);
        }
        renderBlocks();
        updateHiddenInput();

        setTimeout(function () { focusBlock(index, 'start'); }, 50);
    }

    function moveBlock(index, offset) {
        var nextIndex = index + offset;
        if (nextIndex < 0 || nextIndex >= state.blocks.length) return;

        saveUndoState();
        var temp = state.blocks[index];
        state.blocks[index] = state.blocks[nextIndex];
        state.blocks[nextIndex] = temp;

        renderBlocks();
        updateHiddenInput();

        setTimeout(function () { focusBlock(nextIndex, 'start'); }, 50);
    }

    function deleteBlock(index, focusPrevious) {
        if (state.blocks.length <= 1) {
            saveUndoState();
            state.blocks[0] = { type: 'paragraph', content: '' };
            renderBlocks();
            updateHiddenInput();
            return;
        }

        saveUndoState();
        state.blocks.splice(index, 1);
        EditorSerializer.recalcListIndices(state.blocks);
        renderBlocks();
        updateHiddenInput();

        if (focusPrevious) {
            var prevIndex = Math.max(0, index - 1);
            setTimeout(function () { focusBlock(prevIndex, 'end'); }, 50);
        }
    }

    // ═══════════════════════════════════════════════════════
    // FOCUS & CARET HELPERS
    // ═══════════════════════════════════════════════════════
    function focusBlock(index, position) {
        if (index < 0 || index >= state.blocks.length) return;
        position = position || 'end';

        var targetEl = container.querySelector('[data-index="' + index + '"] .editor-block-content');
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
        if (typeof window.getSelection !== 'undefined' && typeof document.createRange !== 'undefined') {
            var range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    function placeCaretAtStart(el) {
        el.focus();
        if (typeof window.getSelection !== 'undefined' && typeof document.createRange !== 'undefined') {
            var range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(true);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    }

    function isCaretAtStart(el) {
        var sel = window.getSelection();
        if (!sel.rangeCount) return false;
        var range = sel.getRangeAt(0);
        return (range.startOffset === 0 && range.startContainer === el) ||
               (range.startContainer === el.firstChild && range.startOffset === 0);
    }

    function isCaretAtEnd(el) {
        var sel = window.getSelection();
        if (!sel.rangeCount) return false;
        var range = sel.getRangeAt(0);
        var lastChild = el.lastChild || el;
        if (range.endContainer === el) return range.endOffset === el.childNodes.length;
        if (range.endContainer.nodeType === 3) return range.endOffset === range.endContainer.textContent.length && range.endContainer === lastChild;
        return false;
    }

    // ═══════════════════════════════════════════════════════
    // STATS GENERATOR
    // ═══════════════════════════════════════════════════════
    function updateStats() {
        var markdown = EditorSerializer.blocksToMarkdown(state.blocks);
        var charCount = markdown.length;
        var wordCount = markdown.trim() === '' ? 0 : markdown.trim().split(/\s+/).length;

        if (charCountSpan) charCountSpan.innerText = charCount + ' character' + (charCount !== 1 ? 's' : '');
        if (wordCountSpan) wordCountSpan.innerText = wordCount + ' word' + (wordCount !== 1 ? 's' : '');

        // Update read time estimate
        var readTimeInput = document.getElementById('read_time');
        if (readTimeInput) {
            var mins = Math.max(1, Math.ceil(wordCount / 200));
            readTimeInput.placeholder = mins + ' min';
        }

        // Live compile HTML preview
        var livePreviewEl = document.getElementById('preview');
        if (livePreviewEl && typeof parseMarkdown === 'function') {
            livePreviewEl.innerHTML = parseMarkdown(markdown);
        }
    }

    function updateHiddenInput() {
        if (hiddenContentInput) {
            hiddenContentInput.value = EditorSerializer.blocksToMarkdown(state.blocks);
        }
    }

    // ═══════════════════════════════════════════════════════
    // UNDO/REDO LOGIC
    // ═══════════════════════════════════════════════════════
    function saveUndoState() {
        var snap = JSON.stringify(state.blocks);
        if (state.undoStack.length === 0 || state.undoStack[state.undoStack.length - 1] !== snap) {
            state.undoStack.push(snap);
            if (state.undoStack.length > 30) state.undoStack.shift();
            state.redoStack = [];
        }
    }

    function triggerUndo() {
        if (state.undoStack.length > 0) {
            var snap = state.undoStack.pop();
            state.redoStack.push(JSON.stringify(state.blocks));
            state.blocks = JSON.parse(snap);
            renderBlocks();
            updateHiddenInput();
        } else {
            if (typeof showToast === 'function') showToast('Nothing to undo', 'info');
        }
    }

    function triggerRedo() {
        if (state.redoStack.length > 0) {
            var snap = state.redoStack.pop();
            state.undoStack.push(JSON.stringify(state.blocks));
            state.blocks = JSON.parse(snap);
            renderBlocks();
            updateHiddenInput();
        } else {
            if (typeof showToast === 'function') showToast('Nothing to redo', 'info');
        }
    }

    // Expose global functions for HTML onclick handlers
    window.initBlockEditor = initBlockEditor;
    window.triggerUndo = triggerUndo;
    window.triggerRedo = triggerRedo;

    // Public API
    return {
        state: state,
        BLOCK_TYPES: BLOCK_TYPES,
        BLOCK_ICONS: BLOCK_ICONS,

        // DOM getters (late-bound)
        getContainer: function () { return container; },
        getFormatBar: function () { return formatBar; },
        getLinkInputWrap: function () { return linkInputWrap; },
        getLinkInput: function () { return linkInput; },

        // Core ops
        initBlockEditor: initBlockEditor,
        renderBlocks: renderBlocks,
        updateHiddenInput: updateHiddenInput,
        updateStats: updateStats,
        insertNewBlockAfter: insertNewBlockAfter,
        convertBlockType: convertBlockType,
        moveBlock: moveBlock,
        deleteBlock: deleteBlock,
        focusBlock: focusBlock,
        placeCaretAtEnd: placeCaretAtEnd,
        placeCaretAtStart: placeCaretAtStart,
        isCaretAtStart: isCaretAtStart,
        isCaretAtEnd: isCaretAtEnd,
        saveUndoState: saveUndoState,
        triggerUndo: triggerUndo,
        triggerRedo: triggerRedo
    };
})();
