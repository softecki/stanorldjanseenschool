/** Normalize server redirect (path or full URL) to a React Router path under basename /app */
export function spaNavigatePath(redirect) {
    if (!redirect || typeof redirect !== 'string') return '/dashboard';
    if (redirect.startsWith('http')) {
        try {
            const u = new URL(redirect);
            let p = u.pathname;
            if (p.startsWith('/app')) p = p.slice(4) || '/';
            return p || '/';
        } catch (e) {
            return '/dashboard';
        }
    }
    if (redirect.startsWith('/app')) return redirect.slice(4) || '/';
    return redirect.startsWith('/') ? redirect : `/${redirect}`;
}
