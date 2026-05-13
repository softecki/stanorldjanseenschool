import React from 'react';
import { UiPageLoader } from '../ui/UiKit';

export function FullPageLoader(props) {
    return <UiPageLoader {...props} />;
}

export function Panel({ Layout, title, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{title}</h1>
                </div>
                {children}
            </div>
        </Layout>
    );
}

export function firstValue(row, keys) {
    for (const key of keys) {
        const v = row?.[key];
        if (v !== undefined && v !== null && v !== '') return v;
    }
    return '-';
}

export function normalizeRows(payload) {
    if (Array.isArray(payload?.data?.data)) return payload.data.data;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload)) return payload;
    return [];
}

/** Index endpoints that return Laravel `paginate()` (items in `data.data`, meta at top). */
export function normalizePagedList(payload) {
    const paged = payload?.data;
    if (paged && Array.isArray(paged.data)) {
        return {
            rows: paged.data,
            meta: payload?.meta || {},
            pagination: {
                current_page: paged.current_page ?? 1,
                last_page: paged.last_page ?? 1,
                per_page: paged.per_page ?? 15,
                total: paged.total ?? paged.data.length,
            },
        };
    }
    // `{ data: [ ...rows ], meta }` without Laravel paginator wrapper
    if (Array.isArray(paged)) {
        const n = paged.length;
        return {
            rows: paged,
            meta: payload?.meta || {},
            pagination: { current_page: 1, last_page: 1, per_page: Math.max(n, 1), total: n },
        };
    }
    const rows = normalizeRows(payload);
    return {
        rows,
        meta: payload?.meta || {},
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: rows.length },
    };
}

export function optionFrom(item) {
    return {
        id: item?.id ?? item?.class?.id ?? item?.section?.id ?? item?.subject?.id ?? item?.teacher?.id ?? '',
        name: item?.name
            ?? item?.title
            ?? (item?.first_name || item?.last_name ? `${item?.first_name || ''} ${item?.last_name || ''}`.trim() : '')
            ?? item?.class?.name
            ?? item?.section?.name
            ?? item?.subject?.name
            ?? item?.teacher?.name
            ?? `#${item?.id ?? ''}`,
    };
}
