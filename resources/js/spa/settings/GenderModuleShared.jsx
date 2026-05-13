import React from 'react';

export function Shell({ Layout, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-6xl space-y-6 p-6">{children}</div>
        </Layout>
    );
}

export function paginateRows(res) {
    const p = res.data?.data;
    if (p && Array.isArray(p.data)) return { rows: p.data, paginator: p };
    const list = Array.isArray(p) ? p : [];
    return { rows: list, paginator: null };
}
