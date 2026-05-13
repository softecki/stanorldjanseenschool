import React, { useState } from 'react';
import { EntityListPage } from '../FeesModuleShared';
import { FeesMastersQuartersTab } from './FeesMastersQuartersTab';

function formatMoney(v) {
    if (v == null || v === '') return '—';
    const n = Number(v);
    if (Number.isNaN(n)) return '—';
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

const tabBtn =
    'rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40';

export function FeesMastersPage({ Layout }) {
    const [tab, setTab] = useState('masters');

    return (
        <Layout>
            <div className="mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div className="mb-6 flex flex-wrap items-center gap-2 border-b border-slate-200 pb-4">
                    <button
                        type="button"
                        className={`${tabBtn} ${tab === 'masters' ? 'bg-white text-indigo-800 shadow ring-1 ring-slate-200' : 'text-slate-600 hover:bg-white/80'}`}
                        onClick={() => setTab('masters')}
                    >
                        Masters
                    </button>
                    <button
                        type="button"
                        className={`${tabBtn} ${tab === 'quarters' ? 'bg-white text-indigo-800 shadow ring-1 ring-slate-200' : 'text-slate-600 hover:bg-white/80'}`}
                        onClick={() => setTab('quarters')}
                    >
                        Quarters
                    </button>
                </div>

                {tab === 'masters' ? (
                    <EntityListPage
                        skipLayout
                        Layout={Layout}
                        variant="corporate"
                        title="Fees Masters"
                        createButtonLabel="Add fee master"
                        hideEyebrow
                        hideTitle
                        ignoreMetaTitle
                        entityLabel="fee masters"
                        endpoint="/fees-master"
                        baseRoute="/masters"
                        createRoute="/masters/create"
                        deleteEndpoint="/fees-master/delete"
                        columns={[
                            {
                                key: 'group',
                                label: 'Fees group',
                                thClassName: 'min-w-[10rem]',
                                tdClassName: 'font-medium text-slate-900',
                                render: (r) => r?.group?.name || <span className="text-slate-400">—</span>,
                            },
                            {
                                key: 'type',
                                label: 'Fees type',
                                thClassName: 'min-w-[9rem]',
                                tdClassName: 'text-slate-800',
                                render: (r) => r?.type?.name || <span className="text-slate-400">—</span>,
                            },
                            {
                                key: 'session',
                                label: 'Session',
                                thClassName: 'whitespace-nowrap',
                                tdClassName: 'text-slate-600',
                                render: (r) => r?.session?.name || '—',
                            },
                            {
                                key: 'amount',
                                label: 'Amount',
                                thClassName: 'text-right whitespace-nowrap',
                                tdClassName: 'text-right tabular-nums font-medium text-slate-900',
                                render: (r) => formatMoney(r?.amount),
                            },
                            {
                                key: 'status',
                                label: 'Status',
                                thClassName: 'whitespace-nowrap',
                                tdClassName: 'whitespace-nowrap',
                                render: (r) => <StatusBadge active={Number(r?.status) === 1} />,
                            },
                        ]}
                    />
                ) : (
                    <FeesMastersQuartersTab />
                )}
            </div>
        </Layout>
    );
}
