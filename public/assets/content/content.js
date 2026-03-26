/**
 * content.js — Editor.js initialization + form submit intercept
 *
 * Global names exposed by each UMD bundle:
 *   editor.js      → EditorJS
 *   header.js      → Header
 *   quote.js       → Quote
 *   image.js       → ImageTool  (via @editorjs/image — global: ImageTool? check below)
 *   nested-list.js → NestedList
 *   link.js        → LinkTool
 *   embed.js       → Embed
 *   table.js       → Table
 *   delimiter.js   → Delimiter
 *   warning.js     → Warning
 *   code.js        → CodeTool
 *   raw.js         → RawTool
 *   attaches.js    → AttachesTool
 *   inline-code.js → InlineCode
 */
(function () {
    const editorEl = document.getElementById('editorjs');
    if (!editorEl) return;

    const hiddenInput = document.getElementById(editorEl.dataset.editor);
    const uploadUrl     = editorEl.dataset.uploadUrl;
    const uploadFileUrl = editorEl.dataset.uploadFileUrl;
    const fetchUrl      = editorEl.dataset.fetchUrl;
    const csrfToken   = editorEl.dataset.csrf;
    const csrfValue   = editorEl.dataset.csrfValue;

    function csrfHeaders() {
        return { [csrfToken]: csrfValue };
    }

    let initialData = {};
    try {
        const raw = hiddenInput.value;
        if (raw) initialData = JSON.parse(raw);
    } catch (e) {}

    const editor = new EditorJS({
        holder: 'editorjs',
        data: initialData,
        tools: {
            header: {
                class: Header,
                config: { levels: [2, 3, 4], defaultLevel: 2 },
            },
            quote: Quote,
            list: {
                class: NestedList,
                inlineToolbar: true,
            },
            image: {
                class: ImageTool,
                config: {
                    endpoints: { byFile: uploadUrl },
                    additionalRequestHeaders: csrfHeaders(),
                },
            },
            linkTool: {
                class: LinkTool,
                config: {
                    endpoint: fetchUrl,
                    additionalRequestHeaders: csrfHeaders(),
                },
            },
            embed: Embed,
            table: Table,
            delimiter: Delimiter,
            warning: Warning,
            code: CodeTool,
            raw: RawTool,
            attaches: {
                class: AttachesTool,
                config: {
                    endpoint: uploadFileUrl,
                    additionalRequestHeaders: csrfHeaders(),
                },
            },
            inlineCode: {
                class: InlineCode,
                shortcut: 'CMD+SHIFT+C',
            },
        },
        placeholder: 'Start writing…',
    });

    // Intercept form submit — serialize editor data into hidden input before POST
    const form = editorEl.closest('form');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            try {
                const saved = await editor.save();
                hiddenInput.value = JSON.stringify(saved);
            } catch (err) {
                console.error('Editor.js save error', err);
            }
            form.submit();
        });
    }
})();
