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
