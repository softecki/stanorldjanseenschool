/** Normalize Laravel paginator / loose array-ish payloads */
export function safeArray(value) {
    if (!value) return [];
    if (Array.isArray(value)) return value;
    if (typeof value === 'object' && Array.isArray(value.data)) return value.data;
    return [];
}

/** Sidebar lists from API (paginator or array) */
export function sidebarRows(raw) {
    if (!raw) return [];
    if (Array.isArray(raw)) return raw;
    if (typeof raw === 'object' && Array.isArray(raw.data)) return raw.data;
    return [];
}

/** Laravel LengthAwarePaginator-like → safe list + page fields */
export function normalizePaginator(raw) {
    if (!raw || typeof raw !== 'object') return null;
    const data = Array.isArray(raw.data) ? raw.data : [];
    return {
        ...raw,
        data,
        current_page: raw.current_page ?? 1,
        last_page: raw.last_page ?? 1,
    };
}

export function stripHtml(html) {
    if (!html) return '';
    return String(html)
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

export function defaultSchoolMeta() {
    return {
        name: 'School',
        tagline: 'Excellence in education & character.',
        phone: '',
        email: '',
        address: '',
    };
}

/** Merge API meta.school with sensible defaults */
export function mergeSchoolMeta(meta) {
    return { ...defaultSchoolMeta(), ...(meta?.school || {}) };
}

/** Resolve upload/media paths from API models for <img src /> */
export function mediaUrl(path) {
    if (!path || typeof path !== 'string') return '';
    if (/^https?:\/\//i.test(path)) return path;
    const p = path.startsWith('/') ? path : `/${path}`;
    return p;
}

export function excerpt(htmlOrText, len = 160) {
    if (!htmlOrText) return '';
    const text = String(htmlOrText).replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
    return text.length <= len ? text : `${text.slice(0, len)}…`;
}

export function formatDate(d) {
    if (!d) return '';
    const dt = new Date(d);
    if (Number.isNaN(dt.getTime())) return String(d);
    return dt.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}
