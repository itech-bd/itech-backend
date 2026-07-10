document.addEventListener('DOMContentLoaded', () => {
    const hasWysiwygTarget = document.querySelector('textarea.wysiwyg, textarea[data-editor="true"]');

    if (!hasWysiwygTarget) {
        return;
    }

    import('./admin/wysiwyg')
        .then(({ initAdminWysiwyg }) => {
            initAdminWysiwyg();
        })
        .catch((error) => {
            // eslint-disable-next-line no-console
            console.error('Failed to load admin WYSIWYG bundle:', error);
        });
});
