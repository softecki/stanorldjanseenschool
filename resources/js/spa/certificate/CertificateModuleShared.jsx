import React from 'react';

export function Shell({ Layout, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-5xl space-y-6 p-6">{children}</div>
        </Layout>
    );
}

export function paginateRows(r) {
    const p = r.data?.data;
    if (Array.isArray(p?.data)) return p.data;
    if (Array.isArray(p)) return p;
    return [];
}

export function splitTitle(title) {
    if (!title) return { first: '', rest: '' };
    const words = String(title).trim().split(/\s+/);
    return { first: words[0] || '', rest: words.slice(1).join(' ') };
}

export function buildDescriptionHtml(cert, row, sessionName, settings) {
    let d = cert.description || '';
    const name = `${row.student?.first_name || ''} ${row.student?.last_name || ''}`.trim();
    d = d.replace(/\[student_name\]/g, name);
    d = d.replace(/\[class_name\]/g, row.class?.name || '');
    d = d.replace(/\[section_name\]/g, row.section?.name || '');
    d = d.replace(/\[school_name\]/g, settings?.application_name || '');
    d = d.replace(/\[session\]/g, sessionName || '');
    d = d.replace(/\[school_address\]/g, settings?.address || '');
    return d;
}
