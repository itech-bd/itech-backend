const SELECTOR = 'textarea.wysiwyg, textarea[data-editor="true"]';
const FLAG = 'tinymceInitialized';
let tinymceLoader = null;

function getTinymceBaseUrl() {
    const meta = document.querySelector('meta[name="tinymce-base-url"]');
    return meta?.getAttribute('content') ?? '/vendor/tinymce';
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta?.getAttribute('content') ?? '';
}

function getUploadUrl() {
    const meta = document.querySelector('meta[name="wysiwyg-upload-url"]');
    return meta?.getAttribute('content') ?? '';
}

function loadTinymce() {
    if (window.tinymce) {
        return Promise.resolve(window.tinymce);
    }

    if (tinymceLoader) {
        return tinymceLoader;
    }

    tinymceLoader = new Promise((resolve, reject) => {
        const baseUrl = getTinymceBaseUrl();
        const script = document.createElement('script');
        script.src = `${baseUrl}/tinymce.min.js`;
        script.referrerPolicy = 'origin';

        script.onload = () => {
            if (window.tinymce) {
                resolve(window.tinymce);
                return;
            }

            reject(new Error('TinyMCE did not initialize.'));
        };

        script.onerror = () => reject(new Error('Failed to load TinyMCE assets.'));
        document.head.appendChild(script);
    }).catch((error) => {
        tinymceLoader = null;
        throw error;
    });

    return tinymceLoader;
}

async function initOneTextarea(textarea) {
    if (!textarea || textarea.dataset[FLAG] === '1') {
        return;
    }

    textarea.dataset[FLAG] = '1';

    try {
        if (!textarea.id) {
            textarea.id = `wysiwyg_${Math.random().toString(36).slice(2)}`;
        }

        const tinymce = await loadTinymce();
        const uploadUrl = getUploadUrl();
        const csrfToken = getCsrfToken();
        const baseUrl = getTinymceBaseUrl();

        const textareaRect = textarea.getBoundingClientRect();
        const baseHeight = Math.round(textareaRect.height || 0);
        const autoresizeMinHeight = Math.max(160, baseHeight || 0);
        const availableViewportHeight = Math.round(window.innerHeight - textareaRect.top - 200);
        const autoresizeMaxHeight = Math.max(
            autoresizeMinHeight,
            Math.min(900, Math.max(240, availableViewportHeight))
        );

        await tinymce.init({
            target: textarea,
            license_key: 'gpl',
            base_url: baseUrl,
            language: 'en',
            menubar: false,
            branding: false,
            promotion: false,
            plugins:
                'advlist autolink link lists charmap anchor searchreplace visualblocks code fullscreen insertdatetime table help wordcount image autoresize',
            toolbar:
                'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code fullscreen',
            autoresize_min_height: autoresizeMinHeight,
            autoresize_max_height: autoresizeMaxHeight,
            skin: 'oxide',
            skin_url: `${baseUrl}/skins/ui/oxide`,
            content_css: `${baseUrl}/skins/content/default/content.min.css`,
            images_upload_handler: uploadUrl
                ? (blobInfo) =>
                      new Promise((resolve, reject) => {
                          const formData = new FormData();
                          formData.append('file', blobInfo.blob(), blobInfo.filename());

                          fetch(uploadUrl, {
                              method: 'POST',
                              body: formData,
                              credentials: 'same-origin',
                              headers: {
                                  Accept: 'application/json',
                                  ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                              },
                          })
                              .then(async (r) => {
                                  const data = await r.json().catch(() => null);
                                  if (!r.ok) {
                                      const message =
                                          data?.error?.message ||
                                          data?.message ||
                                          'Upload failed';
                                      throw new Error(message);
                                  }

                                  const url = data?.location || data?.url;
                                  if (!url) {
                                      throw new Error('Invalid upload response');
                                  }

                                  resolve(url);
                              })
                              .catch((e) => reject(e?.message || 'Upload failed'));
                      })
                : undefined,
            setup(editor) {
                // Ensure textarea stays in sync for Laravel validation + POST.
                editor.on('change keyup setcontent', () => editor.save());

                const form = textarea.closest('form');
                if (form) {
                    form.addEventListener('submit', () => editor.save());
                }

                textarea.__tinymceEditorId = editor.id;
            },
        });
    } catch (e) {
        // If initialization fails, allow retries (e.g. during hot reload)
        textarea.dataset[FLAG] = '0';
        // eslint-disable-next-line no-console
        console.error('WYSIWYG init failed:', e);
    }
}

export function initAdminWysiwyg(root = document) {
    const nodes = root.querySelectorAll(SELECTOR);
    nodes.forEach((textarea) => void initOneTextarea(textarea));

    // Auto-init for dynamically added admin form fields.
    if (!window.__wysiwygObserver) {
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                mutation.addedNodes.forEach((node) => {
                    if (!(node instanceof HTMLElement)) return;

                    if (node.matches?.(SELECTOR)) {
                        void initOneTextarea(node);
                        return;
                    }

                    const inner = node.querySelectorAll?.(SELECTOR);
                    inner?.forEach((el) => void initOneTextarea(el));
                });
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
        window.__wysiwygObserver = observer;
    }
}
