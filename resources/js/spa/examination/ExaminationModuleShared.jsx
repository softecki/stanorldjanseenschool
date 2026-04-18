import React from 'react';

export function Shell({ Layout, children, wide = false }) {
    return (
        <Layout>
            <div className={wide ? 'mx-auto max-w-7xl space-y-6 p-6' : 'mx-auto max-w-3xl space-y-6 p-6'}>{children}</div>
        </Layout>
    );
}

export function readPaginate(res) {
    const p = res.data?.data;
    if (p && Array.isArray(p.data)) {
        return { rows: p.data, page: p.current_page, last: p.last_page };
    }
    return { rows: Array.isArray(p) ? p : [], page: 1, last: 1 };
}
