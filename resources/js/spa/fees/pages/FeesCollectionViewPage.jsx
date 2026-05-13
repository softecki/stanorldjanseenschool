import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader } from '../FeesModuleShared';
import {
    IconBanknote,
    IconCalendar,
    IconClipboardCheck,
    IconEdit,
    IconHash,
    IconList,
    IconReceipt,
    IconTag,
    IconTrash,
    IconX,
    UiButtonLink,
} from '../../ui/UiKit';
import { AnimatedMoney } from '../../ui/AnimatedMoney';

const COUNT_UP_MS = 3000;

function formatMoney(n) {
    const x = Number(n);
    if (!Number.isFinite(x)) return '—';
    return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** Monetary / bucket fields: count up; skip plain ids and counts like printed. */
function isAnimatedMoneyKey(key) {
    const k = String(key).toLowerCase();
    if (/_id$/.test(k) || k === 'id' || k === 'printed' || k === 'payment_method' || k === 'fine_type') return false;
    if (k === 'percentage' || k === 'collect_status') return false;
    return (
        k.includes('amount')
        || k.includes('paid')
        || k.includes('remained')
        || k.includes('balance')
        || k.includes('outstanding')
        || k.includes('quater')
        || k.includes('quarter')
        || k.includes('transaction_amount')
    );
}

function formatDate(raw) {
    if (raw == null || raw === '') return '—';
    const d = typeof raw === 'string' ? new Date(raw) : raw;
    if (d instanceof Date && !Number.isNaN(d.getTime())) {
        return d.toLocaleString(undefined, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    return String(raw);
}

function studentLabel(row) {
    const s = row?.student;
    if (!s) return row?.student_id != null ? `Student #${row.student_id}` : '—';
    const name = `${s.first_name || ''} ${s.last_name || ''}`.trim();
    return name || `Student #${row.student_id ?? s.id ?? ''}`;
}

function feeName(row) {
    const fm = row?.fees_master ?? row?.feesMaster;
    return (
        fm?.name
        ?? fm?.type?.name
        ?? fm?.fee_type_name /* rare flattened API */
        ?? '—'
    );
}

function detailIconForKey(key) {
    const k = String(key).toLowerCase();
    if (k === 'date' || k.endsWith('_at')) return IconCalendar;
    if (k.includes('quater') || k.includes('quarter')) return IconBanknote;
    if (k.includes('amount') || k === 'amount' || k.includes('paid') || k.includes('remained') || k.includes('fine_amount'))
        return IconBanknote;
    if (k.includes('id') || k === 'printed' || k === 'payment_method') return IconHash;
    return IconList;
}

const PAYMENT_ICON_STYLES = [
    'text-emerald-600',
    'text-sky-600',
    'text-violet-600',
    'text-amber-600',
    'text-rose-600',
    'text-cyan-600',
    'text-indigo-600',
];

function iconClassForPayKeyAt(i) {
    return PAYMENT_ICON_STYLES[i % PAYMENT_ICON_STYLES.length];
}

function formatScalarField(key, val) {
    const k = String(key).toLowerCase();
    if (k === 'collect_status') {
        const n = Number(val);
        if (n === 1) return 'Active (collectable)';
        if (n === 0) return 'Cancelled';
        return val === null || val === undefined || val === '' ? '—' : String(val);
    }
    if (val === null || val === undefined || val === '') return '—';
    if (typeof val === 'boolean') return val ? 'Yes' : 'No';
    if (k === 'date' || k.endsWith('_at')) return formatDate(val);
    if (isAnimatedMoneyKey(key)) {
        const x = Number(val);
        if (!Number.isFinite(x)) return '—';
        return <AnimatedMoney value={x} durationMs={COUNT_UP_MS} />;
    }
    if (k.includes('amount') || k.includes('paid') || k.includes('remained') || k.includes('fine')) {
        return formatMoney(val);
    }
    return String(val);
}

function DetailRow({ icon: Icon, label, children, iconClass = 'text-blue-600' }) {
    return (
        <div className="flex gap-3 rounded-xl border border-gray-100 bg-white p-3 shadow-sm ring-1 ring-gray-50">
            <div
                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 ${iconClass}`}
                aria-hidden
            >
                <Icon className="h-5 w-5" />
            </div>
            <div className="min-w-0 flex-1">
                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</p>
                <p className="mt-0.5 text-sm font-semibold text-gray-900">{children}</p>
            </div>
        </div>
    );
}

function SectionCard({ icon: Icon, title, subtitle, iconWrapClass, children }) {
    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
            <div className="flex items-start gap-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-4 py-3">
                <div className={`mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-gray-200/80 ${iconWrapClass}`}>
                    <Icon className="h-5 w-5 text-white" />
                </div>
                <div>
                    <h2 className="text-sm font-semibold text-gray-900">{title}</h2>
                    {subtitle ? <p className="mt-0.5 text-xs text-gray-500">{subtitle}</p> : null}
                </div>
            </div>
            <div className="p-4">{children}</div>
        </div>
    );
}

/** Top-level keys on the assign-child payload that are relations or redundant UI — not "line" scalars. */
const ASSIGN_LINE_SKIP = new Set([
    'student',
    'fees_master',
    'feesMaster',
    'fees_assign',
    'feesAssign',
    'fees_collect',
    'feesCollect',
    'fees_collects',
    'session_enrollment_for_assign',
]);

/**
 * Flatten own scalars on an object (exclude nested plain objects / models).
 * Includes null and '' so the UI can show "—" for everything stored.
 */
function scalarFieldEntries(obj, skip = new Set()) {
    if (!obj || typeof obj !== 'object') return [];
    return Object.entries(obj)
        .filter(([k, v]) => {
            if (skip.has(k)) return false;
            if (v != null && typeof v === 'object' && !Array.isArray(v)) return false;
            return true;
        })
        .sort(([a], [b]) => a.localeCompare(b))
        .map(([k, v]) => ({ k, v }));
}

function assignLineScalarRows(assign) {
    return scalarFieldEntries(assign, ASSIGN_LINE_SKIP);
}

const FEES_ASSIGN_NESTED = new Set(['session', 'section', 'class', 'category', 'gender', 'group', 'fees_group', 'feesGroup']);

function feesAssignHeaderRows(fa) {
    if (!fa || typeof fa !== 'object') return [];
    const rows = scalarFieldEntries(fa, FEES_ASSIGN_NESTED);
    const push = (k, v) => {
        if (v != null && v !== '') rows.push({ k, v });
    };
    const pushNumeric = (k, v) => {
        if (v == null || v === '') return;
        rows.push({ k, v });
    };

    /** Raw FKs + timestamps already come from scalarFieldEntries; below adds INNER JOIN dimensions. */

    if (fa.session && typeof fa.session === 'object') {
        push('joined_session_title', fa.session.name ?? fa.session.title);
        push('joined_session_start_date', fa.session.start_date);
        push('joined_session_end_date', fa.session.end_date);
        pushNumeric('joined_sessions_table_pk', fa.session.id);
    }
    if (fa.class && typeof fa.class === 'object') {
        push('joined_class_name', fa.class.name);
        pushNumeric('joined_classes_table_pk', fa.class.id);
    }
    if (fa.section && typeof fa.section === 'object') {
        push('joined_section_name', fa.section.name);
        pushNumeric('joined_sections_table_pk', fa.section.id);
    }
    if (fa.category && typeof fa.category === 'object') {
        push('joined_student_category_name', fa.category.name);
        pushNumeric('joined_student_categories_table_pk', fa.category.id);
    }
    if (fa.gender && typeof fa.gender === 'object') {
        push('joined_assign_gender_filter_name', fa.gender.name);
        pushNumeric('joined_genders_table_pk', fa.gender.id);
    }

    const group = fa.group && typeof fa.group === 'object' ? fa.group : fa.fees_group && typeof fa.fees_group === 'object' ? fa.fees_group : null;
    if (group) {
        push('joined_fees_group_label', group.name);
        pushNumeric('joined_fees_groups_table_pk', group.id);
    }

    rows.sort((a, b) => String(a.k).localeCompare(String(b.k)));
    return rows;
}

const FEES_MASTER_NESTED = new Set(['type', 'group', 'session', 'feesMasterChilds']);

function feesMasterDetailRows(fm) {
    if (!fm || typeof fm !== 'object') return [];
    const rows = scalarFieldEntries(fm, FEES_MASTER_NESTED);
    const push = (k, v) => {
        if (v != null && v !== '') rows.push({ k, v });
    };
    const pushNumeric = (k, v) => {
        if (v == null || v === '') return;
        rows.push({ k, v });
    };

    /** FK columns (session_id, fees_type_id, …) remain in scalarFieldEntries — add joined table payloads. */

    if (fm.session && typeof fm.session === 'object') {
        push('joined_master_session_title', fm.session.name ?? fm.session.title);
        push('joined_master_session_start', fm.session.start_date);
        push('joined_master_session_end', fm.session.end_date);
        pushNumeric('joined_master_sessions_pk', fm.session.id);
    }
    if (fm.type && typeof fm.type === 'object') {
        if (fm.type.name != null) push('joined_fees_types_name', fm.type.name);
        pushNumeric('joined_fees_types_pk', fm.type.id);
        const linkedClassNm = fm.type.school_class?.name ?? fm.type.schoolClass?.name ?? null;
        if (linkedClassNm != null) push('joined_fees_type_class_name', linkedClassNm);
    }
    if (fm.group && typeof fm.group === 'object') {
        if (fm.group.name != null) push('joined_fees_master_groups_name', fm.group.name);
        pushNumeric('joined_fees_master_groups_pk', fm.group.id);
    }

    rows.sort((a, b) => String(a.k).localeCompare(String(b.k)));
    return rows;
}

function humanizeMetaKey(key) {
    const k = String(key).replaceAll('_', ' ');
    return k.replace(/\b\w/g, (c) => c.toUpperCase());
}

/** Student-side fields joined on the assignment line (API snake_case after Laravel). */
function studentSnapshotRows(assign) {
    const s = assign?.student;
    if (!s || typeof s !== 'object') return [];
    const out = [];
    const push = (label, val) => {
        if (val === null || val === undefined || val === '') return;
        out.push({ label, value: typeof val === 'string' ? val : String(val) });
    };
    push('Admission no.', s.admission_no);
    push('Roll no.', s.roll_no);
    push('Student mobile', s.mobile);
    push('Student email', s.email);
    if (s.gender?.name) push('Gender', s.gender.name);
    if (s.student_category?.name) push('Student category', s.student_category.name);
    const p = s.parent;
    if (p && typeof p === 'object') {
        const gLine = [p.guardian_name, p.guardian_mobile].filter(Boolean).join(' · ');
        if (gLine) push('Guardian', gLine);
        const fLine = [p.father_name, p.father_mobile].filter(Boolean).join(' · ');
        if (!gLine && fLine) push('Father', fLine);
    }
    return out;
}

/** Enrolment for the same academic session as the fee assignment header. */
function enrollmentContextRows(assign) {
    const en = assign?.session_enrollment_for_assign;
    const fa = assign?.fees_assign ?? assign?.feesAssign;
    const out = [];
    const push = (label, value) => {
        if (value === null || value === undefined || value === '') return;
        out.push({ label, value: String(value) });
    };

    if (fa?.session?.name) push('Assignment session', fa.session.name);
    push('Assigned class', fa?.class?.name);
    push('Assigned section', fa?.section?.name);

    if (en && typeof en === 'object') {
        push('Enrollment class', en.class?.name);
        push('Enrollment section', en.section?.name);
        if (en.shift?.name) push('Shift', en.shift.name);
    }
    return out;
}

function AnimatedProgressSegment({ pct, className }) {
    const safe = Number.isFinite(pct) ? Math.min(100, Math.max(0, pct)) : 0;
    const [w, setW] = useState(0);

    useEffect(() => {
        const id = requestAnimationFrame(() => setW(safe));
        return () => cancelAnimationFrame(id);
    }, [safe]);

    return (
        <div className={`h-3 overflow-hidden rounded-full bg-white/25 ring-1 ring-white/20 ${className || ''}`}>
            <div
                className="h-full rounded-full bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-200 shadow-inner transition-[width] duration-[1200ms] ease-out motion-reduce:transition-none motion-reduce:duration-75"
                style={{ width: `${w}%` }}
            />
        </div>
    );
}

/** One payment recorded against this assignment line. */
function FeesCollectTimelineCard({ entry, index }) {
    const bk = entry?.bank_account;
    const by = entry?.collected_by;
    const amount = Number(entry?.amount ?? 0);
    const fine = Number(entry?.fine_amount ?? 0);

    return (
        <div
            className="relative rounded-2xl border border-white/70 bg-white/95 p-4 shadow-lg ring-1 ring-slate-200/80 backdrop-blur-sm"
            style={{ animationDelay: `${index * 80}ms` }}
        >
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment #{entry?.id ?? '—'}</p>
                    <p className="mt-1 text-sm font-medium text-slate-700">{formatDate(entry?.date)}</p>
                    {entry?.session?.name ? <p className="mt-1 text-xs text-slate-500">{entry.session.name}</p> : null}
                </div>
                <div className="text-right">
                    <p className="text-lg font-bold tracking-tight text-slate-900 tabular-nums">
                        <AnimatedMoney value={amount} durationMs={COUNT_UP_MS} />
                    </p>
                    {fine > 0 ? (
                        <p className="text-xs font-medium text-amber-800">
                            Fine <AnimatedMoney value={fine} durationMs={COUNT_UP_MS} />
                        </p>
                    ) : null}
                </div>
            </div>
            <div className="mt-3 grid gap-2 text-xs text-slate-600 sm:grid-cols-2">
                {bk?.bank_name ? (
                    <span>
                        <span className="font-semibold text-slate-500">Bank:</span> {bk.bank_name}
                        {bk.account_number ? ` · ${bk.account_number}` : ''}
                    </span>
                ) : null}
                {by?.name ? (
                    <span>
                        <span className="font-semibold text-slate-500">Received by:</span> {by.name}
                    </span>
                ) : null}
                {entry?.transaction_id ? (
                    <span className="sm:col-span-2 font-mono text-[11px] text-slate-500">Txn ref: {entry.transaction_id}</span>
                ) : null}
                {entry?.comments ? (
                    <span className="sm:col-span-2 text-slate-600">
                        <span className="font-semibold text-slate-500">Note:</span> {entry.comments}
                    </span>
                ) : null}
            </div>
        </div>
    );
}

export function FeesCollectionViewPage({ Layout }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [assign, setAssign] = useState(null);
    const [collectMeta, setCollectMeta] = useState(null);
    const [assignMissing, setAssignMissing] = useState(false);
    const [title, setTitle] = useState('Fees collection');
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deleting, setDeleting] = useState(false);

    useEffect(() => {
        if (!id) {
            setErr('Missing id.');
            setLoading(false);
            return;
        }
        setLoading(true);
        setErr('');
        axios
            .get(`/fees-collect/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const payload = r.data?.data;
                const meta = r.data?.meta || {};
                if (meta.title) setTitle(meta.title);
                setCollectMeta(meta.fees_collect || null);
                setAssignMissing(Boolean(meta.assign_missing));
                if (payload != null && typeof payload === 'object' && !Array.isArray(payload)) {
                    setAssign(payload);
                } else {
                    setAssign(null);
                    setErr('Unexpected response from server.');
                }
            })
            .catch((ex) => {
                const msg = ex.response?.data?.message || ex.message || 'Failed to load collection.';
                setErr(msg);
                setAssign(null);
                setCollectMeta(null);
                setAssignMissing(false);
            })
            .finally(() => setLoading(false));
    }, [id]);

    const extraAssign = assign ? assignLineScalarRows(assign) : [];
    const feesAssignHeader = assign ? assign.fees_assign ?? assign.feesAssign ?? null : null;
    const feesMasterRow = assign ? assign.fees_master ?? assign.feesMaster ?? null : null;
    const assignHeaderRows = feesAssignHeaderRows(feesAssignHeader);
    const masterRows = feesMasterDetailRows(feesMasterRow);
    const collectEntries =
        collectMeta && typeof collectMeta === 'object'
            ? Object.entries(collectMeta).filter(([key]) => String(key).toLowerCase() !== 'payment_method')
            : [];
    const paymentHistory = assign && Array.isArray(assign.fees_collects) ? assign.fees_collects : [];
    const showFlattenedCollectMeta = paymentHistory.length === 0 && collectEntries.length > 0;
    const feeAmtTotal = Number(assign?.fees_amount ?? 0);
    const paidAmtTotal = Number(assign?.paid_amount ?? 0);
    const paidRatioPct = feeAmtTotal > 0 ? (paidAmtTotal / feeAmtTotal) * 100 : paidAmtTotal > 0 ? 100 : 0;
    const studentSnapshots = assign ? studentSnapshotRows(assign) : [];
    const enrollmentLines = assign ? enrollmentContextRows(assign) : [];
    const collectStudentId = collectMeta?.student_id ?? assign?.student_id ?? null;

    const deleteLine = async () => {
        const assignId = assign?.id;
        if (!assignId) return;
        if (!window.confirm('Delete this fee line from collection workbench?')) return;
        setDeleting(true);
        setErr('');
        try {
            await axios.delete(`/fees-collect/deleteFees/${assignId}`, { headers: xhrJson });
            nav('/collections');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        } finally {
            setDeleting(false);
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-6xl px-4 py-5 sm:px-6 lg:py-6">
                {loading ? <FullPageLoader text="Loading details…" /> : null}
                {err ? (
                    <div className="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <span className="mt-0.5 inline-flex rounded-full bg-red-100 p-1 text-red-600" aria-hidden>
                            <IconX className="h-4 w-4" />
                        </span>
                        <span>{err}</span>
                    </div>
                ) : null}

                {!loading && !assign && err ? (
                    <div className="flex justify-end">
                        <UiButtonLink to="/collections" variant="secondary">
                            Back to list
                        </UiButtonLink>
                    </div>
                ) : null}

                {!loading && assign ? (
                    <div className="space-y-6">
                        {/* Fee assignment line — primary overview (top) */}
                        <div className="overflow-hidden rounded-3xl border border-slate-200/80 bg-gradient-to-br from-indigo-700 via-blue-800 to-slate-900 text-white shadow-2xl ring-1 ring-white/10">
                            <div className="border-b border-white/10 px-5 py-5 sm:px-8 sm:py-7">
                                <div className="flex flex-wrap items-start justify-between gap-4">
                                    <div className="min-w-0 flex-1">
                                        <p className="text-[11px] font-bold uppercase tracking-[0.35em] text-indigo-200/90">
                                            Fee assignment line
                                        </p>
                                        <h1 className="mt-1 truncate text-2xl font-bold tracking-tight sm:text-3xl">{studentLabel(assign)}</h1>
                                        <p className="mt-1 text-sm font-medium text-indigo-100/95">{feeName(assign)}</p>
                                        <div className="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-indigo-200/90">
                                            <span className="inline-flex items-center gap-1.5 tabular-nums">
                                                <IconHash className="h-3.5 w-3.5 shrink-0" aria-hidden />
                                                Line #{assign?.id ?? '—'}
                                            </span>
                                            <span className="hidden sm:inline text-indigo-400/80" aria-hidden>
                                                ·
                                            </span>
                                            <span className="tabular-nums">
                                                View URL <span className="font-semibold text-white">#{id}</span>
                                            </span>
                                            {title ? (
                                                <>
                                                    <span className="hidden sm:inline text-indigo-400/80" aria-hidden>
                                                        ·
                                                    </span>
                                                    <span className="max-w-full truncate opacity-90">{title}</span>
                                                </>
                                            ) : null}
                                        </div>
                                    </div>
                                    <span className="inline-flex shrink-0 items-center gap-2 rounded-full bg-emerald-400/20 px-4 py-2 text-xs font-bold uppercase tracking-wide text-emerald-50 ring-2 ring-emerald-300/40">
                                        <IconClipboardCheck className="h-4 w-4" aria-hidden />
                                        Student fee assignment
                                    </span>
                                </div>
                                {assignMissing ? (
                                    <div className="mt-4 flex items-start gap-2 rounded-xl border border-amber-400/40 bg-amber-500/15 px-4 py-3 text-sm text-amber-50 backdrop-blur-sm">
                                        <IconList className="mt-0.5 h-4 w-4 shrink-0" aria-hidden />
                                        <span>
                                            The assignment record is missing or was removed; totals may be incomplete.
                                            Payment receipts are shown below when stored.
                                        </span>
                                    </div>
                                ) : null}
                            </div>

                            <div className="border-b border-white/10 bg-black/15 px-5 py-5 sm:px-8">
                                <div className="grid gap-4 sm:grid-cols-3">
                                    <div className="rounded-2xl bg-white/10 p-4 shadow-inner ring-1 ring-white/15 backdrop-blur-md">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-indigo-200">Fee amount</p>
                                        <p className="mt-2 text-2xl font-bold tabular-nums text-white">
                                            <AnimatedMoney value={assign.fees_amount} durationMs={COUNT_UP_MS} />
                                        </p>
                                    </div>
                                    <div className="rounded-2xl bg-emerald-500/20 p-4 shadow-inner ring-1 ring-emerald-300/35 backdrop-blur-md">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-emerald-100">Paid total</p>
                                        <p className="mt-2 text-2xl font-bold tabular-nums text-emerald-50">
                                            <AnimatedMoney value={assign.paid_amount} durationMs={COUNT_UP_MS} />
                                        </p>
                                    </div>
                                    <div className="rounded-2xl bg-amber-500/15 p-4 shadow-inner ring-1 ring-amber-300/30 backdrop-blur-md">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-amber-100">Outstanding</p>
                                        <p className="mt-2 text-2xl font-bold tabular-nums text-amber-50">
                                            <AnimatedMoney value={assign.remained_amount} durationMs={COUNT_UP_MS} />
                                        </p>
                                    </div>
                                </div>
                                <div className="mt-5">
                                    <div className="mb-2 flex justify-between text-xs font-semibold text-indigo-50/90">
                                        <span>Settled vs fee</span>
                                        <span className="tabular-nums">{Math.round(paidRatioPct)}%</span>
                                    </div>
                                    <AnimatedProgressSegment pct={paidRatioPct} />
                                </div>
                            </div>

                            <div className="bg-slate-950/40 px-5 py-6 sm:px-8">
                                {studentSnapshots.length > 0 || enrollmentLines.length > 0 ? (
                                    <div className="grid gap-4 lg:grid-cols-2">
                                        {studentSnapshots.length > 0 ? (
                                            <div className="rounded-2xl border border-white/15 bg-white/98 p-4 text-slate-900 shadow-lg">
                                                <h3 className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Student profile</h3>
                                                <p className="mt-1 text-[11px] text-slate-400">Joined with gender, category, guardians</p>
                                                <dl className="mt-4 space-y-2.5 text-sm">
                                                    {studentSnapshots.map((row) => (
                                                        <div
                                                            key={row.label}
                                                            className="flex justify-between gap-3 border-b border-slate-100 pb-2.5 last:border-0 last:pb-0"
                                                        >
                                                            <dt className="text-slate-500">{row.label}</dt>
                                                            <dd className="max-w-[58%] text-right font-semibold leading-snug text-slate-900">
                                                                {row.value}
                                                            </dd>
                                                        </div>
                                                    ))}
                                                </dl>
                                            </div>
                                        ) : null}
                                        {enrollmentLines.length > 0 ? (
                                            <div className="rounded-2xl border border-white/15 bg-white/98 p-4 text-slate-900 shadow-lg">
                                                <h3 className="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">
                                                    Session & placement
                                                </h3>
                                                <p className="mt-1 text-[11px] text-slate-400">
                                                    Assignment scope + enrolment row for same session (when available)
                                                </p>
                                                <dl className="mt-4 space-y-2.5 text-sm">
                                                    {enrollmentLines.map((row) => (
                                                        <div
                                                            key={row.label}
                                                            className="flex justify-between gap-3 border-b border-slate-100 pb-2.5 last:border-0 last:pb-0"
                                                        >
                                                            <dt className="text-slate-500">{row.label}</dt>
                                                            <dd className="max-w-[58%] text-right font-semibold leading-snug text-slate-900">
                                                                {row.value}
                                                            </dd>
                                                        </div>
                                                    ))}
                                                </dl>
                                            </div>
                                        ) : null}
                                    </div>
                                ) : null}
                                {assign.comment != null && assign.comment !== '' ? (
                                    <div className="mt-4 rounded-2xl border border-indigo-300/25 bg-indigo-950/50 p-4 text-sm text-indigo-50">
                                        <span className="text-xs font-bold uppercase tracking-wider text-indigo-200">Line comment</span>
                                        <p className="mt-2 leading-relaxed">{assign.comment}</p>
                                    </div>
                                ) : null}
                            </div>
                        </div>

                        {paymentHistory.length > 0 ? (
                            <SectionCard
                                icon={IconReceipt}
                                title="Payments on this assignment"
                                subtitle="Each fees_collect row (bank account, cashier, session, fine, transaction id)."
                                iconWrapClass="bg-gradient-to-br from-emerald-600 to-teal-700"
                            >
                                <div className="relative space-y-4 pl-6 before:absolute before:inset-y-1 before:left-2 before:w-px before:bg-gradient-to-b before:from-emerald-300 before:via-slate-200 before:to-transparent">
                                    {paymentHistory.map((row, idx) => (
                                        <FeesCollectTimelineCard key={row.id ?? idx} entry={row} index={idx} />
                                    ))}
                                </div>
                            </SectionCard>
                        ) : showFlattenedCollectMeta ? (
                            <SectionCard
                                icon={IconReceipt}
                                title="Collection transaction"
                                subtitle="Primary payment record (joined bank, collector, session names where available)"
                                iconWrapClass="bg-gradient-to-br from-emerald-500 to-teal-600"
                            >
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {collectEntries.map(([key, val], i) => {
                                        const Ic = detailIconForKey(key);
                                        return (
                                            <DetailRow
                                                key={key}
                                                icon={Ic}
                                                label={humanizeMetaKey(key)}
                                                iconClass={iconClassForPayKeyAt(i)}
                                            >
                                                {formatScalarField(key, val)}
                                            </DetailRow>
                                        );
                                    })}
                                </div>
                            </SectionCard>
                        ) : null}

                        {assignHeaderRows.length > 0 ? (
                            <SectionCard
                                icon={IconHash}
                                title="Fees assignment header"
                                subtitle="INNER JOIN fees_assigns → sessions, classes, sections, student_categories, genders & fees_groups (plus raw header columns)"
                                iconWrapClass="bg-gradient-to-br from-violet-600 to-indigo-700"
                            >
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {assignHeaderRows.map(({ k, v }, i) => {
                                        const Ic = detailIconForKey(k);
                                        return (
                                            <DetailRow
                                                key={k}
                                                icon={Ic}
                                                label={humanizeMetaKey(k)}
                                                iconClass={iconClassForPayKeyAt(i)}
                                            >
                                                {formatScalarField(k, v)}
                                            </DetailRow>
                                        );
                                    })}
                                </div>
                            </SectionCard>
                        ) : null}

                        {masterRows.length > 0 ? (
                            <SectionCard
                                icon={IconTag}
                                title="Fee master definition"
                                subtitle="INNER JOIN fees_masters → sessions, fees_types (and linked class where set), fees_groups (+ master row scalars)"
                                iconWrapClass="bg-gradient-to-br from-teal-600 to-cyan-700"
                            >
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {masterRows.map(({ k, v }, i) => {
                                        const Ic = detailIconForKey(k);
                                        return (
                                            <DetailRow
                                                key={k}
                                                icon={Ic}
                                                label={humanizeMetaKey(k)}
                                                iconClass={iconClassForPayKeyAt(i + 1)}
                                            >
                                                {formatScalarField(k, v)}
                                            </DetailRow>
                                        );
                                    })}
                                </div>
                            </SectionCard>
                        ) : null}

                        {extraAssign.length > 0 ? (
                            <SectionCard
                                icon={IconList}
                                title="Additional line fields"
                                subtitle="All scalar columns on fees_assign_childrens line (quarters, collect_status, balances, timestamps, …)"
                                iconWrapClass="bg-gradient-to-br from-slate-600 to-slate-800"
                            >
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {extraAssign.map(({ k, v }, i) => {
                                        const Ic = detailIconForKey(k);
                                        return (
                                            <DetailRow
                                                key={k}
                                                icon={Ic}
                                                label={humanizeMetaKey(k)}
                                                iconClass={iconClassForPayKeyAt(i + 2)}
                                            >
                                                {formatScalarField(k, v)}
                                            </DetailRow>
                                        );
                                    })}
                                </div>
                            </SectionCard>
                        ) : null}

                        <div className="flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 pt-4">
                            <UiButtonLink to="/collections" variant="secondary">
                                Back to list
                            </UiButtonLink>
                            <UiButtonLink to={`/collections/${id}/edit`} variant="primary" leftIcon={<IconEdit />}>
                                Edit
                            </UiButtonLink>
                            <button
                                type="button"
                                disabled={deleting || !assign?.id}
                                onClick={deleteLine}
                                className="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-white px-3.5 py-2 text-sm font-medium text-rose-700 shadow-sm transition hover:bg-rose-50 disabled:opacity-50"
                            >
                                <IconTrash className="h-4 w-4" aria-hidden />
                                {deleting ? 'Deleting…' : 'Delete'}
                            </button>
                            {collectStudentId ? (
                                <Link
                                    to={`/collections/collect/${collectStudentId}`}
                                    className="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-medium text-emerald-900 shadow-sm transition hover:bg-emerald-100"
                                >
                                    <IconBanknote className="h-4 w-4" aria-hidden />
                                    Collect fees
                                </Link>
                            ) : null}
                            {collectStudentId ? (
                                <a
                                    href={`/fees-collect/printReceipt/${collectStudentId}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                                >
                                    <IconReceipt className="h-4 w-4 text-emerald-600" aria-hidden />
                                    Print receipt (PDF)
                                </a>
                            ) : null}
                        </div>
                    </div>
                ) : null}
            </div>
        </Layout>
    );
}
