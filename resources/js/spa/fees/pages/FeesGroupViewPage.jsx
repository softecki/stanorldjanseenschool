import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    IconBanknote,
    IconBookOpen,
    IconCalendar,
    IconClipboardCheck,
    IconEdit,
    IconGlobe,
    IconHash,
    IconList,
    IconReceipt,
    IconTag,
    UiButtonLink,
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

function IconLayers({ className = 'h-5 w-5' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 2L2 7l10 5 10-5-10-5z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M2 17l10 5 10-5M2 12l10 5 10-5" />
        </svg>
    );
}

function IconChevronLeft({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M15 18l-6-6 6-6" />
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

function IconShieldCheck({ className = 'h-5 w-5' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
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

export function FeesGroupViewPage({ Layout }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(`/fees-group/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const raw = r.data?.data;
                if (raw != null && typeof raw === 'object' && !Array.isArray(raw)) {
                    setData(raw);
                } else {
                    setData(null);
                    setErr('Unexpected response from server.');
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load fees group.'))
            .finally(() => setLoading(false));
    }, [id]);

    const masters = useMemo(() => (Array.isArray(data?.fee_masters) ? data.fee_masters : []), [data]);
    const assigns = useMemo(() => (Array.isArray(data?.fee_assigns) ? data.fee_assigns : []), [data]);
    const mastersTotal = data?.fee_masters_count ?? masters.length;
    const assignsTotal = data?.fee_assigns_count ?? assigns.length;

    const detailRows = useMemo(() => {
        if (!data) return [];
        return [
            { key: 'id', label: 'Record ID', value: data.id, icon: <IconHash className="h-5 w-5" /> },
            { key: 'name', label: 'Group name', value: data.name?.trim() ? data.name : '—', icon: <IconTag className="h-5 w-5" /> },
            {
                key: 'status',
                label: 'Status',
                value: statusPill(data.status),
                icon: <IconPulse className="h-5 w-5" />,
            },
            {
                key: 'online_admission_fees',
                label: 'Online admission fees',
                value:
                    Number(data.online_admission_fees) === 1 ? (
                        <span className="inline-flex items-center gap-1.5 text-emerald-700">
                            <IconGlobe className="h-4 w-4 shrink-0" />
                            Enabled for online admission
                        </span>
                    ) : (
                        <span className="inline-flex items-center gap-1.5 text-gray-600">
                            <IconShieldCheck className="h-4 w-4 shrink-0 text-gray-400" />
                            Not used for online admission
                        </span>
                    ),
                icon: <IconGlobe className="h-5 w-5" />,
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

    return (
        <Layout>
            <div className="mx-auto max-w-6xl p-4 sm:p-6">
                {loading ? <UiPageLoader text="Loading fees group…" /> : null}

                {!loading && err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{err}</div>
                ) : null}

                {!loading && !err && data ? (
                    <>
                        <div className="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-600 p-6 text-white shadow-lg sm:p-8">
                            <div className="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10 blur-2xl" aria-hidden />
                            <div className="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-black/10 blur-2xl" aria-hidden />
                            <div className="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div className="flex gap-4">
                                    <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/30 backdrop-blur">
                                        <IconLayers className="h-8 w-8 text-white" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-wider text-indigo-100">Fees group</p>
                                        <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{data.name || `Group #${data.id}`}</h1>
                                        <p className="mt-2 max-w-2xl text-sm text-indigo-100/95">
                                            Overview of this fee bundle, linked masters, and class assignments.
                                        </p>
                                    </div>
                                </div>
                                <div className="flex flex-wrap gap-2 sm:justify-end">
                                    <UiButtonLink
                                        to="/groups"
                                        variant="secondary"
                                        className="border-white/40 bg-white/10 text-white shadow-none hover:bg-white/20"
                                        leftIcon={<IconChevronLeft />}
                                    >
                                        All groups
                                    </UiButtonLink>
                                    <UiButtonLink
                                        to={`/groups/${id}/edit`}
                                        variant="secondary"
                                        className="border-transparent bg-white text-indigo-700 shadow-md hover:bg-indigo-50"
                                        leftIcon={<IconEdit className="h-4 w-4" />}
                                    >
                                        Edit
                                    </UiButtonLink>
                                </div>
                            </div>
                            <div className="relative mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconReceipt className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Fee masters</p>
                                        <p className="text-xl font-bold tabular-nums">{mastersTotal}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconList className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Assignments</p>
                                        <p className="text-xl font-bold tabular-nums">{assignsTotal}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconBanknote className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Group ID</p>
                                        <p className="text-xl font-bold tabular-nums">#{data.id}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2">
                                        <IconClipboardCheck className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Status</p>
                                        <div className="mt-0.5 text-sm font-semibold">{Number(data.status) === 1 ? 'Active' : 'Inactive'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-6 grid gap-6 lg:grid-cols-3">
                            <div className="lg:col-span-2 space-y-6">
                                <section className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                                    <div className="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3">
                                        <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                            <IconClipboardCheck className="h-5 w-5" />
                                        </span>
                                        <h2 className="text-lg font-semibold text-gray-900">Database fields</h2>
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

                                <section className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                                    <div className="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3">
                                        <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                                            <IconBookOpen className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <h2 className="text-lg font-semibold text-gray-900">Description</h2>
                                            <p className="text-xs text-gray-500">Stored as plain text in the database</p>
                                        </div>
                                    </div>
                                    <div className="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-4 text-sm leading-relaxed text-gray-800">
                                        {data.description?.trim() ? data.description : <span className="text-gray-400">No description saved.</span>}
                                    </div>
                                </section>
                            </div>

                            <aside className="space-y-6">
                                <section className="rounded-2xl border border-gray-200 bg-gradient-to-b from-white to-gray-50/80 p-5 shadow-sm">
                                    <h3 className="text-sm font-semibold text-gray-900">Quick links</h3>
                                    <ul className="mt-3 space-y-2 text-sm">
                                        <li>
                                            <Link
                                                to="/masters"
                                                className="flex items-center gap-2 rounded-lg px-2 py-2 text-indigo-600 hover:bg-indigo-50"
                                            >
                                                <IconReceipt className="h-4 w-4 shrink-0" />
                                                Browse fee masters
                                            </Link>
                                        </li>
                                        <li>
                                            <Link
                                                to="/assignments"
                                                className="flex items-center gap-2 rounded-lg px-2 py-2 text-indigo-600 hover:bg-indigo-50"
                                            >
                                                <IconList className="h-4 w-4 shrink-0" />
                                                Browse assignments
                                            </Link>
                                        </li>
                                    </ul>
                                </section>
                            </aside>
                        </div>

                        <div className="mt-6 grid gap-6 lg:grid-cols-2">
                            <section className="rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                    <div className="flex items-center gap-2">
                                        <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                                            <IconReceipt className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <h2 className="text-base font-semibold text-gray-900">Fee masters</h2>
                                            <p className="text-xs text-gray-500">
                                                Showing {masters.length} of {mastersTotal} linked record{mastersTotal === 1 ? '' : 's'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <UiTableWrap className="rounded-none border-0 shadow-none">
                                    <UiTable>
                                        <UiTHead>
                                            <UiTR>
                                                <UiTH>Type</UiTH>
                                                <UiTH>Due</UiTH>
                                                <UiTH>Amount</UiTH>
                                                <UiTH>Status</UiTH>
                                            </UiTR>
                                        </UiTHead>
                                        <UiTBody>
                                            {masters.length === 0 ? (
                                                <UiTableEmptyRow colSpan={4} message="No fee masters linked to this group." />
                                            ) : (
                                                masters.map((m) => (
                                                    <UiTR key={m.id}>
                                                        <UiTD>{m.type?.name || '—'}</UiTD>
                                                        <UiTD>{m.due_date || '—'}</UiTD>
                                                        <UiTD className="tabular-nums">{formatMoney(m.amount)}</UiTD>
                                                        <UiTD>{statusPill(m.status)}</UiTD>
                                                    </UiTR>
                                                ))
                                            )}
                                        </UiTBody>
                                    </UiTable>
                                </UiTableWrap>
                            </section>

                            <section className="rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                                    <div className="flex items-center gap-2">
                                        <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 text-teal-700">
                                            <IconList className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <h2 className="text-base font-semibold text-gray-900">Class assignments</h2>
                                            <p className="text-xs text-gray-500">
                                                Showing {assigns.length} of {assignsTotal} linked record{assignsTotal === 1 ? '' : 's'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <UiTableWrap className="rounded-none border-0 shadow-none">
                                    <UiTable>
                                        <UiTHead>
                                            <UiTR>
                                                <UiTH>Class</UiTH>
                                                <UiTH>Section</UiTH>
                                                <UiTH>Assign ID</UiTH>
                                            </UiTR>
                                        </UiTHead>
                                        <UiTBody>
                                            {assigns.length === 0 ? (
                                                <UiTableEmptyRow colSpan={3} message="No class assignments for this group." />
                                            ) : (
                                                assigns.map((a) => (
                                                    <UiTR key={a.id}>
                                                        <UiTD>{a.class?.name || '—'}</UiTD>
                                                        <UiTD>{a.section?.name || '—'}</UiTD>
                                                        <UiTD className="tabular-nums text-gray-600">#{a.id}</UiTD>
                                                    </UiTR>
                                                ))
                                            )}
                                        </UiTBody>
                                    </UiTable>
                                </UiTableWrap>
                            </section>
                        </div>
                    </>
                ) : null}
            </div>
        </Layout>
    );
}
