import DOMPurify from 'dompurify';

// Sanitise HTML before it is rendered with v-html.
export function sanitizeHtml(html) {
    if (html === null || html === undefined) return '';
    return DOMPurify.sanitize(String(html), { USE_PROFILES: { html: true } });
}

// Escape user text so it can safely be wrapped in trusted markup.
export function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
