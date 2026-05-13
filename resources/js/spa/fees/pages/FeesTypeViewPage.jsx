import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    IconBanknote,
    IconBookOpen,
    IconCalendar,
    IconEdit,
    IconHash,
    IconList,
    IconReceipt,
    IconTag,
    IconUsers,
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

function IconSparkles({ className = 'h-5 w-5' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.847a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 00-3.09 3.09z" />
        </svg>
    );
}

/** Never render raw objects — use name/title or em dash. */
function pickDisplayName(value) {
    if (value == null) return null;
    if (typeof value === 'string') {
        const t = value.trim();
        return t.length ? t : null;
    }
    if (typeof value === 'object' && !Array.isArray(value)) {
        const n = value.name ?? value.title;
        if (typeof n === 'string' && n.trim()) return n.trim();
    }
    return null;
}

function formatDateTime(v) {
    if (v == null || v === '') return '—';
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return '—';
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}

function formatMoney(v) {
    if (v == null || v === '') return '—';
    const n = Number(v);
    if (Number.isNaN(n)) return '—';
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDue(v) {
    if (v == null || v === '') return '—';
    const s = String(v);
    return s.length >= 10 ? s.slice(0, 10) : s;
}

function statusPill(status) {
    const n = Number(status);
    const active = n === 1;
    return (
        <span
            className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ${
                active ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-600/15' : 'bg-slate-100 text-slate-700 ring-1 ring-slate-400/20'
            }`}
        >
            <span className={`h-1.5 w-1.5 rounded-full ${active ? 'bg-emerald-500' : 'bg-slate-400'}`} aria-hidden />
            {active ? 'Active' : 'Inactive'}
        </span>
    );
}

function InfoCard({ icon, label, children, className = '' }) {
    return (
        <div
            className={`flex gap-4 rounded-2xl border border-slate-200/90 bg-gradient-to-br from-white to-slate-50/80 p-5 shadow-sm ring-1 ring-slate-900/[0.03] ${className}`}
        >
            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">{icon}</div>
            <div className="min-w-0 flex-1">
                <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{label}</p>
                <div className="mt-1.5 text-sm leading-snug text-slate-900">{children}</div>
            </div>
        </div>
    );
}

export function FeesTypeViewPage({ Layout }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(`/fees-type/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const raw = r.data?.data;
                if (raw != null && typeof raw === 'object' && !Array.isArray(raw)) {
                    setData(raw);
                } else {
                    setData(null);
                    setErr('Unexpected response from server.');
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load fees type.'))
            .finally(() => setLoading(false));
    }, [id]);

    const masters = useMemo(() => (Array.isArray(data?.fee_masters) ? data.fee_masters : []), [data]);
    const mastersTotal = data?.fee_masters_count ?? masters.length;

    const classLabel = pickDisplayName(data?.school_class);
    const classId = data?.class_id != null && Number(data.class_id) > 0 ? Number(data.class_id) : null;

    const studentCategoryLabels = useMemo(() => {
        const arr = data?.student_categories;
        if (!Array.isArray(arr) || arr.length === 0) return null;
        return arr
            .map((c) => (typeof c?.name === 'string' && c.name.trim() ? c.name.trim() : null))
            .filter(Boolean);
    }, [data]);

    return (
        <Layout>
            <div className="mx-auto max-w-6xl p-4 sm:p-6">
                {loading ? <UiPageLoader text="Loading fees type…" /> : null}

                {!loading && err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{err}</div>
                ) : null}

                {!loading && !err && data ? (
                    <>
                        <div className="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-700 via-violet-700 to-fuchsia-800 p-6 text-white shadow-xl sm:p-8">
                            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(800px_circle_at_20%_-20%,rgba(255,255,255,0.18),transparent)]" aria-hidden />
                            <div className="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-white/10 blur-3xl" aria-hidden />
                            <div className="relative flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                <div className="flex min-w-0 gap-4">
                                    <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/30 backdrop-blur">
                                        <IconLayersStack className="h-9 w-9 text-white" />
                                    </div>
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-indigo-100">Fee type</p>
                                            {statusPill(data.status)}
                                            {data.code?.trim() ? (
                                                <span className="inline-flex items-center rounded-md bg-white/15 px-2 py-0.5 font-mono text-xs font-medium text-white ring-1 ring-white/25">
                                                    {data.code.trim()}
                                                </span>
                                            ) : null}
                                        </div>
                                        <h1 className="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">{data.name?.trim() || `Type #${data.id}`}</h1>
                                        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-indigo-100/95">
                                            Structured view of this fee type: identifiers, class scope, audit times, and related fee masters — all
                                            formatted for reading (no raw JSON).
                                        </p>
                                    </div>
                                </div>
                                <div className="flex shrink-0 flex-wrap gap-2">
                                    <UiButtonLink
                                        to="/types"
                                        variant="secondary"
                                        className="border-white/40 bg-white/10 text-white shadow-none hover:bg-white/20"
                                        leftIcon={<IconChevronLeft />}
                                    >
                                        All types
                                    </UiButtonLink>
                                    <UiButtonLink
                                        to={`/types/${id}/edit`}
                                        variant="secondary"
                                        className="border-transparent bg-white text-indigo-800 shadow-md hover:bg-indigo-50"
                                        leftIcon={<IconEdit className="h-4 w-4" />}
                                    >
                                        Edit
                                    </UiButtonLink>
                                </div>
                            </div>

                            <div className="relative mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3.5 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2 text-white">
                                        <IconHash className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Type ID</p>
                                        <p className="text-xl font-bold tabular-nums">#{data.id}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3.5 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2 text-white">
                                        <IconReceipt className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Fee masters</p>
                                        <p className="text-xl font-bold tabular-nums">{mastersTotal}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3.5 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2 text-white">
                                        <IconUsers className="h-5 w-5" />
                                    </div>
                                    <div className="min-w-0">
                                        <p className="text-xs font-medium text-indigo-100">Class scope</p>
                                        <p className="truncate text-sm font-semibold">{classLabel || (classId ? `Class #${classId}` : 'All classes')}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3.5 ring-1 ring-white/20 backdrop-blur">
                                    <div className="rounded-lg bg-white/15 p-2 text-white">
                                        <IconCalendar className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-xs font-medium text-indigo-100">Last change</p>
                                        <p className="text-sm font-semibold leading-tight">{formatDateTime(data.updated_at)}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8">
                            <div className="mb-4 flex items-center gap-2">
                                <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                    <IconSparkles className="h-5 w-5" />
                                </span>
                                <h2 className="text-lg font-semibold text-slate-900">At a glance</h2>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <InfoCard icon={<IconTag className="h-6 w-6" />} label="Type name">
                                    <span className="text-base font-semibold">{data.name?.trim() || '—'}</span>
                                </InfoCard>
                                <InfoCard icon={<IconHash className="h-6 w-6" />} label="Code">
                                    {data.code?.trim() ? (
                                        <span className="inline-flex rounded-md bg-slate-100 px-2 py-1 font-mono text-sm font-medium text-slate-800">
                                            {data.code.trim()}
                                        </span>
                                    ) : (
                                        <span className="text-slate-500">No code set</span>
                                    )}
                                </InfoCard>
                                <InfoCard icon={<IconUsers className="h-6 w-6" />} label="Linked class">
                                    {classLabel ? (
                                        <span className="inline-flex items-center gap-2 font-medium text-slate-800">
                                            <IconUsers className="h-4 w-4 text-indigo-500" aria-hidden />
                                            {classLabel}
                                        </span>
                                    ) : classId ? (
                                        <span className="font-mono text-sm text-slate-700">Class ID #{classId}</span>
                                    ) : (
                                        <span className="text-slate-500">Not tied to a single class</span>
                                    )}
                                </InfoCard>
                                <InfoCard icon={<IconTag className="h-6 w-6" />} label="Student categories">
                                    {studentCategoryLabels && studentCategoryLabels.length > 0 ? (
                                        <ul className="list-inside list-disc space-y-1 text-sm font-medium text-slate-800">
                                            {studentCategoryLabels.map((name) => (
                                                <li key={name}>{name}</li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <span className="text-slate-500">Not restricted to specific student categories</span>
                                    )}
                                </InfoCard>
                                <InfoCard icon={<IconPulse className="h-6 w-6" />} label="Status">
                                    <div className="flex flex-wrap items-center gap-2">{statusPill(data.status)}</div>
                                </InfoCard>
                            </div>
                        </div>

                        <div className="mt-8 grid gap-4 sm:grid-cols-2">
                            <div className="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                                    <IconCalendar className="h-5 w-5" />
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</p>
                                    <p className="mt-1 text-sm font-medium text-slate-900">{formatDateTime(data.created_at)}</p>
                                </div>
                            </div>
                            <div className="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-700">
                                    <IconCalendar className="h-5 w-5" />
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Last updated</p>
                                    <p className="mt-1 text-sm font-medium text-slate-900">{formatDateTime(data.updated_at)}</p>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 grid gap-6 lg:grid-cols-3">
                            <div className="lg:col-span-2">
                                <section className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                                    <div className="mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                                        <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                                            <IconBookOpen className="h-5 w-5" />
                                        </span>
                                        <div>
                                            <h2 className="text-lg font-semibold text-slate-900">Description</h2>
                                            <p className="text-xs text-slate-500">Plain text from the database</p>
                                        </div>
                                    </div>
                                    <div className="rounded-xl border border-dashed border-slate-200 bg-slate-50/90 p-5 text-sm leading-relaxed text-slate-800">
                                        {data.description?.trim() ? (
                                            data.description.trim()
                                        ) : (
                                            <span className="flex items-center gap-2 text-slate-500">
                                                <IconBookOpen className="h-4 w-4 shrink-0 opacity-60" aria-hidden />
                                                No description saved for this type.
                                            </span>
                                        )}
                                    </div>
                                </section>
                            </div>

                            <aside>
                                <section className="rounded-2xl border border-slate-200 bg-gradient-to-b from-white to-slate-50/90 p-5 shadow-sm">
                                    <h3 className="text-sm font-semibold text-slate-900">Quick links</h3>
                                    <ul className="mt-3 space-y-1 text-sm">
                                        <li>
                                            <Link
                                                to="/masters"
                                                className="flex items-center gap-2 rounded-lg px-2 py-2.5 text-indigo-700 transition hover:bg-indigo-50"
                                            >
                                                <IconReceipt className="h-4 w-4 shrink-0" />
                                                Fee masters
                                            </Link>
                                        </li>
                                        <li>
                                            <Link
                                                to="/types"
                                                className="flex items-center gap-2 rounded-lg px-2 py-2.5 text-indigo-700 transition hover:bg-indigo-50"
                                            >
                                                <IconList className="h-4 w-4 shrink-0" />
                                                All fee types
                                            </Link>
                                        </li>
                                    </ul>
                                </section>
                            </aside>
                        </div>

                        <section className="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md ring-1 ring-slate-900/[0.04]">
                            <div className="border-b border-slate-100 bg-gradient-to-r from-amber-50/80 to-white px-5 py-4 sm:px-6">
                                <div className="flex flex-wrap items-center gap-3">
                                    <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                                        <IconBanknote className="h-5 w-5" />
                                    </span>
                                    <div>
                                        <h2 className="text-base font-semibold text-slate-900">Fee masters using this type</h2>
                                        <p className="text-xs text-slate-600">
                                            Showing {masters.length} of {mastersTotal} — group, session, due date, and amount are shown as labels, not
                                            JSON.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <UiTableWrap className="rounded-none border-0 shadow-none ring-0">
                                <UiTable>
                                    <UiTHead>
                                        <UiHeadRow className="bg-slate-50">
                                            <UiTH className="text-xs font-semibold uppercase tracking-wide text-slate-600">Master</UiTH>
                                            <UiTH className="text-xs font-semibold uppercase tracking-wide text-slate-600">Group</UiTH>
                                            <UiTH className="text-xs font-semibold uppercase tracking-wide text-slate-600">Session</UiTH>
                                            <UiTH className="text-xs font-semibold uppercase tracking-wide text-slate-600">Due</UiTH>
                                            <UiTH className="text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Amount</UiTH>
                                            <UiTH className="text-xs font-semibold uppercase tracking-wide text-slate-600">Status</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {masters.length === 0 ? (
                                            <UiTableEmptyRow colSpan={6} message="No fee masters use this type yet." />
                                        ) : (
                                            masters.map((m) => (
                                                <UiTR key={m.id}>
                                                    <UiTD className="font-medium">
                                                        <Link to={`/masters/${m.id}`} className="inline-flex items-center gap-1.5 text-indigo-600 hover:underline">
                                                            <IconHash className="h-3.5 w-3.5 shrink-0 opacity-70" aria-hidden />
                                                            <span className="tabular-nums">#{m.id}</span>
                                                        </Link>
                                                    </UiTD>
                                                    <UiTD className="text-slate-800">{pickDisplayName(m.group) || '—'}</UiTD>
                                                    <UiTD className="text-slate-700">{pickDisplayName(m.session) || '—'}</UiTD>
                                                    <UiTD className="whitespace-nowrap text-slate-700">{formatDue(m.due_date)}</UiTD>
                                                    <UiTD className="text-right tabular-nums font-medium text-slate-900">{formatMoney(m.amount)}</UiTD>
                                                    <UiTD>{statusPill(m.status)}</UiTD>
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
