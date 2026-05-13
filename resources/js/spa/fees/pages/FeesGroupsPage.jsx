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

function YesNoAdmission({ value }) {
    const on = Number(value) === 1;
    return on ? (
        <span className="inline-flex rounded-md bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-800 ring-1 ring-sky-700/10">Yes</span>
    ) : (
        <span className="inline-flex rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-slate-200">No</span>
    );
}

export function FeesGroupsPage({ Layout }) {
    return (
        <EntityListPage
            Layout={Layout}
            variant="corporate"
            title="Fees Groups"
            createButtonLabel="Add fee group"
            entityLabel="fee groups"
            hideEyebrow
            hideTitle
            ignoreMetaTitle
            endpoint="/fees-group"
            baseRoute="/groups"
            createRoute="/groups/create"
            deleteEndpoint="/fees-group/delete"
            columns={[
                {
                    key: 'name',
                    label: 'Group name',
                    thClassName: 'min-w-[12rem]',
                    tdClassName: 'font-medium text-slate-900',
                    render: (r) => r?.name || <span className="font-normal text-slate-400">—</span>,
                },
                {
                    key: 'description',
                    label: 'Description',
                    thClassName: 'min-w-[14rem] max-w-md',
                    tdClassName: 'max-w-md',
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
                    key: 'online_admission_fees',
                    label: 'Online admission',
                    thClassName: 'whitespace-nowrap',
                    tdClassName: 'whitespace-nowrap',
                    render: (r) => <YesNoAdmission value={r?.online_admission_fees} />,
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
