import React from 'react';
import { EntityListPage } from '../FeesModuleShared';

function formatShortDate(v) {
    if (v == null || v === '') return '—';
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

function StatusBadge({ active }) {
    return active ? (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-600/15">
            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden />
            Active
        </span>
    ) : (
        <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-500/10">
            <span className="h-1.5 w-1.5 rounded-full bg-slate-400" aria-hidden />
            Inactive
        </span>
    );
}

export function FeesTypesPage({ Layout }) {
    return (
        <EntityListPage
            Layout={Layout}
            variant="corporate"
            title="Fees Types"
            createButtonLabel="Add fee type"
            hideEyebrow
            hideTitle
            ignoreMetaTitle
            entityLabel="fee types"
            endpoint="/fees-type"
            baseRoute="/types"
            createRoute="/types/create"
            deleteEndpoint="/fees-type/delete"
            columns={[
                {
                    key: 'name',
                    label: 'Type name',
                    thClassName: 'min-w-[11rem]',
                    tdClassName: 'font-medium text-slate-900',
                    render: (r) => r?.name || <span className="font-normal text-slate-400">—</span>,
                },
                {
                    key: 'code',
                    label: 'Code',
                    thClassName: 'w-[7rem]',
                    tdClassName: 'font-mono text-xs text-slate-700',
                    render: (r) => {
                        const c = (r?.code || '').trim();
                        return c ? <span>{c}</span> : <span className="text-slate-400">—</span>;
                    },
                },
                {
                    key: 'description',
                    label: 'Description',
                    thClassName: 'min-w-[12rem] max-w-sm',
                    tdClassName: 'max-w-sm',
                    render: (r) => {
                        const text = (r?.description || '').trim();
                        if (!text) return <span className="text-slate-400">—</span>;
                        return (
                            <span className="line-clamp-2 text-slate-600" title={text}>
                                {text}
                            </span>
                        );
                    },
                },
                {
                    key: 'class',
                    label: 'Class',
                    thClassName: 'whitespace-nowrap',
                    tdClassName: 'text-slate-700',
                    render: (r) => r?.school_class?.name || <span className="text-slate-400">All / none</span>,
                },
                {
                    key: 'status',
                    label: 'Status',
                    thClassName: 'whitespace-nowrap',
                    tdClassName: 'whitespace-nowrap',
                    render: (r) => <StatusBadge active={Number(r?.status) === 1} />,
                },
                {
                    key: 'updated_at',
                    label: 'Last updated',
                    thClassName: 'whitespace-nowrap',
                    tdClassName: 'whitespace-nowrap text-slate-600',
                    render: (r) => formatShortDate(r?.updated_at),
                },
            ]}
        />
    );
}
