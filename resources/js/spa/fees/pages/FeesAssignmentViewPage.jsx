import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    IconCalendar,
    IconEdit,
    IconList,
    IconTag,
    IconUsers,
    UiButtonLink,
    UiHeadRow,
    UiPageLoader,
    UiTable,
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

function IconConnect({ className = 'h-5 w-5' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 3h-3L8 7l4 3 4-3-2.5-4z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 7v12.5A2.5 2.5 0 0010.5 22h3A2.5 2.5 0 0016 19.5V7" />
        </svg>
    );
}

function pickName(value) {
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

function studentDisplayName(s) {
    if (!s) return '—';
    const n = `${s.first_name || ''} ${s.last_name || ''}`.trim();
    return n || `Student #${s.id ?? ''}`;
}

function InfoRow({ icon, label, value }) {
    return (
        <div className="flex gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-600">{icon}</div>
            <div className="min-w-0">
                <p className="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{label}</p>
                <p className="mt-0.5 text-sm font-medium text-slate-900">{value}</p>
            </div>
        </div>
    );
}

export function FeesAssignmentViewPage({ Layout }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [masters, setMasters] = useState([]);
    const [mastersLoading, setMastersLoading] = useState(false);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(`/fees-assign/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const raw = r.data?.data;
                if (raw != null && typeof raw === 'object' && !Array.isArray(raw)) {
                    setData(raw);
                } else {
                    setData(null);
                    setErr('Unexpected response from server.');
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load assignment.'))
            .finally(() => setLoading(false));
    }, [id]);

    const groupId = data?.fees_group_id ?? data?.group?.id;

    useEffect(() => {
        if (!groupId) {
            setMasters([]);
            return;
        }
        let cancelled = false;
        setMastersLoading(true);
        axios
            .get('/fees-assign/get-all-type', { headers: xhrJson, params: { id: groupId } })
            .then((r) => {
                if (!cancelled) {
                    setMasters(Array.isArray(r.data?.data) ? r.data.data : []);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setMasters([]);
                }
            })
            .finally(() => {
                if (!cancelled) {
                    setMastersLoading(false);
                }
            });
        return () => {
            cancelled = true;
        };
    }, [groupId]);

    const childRows = useMemo(() => (Array.isArray(data?.fees_assign_childs) ? data.fees_assign_childs : []), [data]);
    const childTotal = data?.fees_assign_childs_count ?? childRows.length;

    /**
     * Same structure as edit: one block per fee master in the fees group (from get-all-type), with assignment lines nested under each.
     * Falls back to “only masters that appear in child rows” if the master list endpoint fails.
     */
    const blocksByFeeType = useMemo(() => {
        const rowsByMid = new Map();

        childRows.forEach((ch, i) => {
            const fm = ch.fees_master || ch.feesMaster || {};
            const midRaw = fm.id ?? ch.fees_master_id ?? ch.fees_masterid;
            const midNum = typeof midRaw === 'number' && Number.isFinite(midRaw) ? midRaw : Number(midRaw);
            const midKey = Number.isFinite(midNum) && midNum > 0 ? String(midNum) : `_row_${i}`;
            const label = pickName(fm?.type) || `Fee master ${midKey}`;
            if (!rowsByMid.has(midKey)) {
                rowsByMid.set(midKey, { rows: [], label, amountSample: fm.amount ?? null });
            }
            rowsByMid.get(midKey).rows.push(ch);
        });

        const masterFeeTypeLabel = (m) => pickName(m?.type) || `Fee master #${m?.id ?? '?'}`;

        if (masters.length > 0) {
            return masters.map((m) => {
                const k = String(m.id);
                const bucket = rowsByMid.get(k);
                const rows = bucket?.rows ?? [];
                return {
                    key: k,
                    label: masterFeeTypeLabel(m),
                    amountSample: m?.amount ?? bucket?.amountSample ?? null,
                    rows,
                    masterId: m.id,
                };
            });
        }

        return [...rowsByMid.entries()].map(([key, bucket]) => ({
            key,
            label: bucket.label,
            amountSample: bucket.amountSample ?? null,
            rows: bucket.rows,
            masterId: key.startsWith('_row_') ? null : Number(key),
        }));
    }, [childRows, masters]);

    const uniqueAssignedStudents = useMemo(() => {
        const s = new Set();
        childRows.forEach((ch) => {
            const sid = ch.student?.id ?? ch.student_id;
            if (sid != null) {
                s.add(Number(sid));
            }
        });
        return s.size;
    }, [childRows]);

    return (
        <Layout>
            <div className="mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-1.5 sm:px-6 sm:py-2 lg:px-8 lg:py-2.5">
                {loading ? <UiPageLoader text="Loading assignment…" /> : null}

                {!loading && err ? <div className="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{err}</div> : null}

                {!loading && !err && data ? (
                    <>
                        <div className="relative overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-700 via-cyan-800 to-slate-900 p-6 text-white shadow-xl sm:p-8">
                            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(600px_at_10%_0%,rgba(255,255,255,0.12),transparent)]" aria-hidden />
                            <div className="relative flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                <div className="flex min-w-0 gap-4">
                                    <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                                        <IconConnect className="h-9 w-9" />
                                    </div>
                                    <div className="min-w-0">
                                        <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-teal-100/90">Fees assignment</p>
                                        <h1 className="mt-2 break-words text-2xl font-bold tracking-tight sm:text-3xl">
                                            {pickName(data.group) || 'Fees group'}
                                            <span className="mx-2 text-teal-200/80">·</span>
                                            {pickName(data.class) || 'Class'}
                                        </h1>
                                        <p className="mt-2 max-w-2xl text-sm text-teal-100/90">
                                            Scoped details and then one block per fee type in this fees group, matching Edit.
                                            <span className="mt-1 block text-teal-100/80">{uniqueAssignedStudents} distinct student(s) with lines on this assignment.</span>
                                        </p>
                                    </div>
                                </div>
                                <div className="flex shrink-0 flex-wrap gap-2">
                                    <UiButtonLink
                                        to="/assignments"
                                        variant="secondary"
                                        className="border-white/40 bg-white/10 text-white shadow-none hover:bg-white/20"
                                        leftIcon={<IconChevronLeft />}
                                    >
                                        All assignments
                                    </UiButtonLink>
                                    <UiButtonLink
                                        to={`/assignments/${id}/edit`}
                                        variant="secondary"
                                        className="border-transparent bg-white text-teal-900 shadow-md hover:bg-teal-50"
                                        leftIcon={<IconEdit className="h-4 w-4" />}
                                    >
                                        Edit
                                    </UiButtonLink>
                                </div>
                            </div>
                            <div className="relative mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div className="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <p className="text-xs font-medium text-teal-100">Assignment ID</p>
                                    <p className="text-xl font-bold tabular-nums">#{data.id}</p>
                                </div>
                                <div className="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <p className="text-xs font-medium text-teal-100">Session</p>
                                    <p className="truncate text-sm font-semibold">{pickName(data.session) || '—'}</p>
                                </div>
                                <div className="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <p className="text-xs font-medium text-teal-100">Student links</p>
                                    <p className="text-xl font-bold tabular-nums">{childTotal}</p>
                                </div>
                                <div className="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/20 backdrop-blur">
                                    <p className="text-xs font-medium text-teal-100">Last updated</p>
                                    <p className="text-sm font-medium leading-tight">{formatDateTime(data.updated_at)}</p>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <InfoRow
                                icon={<IconList className="h-5 w-5" />}
                                label="Fees group"
                                value={pickName(data.group) || '—'}
                            />
                            <InfoRow
                                icon={<IconUsers className="h-5 w-5" />}
                                label="Class"
                                value={pickName(data.class) || '—'}
                            />
                            <InfoRow
                                icon={<IconTag className="h-5 w-5" />}
                                label="Section"
                                value={pickName(data.section) || '—'}
                            />
                            <InfoRow
                                icon={<IconCalendar className="h-5 w-5" />}
                                label="Session"
                                value={pickName(data.session) || '—'}
                            />
                            <InfoRow
                                icon={<IconTag className="h-5 w-5" />}
                                label="Student category"
                                value={pickName(data.category) || '—'}
                            />
                            <InfoRow
                                icon={<IconTag className="h-5 w-5" />}
                                label="Gender filter"
                                value={pickName(data.gender) || '—'}
                            />
                        </div>

                        <div className="mt-8 flex flex-wrap gap-4 text-sm text-slate-600">
                            <span className="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5">
                                <IconCalendar className="h-4 w-4 text-slate-500" />
                                <span className="font-medium text-slate-700">Created</span> {formatDateTime(data.created_at)}
                            </span>
                            <span className="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5">
                                <IconCalendar className="h-4 w-4 text-slate-500" />
                                <span className="font-medium text-slate-700">Updated</span> {formatDateTime(data.updated_at)}
                            </span>
                        </div>

                        <section className="mt-8 space-y-5">
                            <div className="rounded-xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 text-xs leading-relaxed text-indigo-950">
                                <p>
                                    <span className="font-semibold">Matches Edit layout</span> — every active fee master in this fees group is listed as its own card.
                                    In this assignment, each student appears under at most one fee type (same validation as Edit).
                                </p>
                                <p className="mt-2 font-medium text-indigo-900">{childTotal} line(s) loaded · {uniqueAssignedStudents} distinct student(s)</p>
                            </div>
                            <div className="px-1">
                                <h2 className="text-sm font-semibold text-slate-900">Fee types &amp; assigned students</h2>
                                <p className="mt-1 text-xs text-slate-600">
                                    {masters.length > 0
                                        ? 'Blocks follow the configured fee masters for this fees group.'
                                        : 'Fee master list unavailable; blocks are built only from assignment lines loaded on this assignment.'}
                                </p>
                            </div>
                            {mastersLoading ? (
                                <p className="px-1 text-sm text-slate-500">Loading fee types for this fees group…</p>
                            ) : null}
                            {blocksByFeeType.length === 0 ? (
                                <div className="rounded-2xl border border-slate-200 bg-white px-6 py-10 text-center text-sm text-slate-600 shadow-sm">
                                    No fee masters found and no assignment lines yet. Use Edit to assign students to fee types.
                                </div>
                            ) : (
                                blocksByFeeType.map((block) => (
                                    <div key={String(block.key)} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                                        <div className="mb-3 border-b border-slate-100 pb-3">
                                            <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Fee type</p>
                                            <h3 className="text-base font-semibold text-slate-900">{block.label}</h3>
                                            <p className="mt-1 font-mono text-xs text-slate-500">
                                                Master #{block.masterId ?? block.key}
                                                {block.amountSample != null ? (
                                                    <>
                                                        <span className="mx-2 text-slate-300">·</span>
                                                        Amount <span className="tabular-nums text-slate-700">{formatMoney(block.amountSample)}</span>
                                                    </>
                                                ) : null}
                                            </p>
                                            <span className="mt-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                                {block.rows.length} assigned student{block.rows.length === 1 ? '' : 's'}
                                            </span>
                                        </div>
                                        <UiTableWrap className="rounded-xl border border-slate-100">
                                            <UiTable>
                                                <UiTHead>
                                                    <UiHeadRow className="bg-slate-100">
                                                        <UiTH className="text-xs font-semibold uppercase text-slate-600">#</UiTH>
                                                        <UiTH className="text-xs font-semibold uppercase text-slate-600">Student</UiTH>
                                                        <UiTH className="text-xs font-semibold uppercase text-slate-600">Admission</UiTH>
                                                        <UiTH className="text-xs font-semibold uppercase text-slate-600">Control #</UiTH>
                                                        <UiTH className="text-right text-xs font-semibold uppercase text-slate-600">Assigned amount</UiTH>
                                                    </UiHeadRow>
                                                </UiTHead>
                                                <UiTBody>
                                                    {block.rows.length === 0 ? (
                                                        <UiTR>
                                                            <UiTD colSpan={5} className="py-6 text-center text-sm text-slate-500">
                                                                No students assigned to this fee type on this assignment.
                                                            </UiTD>
                                                        </UiTR>
                                                    ) : (
                                                        block.rows.map((ch, i) => (
                                                            <UiTR key={ch.id != null ? ch.id : `${block.key}-${i}`}>
                                                                <UiTD className="whitespace-nowrap text-xs text-slate-500 tabular-nums">#{i + 1}</UiTD>
                                                                <UiTD className="font-medium text-slate-900">{studentDisplayName(ch.student)}</UiTD>
                                                                <UiTD className="text-slate-700">{ch.student?.admission_no || '—'}</UiTD>
                                                                <UiTD className="font-mono text-xs text-slate-600">{ch.control_number || '—'}</UiTD>
                                                                <UiTD className="text-right tabular-nums font-medium text-slate-900">
                                                                    {formatMoney(ch.fees_amount ?? ch.fees_master?.amount ?? ch.feesMaster?.amount)}
                                                                </UiTD>
                                                            </UiTR>
                                                        ))
                                                    )}
                                                </UiTBody>
                                            </UiTable>
                                        </UiTableWrap>
                                    </div>
                                ))
                            )}
                        </section>

                        <p className="mt-4 text-center text-xs text-slate-500">
                            <Link to="/assignments" className="text-indigo-600 hover:underline">
                                Back to assignments
                            </Link>
                        </p>
                    </>
                ) : null}
            </div>
        </Layout>
    );
}
