/* admin/editor-serializer.js — Markdown ↔ Block Serialization & Paste Parsing */

window.EditorSerializer = (function () {
    'use strict';

    var E = window.EditorEngine;

    // ═══════════════════════════════════════════════════════
    // MARKDOWN DESERIALIZATION (Markdown -> JSON Blocks)
    // ═══════════════════════════════════════════════════════
    function markdownToBlocks(markdown) {
        if (!markdown) return [];

        // Clean line endings
        markdown = markdown.replace(/\r\n/g, '\n').replace(/\r/g, '\n');

        var blocks = [];
        var lines = markdown.split('\n');
        var inCodeBlock = false;
        var codeContent = '';
        var codeLang = '';

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];

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

            var trimmed = line.trim();
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
                var quoteText = trimmed.substring(2);
                while (i + 1 < lines.length && lines[i + 1].trim().startsWith('> ')) {
                    i++;
                    quoteText += ' ' + lines[i].trim().substring(2);
                }
                if (quoteText.startsWith('[!NOTE]') || quoteText.startsWith('[!TIP]') || quoteText.startsWith('[!WARNING]')) {
                    var clean = quoteText.replace(/\[!(NOTE|TIP|WARNING)\]/i, '').trim();
                    blocks.push({ type: 'callout', content: mdInlineToHtml(clean) });
                } else {
                    blocks.push({ type: 'quote', content: mdInlineToHtml(quoteText) });
                }
            }
            // YouTube embeds
            else if (trimmed.startsWith('{{youtube:') && trimmed.endsWith('}}')) {
                var ytId = trimmed.replace('{{youtube:', '').replace('}}', '').trim();
                blocks.push({ type: 'youtube', url: 'https://www.youtube.com/watch?v=' + ytId });
            }
            else if (trimmed.startsWith('[youtube](') && trimmed.endsWith(')')) {
                var ytUrl = trimmed.match(/\[youtube\]\((.*?)\)/)[1];
                blocks.push({ type: 'youtube', url: ytUrl });
            }
            else if (trimmed.includes('youtube.com/embed/')) {
                var srcMatch = trimmed.match(/src="(.*?)"/);
                var embedUrl = srcMatch ? srcMatch[1] : trimmed;
                blocks.push({ type: 'youtube', url: embedUrl });
            }
            // Table blocks
            else if (trimmed.startsWith('|')) {
                var tableContent = trimmed;
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
            else if (/^\d+\.\s*/.test(trimmed)) {
                var numMatch = trimmed.match(/^(\d+)\.\s*(.*)$/);
                blocks.push({ type: 'list-ordered', content: mdInlineToHtml(numMatch[2] || ''), index: parseInt(numMatch[1]) || 1 });
            }
            // Images: ![alt](src){position}
            else if (trimmed.startsWith('![') && trimmed.includes('](')) {
                var altMatch = trimmed.match(/!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|end)\})?/);
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
                var pText = trimmed;
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

    // Inline Markdown Parser to basic HTML tags
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
    // ═══════════════════════════════════════════════════════
    function blocksToMarkdown(blocksList) {
        blocksList = blocksList || E.state.blocks;
        var result = '';

        // Recalculate ordered list indices
        recalcListIndices(blocksList);

        blocksList.forEach(function (block, idx) {
            var line = '';

            switch (block.type) {
                case 'paragraph':
                    if (block.content.trim() !== '') {
                        if (block.align) {
                            line = '<p style="text-align: ' + block.align + ';">' + block.content + '</p>';
                        } else {
                            line = htmlToMdInline(block.content);
                        }
                    }
                    break;
                case 'heading-2':
                    if (block.align) {
                        line = '<h2 style="text-align: ' + block.align + ';">' + block.content + '</h2>';
                    } else {
                        line = '## ' + htmlToMdInline(block.content);
                    }
                    break;
                case 'heading-3':
                    if (block.align) {
                        line = '<h3 style="text-align: ' + block.align + ';">' + block.content + '</h3>';
                    } else {
                        line = '### ' + htmlToMdInline(block.content);
                    }
                    break;
                case 'heading-4':
                    if (block.align) {
                        line = '<h4 style="text-align: ' + block.align + ';">' + block.content + '</h4>';
                    } else {
                        line = '#### ' + htmlToMdInline(block.content);
                    }
                    break;
                case 'quote':
                    if (block.align) {
                        line = '<blockquote style="text-align: ' + block.align + ';">' + block.content + '</blockquote>';
                    } else {
                        line = '> ' + htmlToMdInline(block.content);
                    }
                    break;
                case 'callout':
                    line = '> [!NOTE]\n> ' + htmlToMdInline(block.content);
                    break;
                case 'divider':
                    line = '---';
                    break;
                case 'list-bullet':
                    if (block.align) {
                        line = '<li style="text-align: ' + block.align + '; list-style-type: disc;">' + block.content + '</li>';
                    } else {
                        line = '- ' + htmlToMdInline(block.content);
                    }
                    break;
                case 'list-ordered':
                    if (block.align) {
                        line = '<li style="text-align: ' + block.align + '; list-style-type: decimal;" value="' + (block.index || 1) + '">' + block.content + '</li>';
                    } else {
                        line = (block.index || 1) + '. ' + htmlToMdInline(block.content);
                    }
                    break;
                case 'code':
                    line = '```' + (block.lang || '') + '\n' + block.content + '\n```';
                    break;
                case 'image':
                    if (block.url) {
                        var pos = block.position ? '{' + block.position + '}' : '';
                        line = '![' + htmlToMdInline(block.caption || '') + '](' + block.url + ')' + pos;
                    }
                    break;
                case 'youtube':
                    if (block.url) {
                        var ytVideoId = getYoutubeId(block.url);
                        if (ytVideoId) {
                            line = '{{youtube:' + ytVideoId + '}}';
                        } else {
                            line = '[youtube](' + block.url + ')';
                        }
                    }
                    break;
                case 'table':
                    line = block.content || '';
                    break;
            }

            if (line === '') return;

            // Join consecutive list items with single newline
            if (idx > 0 && result !== '') {
                var prevBlock = blocksList[idx - 1];
                var isContinuousList =
                    (block.type === 'list-bullet' && prevBlock.type === 'list-bullet') ||
                    (block.type === 'list-ordered' && prevBlock.type === 'list-ordered');

                if (isContinuousList) {
                    result += '\n' + line;   // Single newline — same list
                } else {
                    result += '\n\n' + line; // Double newline — new block
                }
            } else {
                result += line;
            }
        });

        return result;
    }

    // Expose globally for blog-editor.php preview function
    window.blocksToMarkdown = function (blocksList) {
        return blocksToMarkdown(blocksList || E.state.blocks);
    };

    // Recalculate ordered list indices for consecutive list-ordered blocks
    function recalcListIndices(blocksList) {
        var counter = 0;
        for (var i = 0; i < blocksList.length; i++) {
            if (blocksList[i].type === 'list-ordered') {
                if (i === 0 || blocksList[i - 1].type !== 'list-ordered') {
                    counter = 1;
                } else {
                    counter++;
                }
                blocksList[i].index = counter;
            }
        }
    }

    function htmlToMdInline(html) {
        if (!html) return '';
        var div = document.createElement('div');
        div.innerHTML = html;

        // Convert strong tags to **
        var strongs = div.querySelectorAll('strong, b');
        strongs.forEach(function (s) { s.outerHTML = '**' + s.innerHTML + '**'; });

        // Convert em tags to *
        var ems = div.querySelectorAll('em, i');
        ems.forEach(function (e) { e.outerHTML = '*' + e.innerHTML + '*'; });

        // Convert links to [text](href)
        var links = div.querySelectorAll('a');
        links.forEach(function (l) { l.outerHTML = '[' + l.innerHTML + '](' + l.getAttribute('href') + ')'; });

        // Convert linebreaks
        var brs = div.querySelectorAll('br');
        brs.forEach(function (b) { b.outerHTML = '\n'; });

        // Preserve inline formatting that has no markdown equivalent
        var result = div.innerHTML;

        // Remove block-level wrappers
        result = result.replace(/<\/?(?:div|p|section|article)[^>]*>/gi, '');

        return result.trim();
    }

    // ═══════════════════════════════════════════════════════
    // PASTE HTML PARSER
    // ═══════════════════════════════════════════════════════
    function htmlToBlocks(html) {
        var tempContainer = document.createElement('div');
        tempContainer.innerHTML = html;

        // Remove Word/Google Docs cruft
        tempContainer.querySelectorAll('style, script, meta, link, o\\:p, xml').forEach(function (el) { el.remove(); });

        var blockTags = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'ul', 'ol', 'li', 'pre', 'hr', 'table', 'tr', 'div', 'section', 'article', 'main'];
        var blocks = [];

        function hasBlockDescendant(element) {
            return Array.from(element.getElementsByTagName('*')).some(function (el) {
                return blockTags.includes(el.tagName.toLowerCase());
            });
        }

        function walk(node, parentListType, listIndex) {
            parentListType = parentListType || null;
            listIndex = listIndex || 1;

            if (node.nodeType === 3) { // Text node
                var txt = node.textContent.trim();
                if (txt) {
                    blocks.push({ type: 'paragraph', content: escapeHtml(txt) });
                }
                return;
            }

            if (node.nodeType !== 1) return;

            var tag = node.tagName.toLowerCase();

            // Table
            if (tag === 'table') {
                var tableBlk = htmlTableToMarkdown(node);
                if (tableBlk) blocks.push({ type: 'table', content: tableBlk });
                return;
            }

            // HR
            if (tag === 'hr') {
                blocks.push({ type: 'divider' });
                return;
            }

            // List container
            if (tag === 'ul' || tag === 'ol') {
                var listType = tag === 'ul' ? 'list-bullet' : 'list-ordered';
                var idx = 1;
                Array.from(node.childNodes).forEach(function (child) {
                    if (child.nodeType === 1 && child.tagName.toLowerCase() === 'li') {
                        walk(child, listType, idx);
                        idx++;
                    } else {
                        walk(child);
                    }
                });
                return;
            }

            // Block tag or leaf
            if (blockTags.includes(tag) || !hasBlockDescendant(node)) {
                var content = cleanInlineHtml(node.innerHTML).trim();
                if (!content && tag !== 'hr') return;

                var type = 'paragraph';
                var indexAttr = null;
                var alignAttr = null;

                if (node.style && node.style.textAlign) {
                    alignAttr = node.style.textAlign;
                }

                if (parentListType === 'list-bullet' && tag === 'li') {
                    type = 'list-bullet';
                } else if (parentListType === 'list-ordered' && tag === 'li') {
                    type = 'list-ordered';
                    indexAttr = listIndex;
                } else {
                    switch (tag) {
                        case 'h1':
                        case 'h2':
                            type = 'heading-2';
                            break;
                        case 'h3':
                            type = 'heading-3';
                            break;
                        case 'h4':
                        case 'h5':
                        case 'h6':
                            type = 'heading-4';
                            break;
                        case 'blockquote':
                            type = 'quote';
                            break;
                        case 'pre':
                            type = 'code';
                            break;
                    }
                }

                var newBlock = { type: type, content: content };
                if (indexAttr !== null) newBlock.index = indexAttr;
                if (alignAttr !== null) newBlock.align = alignAttr;
                blocks.push(newBlock);
            } else {
                // Container with block descendants
                Array.from(node.childNodes).forEach(function (child) { walk(child); });
            }
        }

        Array.from(tempContainer.childNodes).forEach(function (child) { walk(child); });
        return blocks;
    }

    function htmlTableToMarkdown(tableEl) {
        var rows = tableEl.querySelectorAll('tr');
        if (rows.length === 0) return null;

        var md = '';
        rows.forEach(function (row, idx) {
            var cells = row.querySelectorAll('th, td');
            var cellValues = Array.from(cells).map(function (c) { return c.textContent.trim(); });
            md += '| ' + cellValues.join(' | ') + ' |\n';

            if (idx === 0) {
                md += '| ' + cellValues.map(function () { return '---'; }).join(' | ') + ' |\n';
            }
        });

        return md.trim();
    }

    // Clean pasted HTML to only keep inline formatting
    function cleanInlineHtml(html) {
        if (!html) return '';
        var div = document.createElement('div');
        div.innerHTML = html;

        // Remove dangerous elements
        div.querySelectorAll('style, script, meta, link, svg, iframe, object, embed').forEach(function (el) { el.remove(); });

        var allowedTags = ['strong', 'b', 'em', 'i', 'a', 'u', 's', 'code', 'sub', 'sup', 'span', 'font', 'mark', 'br'];

        function sanitize(node) {
            var children = Array.from(node.childNodes);
            children.forEach(function (child) { sanitize(child); });

            if (node.nodeType === 1) {
                var tag = node.tagName.toLowerCase();

                if (!allowedTags.includes(tag)) {
                    node.outerHTML = node.innerHTML;
                    return;
                }

                // Filter attributes
                var attrs = Array.from(node.attributes);
                attrs.forEach(function (attr) {
                    var name = attr.name.toLowerCase();
                    if (tag === 'a' && (name === 'href' || name === 'target')) return;
                    if (tag === 'font' && (name === 'color' || name === 'size')) return;
                    if (name === 'style') {
                        var styleVal = attr.value;
                        var cleanStyles = [];
                        var styles = styleVal.split(';');
                        styles.forEach(function (s) {
                            var parts = s.split(':');
                            if (parts.length === 2) {
                                var prop = parts[0].trim().toLowerCase();
                                var val = parts[1].trim();
                                if (['color', 'background-color', 'font-size', 'text-align'].includes(prop)) {
                                    cleanStyles.push(prop + ': ' + val);
                                }
                            }
                        });
                        if (cleanStyles.length > 0) {
                            node.setAttribute('style', cleanStyles.join('; ') + ';');
                            return;
                        }
                    }
                    node.removeAttribute(attr.name);
                });

                // Unwrap spans/fonts with no attributes
                if ((tag === 'span' || tag === 'font') && node.attributes.length === 0) {
                    node.outerHTML = node.innerHTML;
                }
            }
        }

        Array.from(div.childNodes).forEach(function (child) { sanitize(child); });
        return div.innerHTML;
    }

    function getYoutubeId(url) {
        var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        var match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Public API
    return {
        markdownToBlocks: markdownToBlocks,
        blocksToMarkdown: blocksToMarkdown,
        recalcListIndices: recalcListIndices,
        mdInlineToHtml: mdInlineToHtml,
        htmlToMdInline: htmlToMdInline,
        htmlToBlocks: htmlToBlocks,
        cleanInlineHtml: cleanInlineHtml,
        htmlTableToMarkdown: htmlTableToMarkdown,
        getYoutubeId: getYoutubeId,
        escapeHtml: escapeHtml
    };
})();
