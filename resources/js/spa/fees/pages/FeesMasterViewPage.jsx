import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    IconBanknote,
    IconCalendar,
    IconClipboardCheck,
    IconEdit,
    IconHash,
    IconList,
    IconReceipt,
    IconTag,
    UiButtonLink,
    UiHeadRow,
    UiPageLoader,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../ui/UiKit';

function IconChevronLeft({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M15 18l-6-6 6-6" />
        </svg>
    );
}

function IconLayersStack({ className = 'h-5 w-5' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M4 7l8-4 8 4M4 12l8 4 8-4M4 17l8 4 8-4" />
        </svg>
    );
}

function IconPulse({ className = 'h-5 w-5' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M3 12h4l2-8 4 16 2-8h6" />
        </svg>
    );
}

function formatDateTime(v) {
    if (v == null || v === '') return '—';
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return String(v);
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}

function formatMoney(v) {
    if (v == null || v === '') return '—';
    const n = Number(v);
    if (Number.isNaN(n)) return String(v);
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function statusPill(status) {
    const n = Number(status);
    const active = n === 1;
    return (
        <span
            className={`inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700'
            }`}
        >
            <span className={`h-1.5 w-1.5 rounded-full ${active ? 'bg-emerald-500' : 'bg-gray-500'}`} aria-hidden />
            {active ? 'Active' : 'Inactive'}
        </span>
    );
}

function fineTypeLabel(ft) {
    const n = Number(ft);
    if (n === 1) return 'Percentage';
    if (n === 2) return 'Fixed amount';
    return 'None';
}

export function FeesMasterViewPage({ Layout }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(`/fees-master/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const raw = r.data?.data;
                if (raw != null && typeof raw === 'object' && !Array.isArray(raw)) {
                    setData(raw);
                } else {
                    setData(null);
                    setErr('Unexpected response from server.');
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load fee master.'))
            .finally(() => setLoading(false));
    }, [id]);

    const children = useMemo(() => (Array.isArray(data?.fees_master_childs) ? data.fees_master_childs : []), [data]);
    const childCount = data?.fees_master_childs_count ?? children.length;

    const detailRows = useMemo(() => {
        if (!data) return [];
        const due = data.due_date ? String(data.due_date).slice(0, 10) : '—';
        return [
            { key: 'id', label: 'Record ID', value: `#${data.id}`, icon: <IconHash className="h-5 w-5" /> },
            {
                key: 'session',
                label: 'Session',
                value: data.session?.name || '—',
                icon: <IconCalendar className="h-5 w-5" />,
            },
            {
                key: 'group',
                label: 'Fees group',
                value: data.group?.name || '—',
                icon: <IconList className="h-5 w-5" />,
            },
            {
                key: 'type',
                label: 'Fees type',
                value: data.type?.name || '—',
                icon: <IconTag className="h-5 w-5" />,
            },
            { key: 'due', label: 'Due date', value: due, icon: <IconCalendar className="h-5 w-5" /> },
            {
                key: 'amount',
                label: 'Base amount',
                value: <span className="font-medium tabular-nums">{formatMoney(data.amount)}</span>,
                icon: <IconBanknote className="h-5 w-5" />,
            },
            {
                key: 'fine_type',
                label: 'Fine type',
                value: fineTypeLabel(data.fine_type),
                icon: <IconClipboardCheck className="h-5 w-5" />,
            },
            {
                key: 'percentage',
                label: 'Fine percentage',
                value: <span className="tabular-nums">{data.percentage != null ? `${data.percentage}%` : '—'}</span>,
                icon: <IconReceipt className="h-5 w-5" />,
            },
            {
                key: 'fine_amount',
                label: 'Fine amount',
                value: <span className="tabular-nums">{formatMoney(data.fine_amount)}</span>,
                icon: <IconBanknote className="h-5 w-5" />,
            },
            {
                key: 'status',
                label: 'Status',
                value: statusPill(data.status),
                icon: <IconPulse className="h-5 w-5" />,
            },
            {
                key: 'created_at',
                label: 'Created',
                value: formatDateTime(data.created_at),
                icon: <IconCalendar className="h-5 w-5" />,
            },
            {
                key: 'updated_at',
                label: 'Last updated',
                value: formatDateTime(data.updated_at),
                icon: <IconCalendar className="h-5 w-5" />,
            },
        ];
    }, [data]);

    const titleLabel = data?.group?.name && data?.type?.name ? `${data.group.name} · ${data.type.name}` : data?.type?.name || data?.group?.name || `Master #${id}`;

    return (
        <Layout>
            <div className="mx-auto max-w-6xl p-4 sm:p-6">
                {loading ? <UiPageLoader text="Loading fee master…" /> : null}

                {!loading && err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{err}</div>
                ) : null}

                {!loading && !err && data ? (
                    <>
                        <div className="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-700 via-violet-700 to-purple-800 p-6 text-white shadow-lg sm:p-8">
                            <div className="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10 blur-2xl" aria-hidden />
                            <div className="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div className="flex gap-4">
                                    <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25 backdrop-blur">
                                        <IconLayersStack className="h-8 w-8 text-white" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-wider text-indigo-100">Fee master</p>
                                        <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{titleLabel}</h1>
                                        <p className="mt-2 max-w-2xl text-sm text-indigo-100/95">
                                            Amount, due date, and fine rules for this group and type in the linked session.
                                        </p>
                                    </div>
                                </div>
                                <div className="flex flex-wrap gap-2 sm:justify-end">
                                    <UiButtonLink
                                        to="/masters"
                                        variant="secondary"
                                        className="border-white/40 bg-white/10 text-white shadow-none hover:bg-white/20"
                                        leftIcon={<IconChevronLeft />}
                                    >
                                        All masters
                                    </UiButtonLink>
                                    <UiButtonLink
                                        to={`/masters/${id}/edit`}
                                        variant="secondary"
                                        className="border-transparent bg-white text-indigo-800 shadow-md hover:bg-indigo-50"
                                        leftIcon={<IconEdit className="h-4 w-4" />}
                                    >
                                        Edit
                                    </UiButtonLink>
                                </div>
                            </div>
                            <div className="relative mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconBanknote className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Amount</p>
                                        <p className="text-lg font-bold tabular-nums">{formatMoney(data.amount)}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconCalendar className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Due</p>
                                        <p className="text-sm font-semibold">{data.due_date ? String(data.due_date).slice(0, 10) : '—'}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconReceipt className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Child rows</p>
                                        <p className="text-xl font-bold tabular-nums">{childCount}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconHash className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Master ID</p>
                                        <p className="text-xl font-bold tabular-nums">#{data.id}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-6 grid gap-6 lg:grid-cols-3">
                            <div className="space-y-6 lg:col-span-2">
                                <section className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                                    <div className="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3">
                                        <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                                            <IconClipboardCheck className="h-5 w-5" />
                                        </span>
                                        <h2 className="text-lg font-semibold text-gray-900">All database fields</h2>
                                    </div>
                                    <ul className="divide-y divide-gray-100">
                                        {detailRows.map((row) => (
                                            <li key={row.key} className="flex flex-col gap-1 py-3 first:pt-0 sm:flex-row sm:items-center sm:gap-4">
                                                <div className="flex min-w-[10rem] items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                    <span className="text-gray-400">{row.icon}</span>
                                                    {row.label}
                                                </div>
                                                <div className="flex-1 text-sm text-gray-900">{row.value}</div>
                                            </li>
                                        ))}
                                    </ul>
                                </section>
                            </div>

                            <aside className="space-y-6">
                                <section className="rounded-2xl border border-gray-200 bg-gradient-to-b from-white to-gray-50/80 p-5 shadow-sm">
                                    <h3 className="text-sm font-semibold text-gray-900">Quick links</h3>
                                    <ul className="mt-3 space-y-2 text-sm">
                                        <li>
                                            <Link to="/masters" className="flex items-center gap-2 rounded-lg px-2 py-2 text-indigo-700 hover:bg-indigo-50">
                                                <IconList className="h-4 w-4 shrink-0" />
                                                All fee masters
                                            </Link>
                                        </li>
                                        <li>
                                            <Link to="/groups" className="flex items-center gap-2 rounded-lg px-2 py-2 text-indigo-700 hover:bg-indigo-50">
                                                <IconList className="h-4 w-4 shrink-0" />
                                                Fees groups
                                            </Link>
                                        </li>
                                        <li>
                                            <Link to="/types" className="flex items-center gap-2 rounded-lg px-2 py-2 text-indigo-700 hover:bg-indigo-50">
                                                <IconTag className="h-4 w-4 shrink-0" />
                                                Fees types
                                            </Link>
                                        </li>
                                    </ul>
                                </section>
                            </aside>
                        </div>

                        <section className="mt-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                <div className="flex items-center gap-2">
                                    <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                                        <IconReceipt className="h-5 w-5" />
                                    </span>
                                    <div>
                                        <h2 className="text-base font-semibold text-gray-900">Linked fee master children</h2>
                                        <p className="text-xs text-gray-500">
                                            Showing {children.length} of {childCount} row{childCount === 1 ? '' : 's'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <UiTableWrap className="rounded-none border-0 shadow-none">
                                <UiTable>
                                    <UiTHead>
                                        <UiHeadRow>
                                            <UiTH>Child ID</UiTH>
                                            <UiTH>Fees type</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {children.length === 0 ? (
                                            <UiTableEmptyRow colSpan={2} message="No child rows (legacy structure may still create one row on save)." />
                                        ) : (
                                            children.map((ch) => (
                                                <UiTR key={ch.id}>
                                                    <UiTD className="font-mono text-xs text-slate-600">#{ch.id}</UiTD>
                                                    <UiTD>{ch.type?.name || '—'}</UiTD>
                                                </UiTR>
                                            ))
                                        )}
                                    </UiTBody>
                                </UiTable>
                            </UiTableWrap>
                        </section>
                    </>
                ) : null}
            </div>
        </Layout>
    );
}
