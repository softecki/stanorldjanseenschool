import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, studentsTableClass } from '../FeesModuleShared';
import {
    IconBanknote,
    IconClipboardCheck,
    IconHash,
    IconReceipt,
    IconUser,
    UiButtonLink,
    UiHeadRow,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../ui/UiKit';

function money(n) {
    if (n == null || n === '') return '—';
    const x = Number(n);
    if (!Number.isFinite(x)) return String(n);
    return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function studentName(s) {
    if (!s) return '—';
    const t = `${s.first_name || ''} ${s.last_name || ''}`.trim();
    return t || '—';
}

function lineGroup(item) {
    return item?.fees_master?.group?.name || item?.feesMaster?.group?.name || '—';
}

function lineType(item) {
    return item?.fees_master?.type?.name || item?.feesMaster?.type?.name || '—';
}

/** fees_masters.due_date (API uses snake_case) */
function masterDueDate(line) {
    return line?.fees_master?.due_date ?? line?.feesMaster?.due_date ?? null;
}

function masterFineAmount(line) {
    const v = line?.fees_master?.fine_amount ?? line?.feesMaster?.fine_amount;
    return v == null ? 0 : Number(v);
}

function toNumber(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
}

function todayDate() {
    return new Date().toISOString().slice(0, 10);
}

export function FeesCollectionCollectPage({ Layout }) {
    const { studentId } = useParams();
    const [meta, setMeta] = useState({});
    const [student, setStudent] = useState(null);
    const [lines, setLines] = useState([]);
    const [err, setErr] = useState('');
    const [success, setSuccess] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [selectedFeeId, setSelectedFeeId] = useState('');
    const [form, setForm] = useState({
        date: todayDate(),
        payment_method: '1',
        amounts: '',
        account_id: '',
        comment: '',
        status: '1',
    });

    const loadCollectData = useCallback(() => {
        if (!studentId) {
            setErr('Missing student id.');
            setLoading(false);
            return Promise.resolve();
        }
        setLoading(true);
        setErr('');
        return axios
            .get(`/fees-collect/collect/${studentId}`, { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta(m);
                const d = r.data?.data;
                setStudent(d?.student ?? null);
                const raw = d?.fees_assigned;
                const list = Array.isArray(raw) ? raw : raw != null && typeof raw === 'object' ? Object.values(raw) : [];
                setLines(list);
                const firstUnpaid = list.find((row) => toNumber(row?.remained_amount) > 0) || list[0];
                const firstPaymentMethod = m?.payment_methods?.[0]?.value;
                setSelectedFeeId((cur) => cur || (firstUnpaid?.id != null ? String(firstUnpaid.id) : ''));
                setForm((cur) => ({
                    ...cur,
                    payment_method: cur.payment_method || firstPaymentMethod || '1',
                    amounts: cur.amounts || (firstUnpaid ? String(toNumber(firstUnpaid.remained_amount)) : ''),
                }));
            })
            .catch((ex) => {
                setErr(ex.response?.data?.message || ex.message || 'Failed to load collect data.');
                setStudent(null);
                setLines([]);
            })
            .finally(() => setLoading(false));
    }, [studentId]);

    useEffect(() => {
        loadCollectData();
    }, [loadCollectData]);

    const selectedLine = useMemo(
        () => lines.find((row) => String(row.id) === String(selectedFeeId)) || null,
        [lines, selectedFeeId],
    );

    const accountOptions = meta.accounts || [];
    const paymentMethods = meta.payment_methods?.length ? meta.payment_methods : [{ value: '1', label: 'Cash' }];
    const totalPaid = useMemo(() => lines.reduce((sum, row) => sum + toNumber(row.paid_amount), 0), [lines]);
    const totalRemaining = useMemo(() => lines.reduce((sum, row) => sum + toNumber(row.remained_amount), 0), [lines]);

    const setField = (key, value) => {
        setForm((cur) => ({ ...cur, [key]: value }));
    };

    const selectLine = (row) => {
        setSelectedFeeId(String(row.id));
        setForm((cur) => ({ ...cur, amounts: String(toNumber(row.remained_amount)) }));
    };

    const submitPayment = async (event) => {
        event.preventDefault();
        setErr('');
        setSuccess('');
        if (!selectedFeeId) {
            setErr('Select a fee line before collecting payment.');
            return;
        }
        if (!form.date || !form.amounts || toNumber(form.amounts) <= 0 || !form.account_id) {
            setErr('Fill date, amount, and account number before submitting.');
            return;
        }
        const due = masterDueDate(selectedLine);
        const fineAmount = due && form.date > due ? masterFineAmount(selectedLine) : 0;
        setSaving(true);
        try {
            const payload = {
                student_id: studentId,
                date: form.date,
                payment_method: form.payment_method,
                amounts: form.amounts,
                account_id: form.account_id,
                comment: form.comment,
                status: form.status,
                fees_assign_childrens: [Number(selectedFeeId)],
                fine_amounts: [fineAmount],
            };
            const res = await axios.post('/fees-collect/store', payload, { headers: xhrJson });
            setSuccess(res.data?.message || 'Payment collected successfully.');
            setSelectedFeeId('');
            setForm((cur) => ({ ...cur, amounts: '', comment: '' }));
            await loadCollectData();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to collect payment.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5">
                <div className="mb-4 flex justify-end">
                    <UiButtonLink to="/collections" variant="secondary" leftIcon={<IconReceipt className="h-4 w-4 text-slate-500" />}>
                        Back to collect list
                    </UiButtonLink>
                </div>

                {err ? (
                    <div className="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {loading ? <FullPageLoader text="Loading student…" /> : null}

                {!loading && student && !err ? (
                    <div className="space-y-6">
                        <div className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm sm:p-5">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                                    <IconUser className="h-5 w-5" aria-hidden />
                                </div>
                                <div>
                                    <h2 className="text-sm font-semibold text-slate-900">Student details</h2>
                                    <p className="text-xs text-slate-500">Current student attached to this collection.</p>
                                </div>
                            </div>
                            <dl className="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                <div className="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
                                    <IconUser className="h-5 w-5 shrink-0 text-cyan-700" aria-hidden />
                                    <div className="min-w-0">
                                        <dt className="text-xs font-medium uppercase tracking-wide text-slate-500">Name</dt>
                                        <dd className="truncate font-semibold text-slate-900">{studentName(student)}</dd>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
                                    <IconHash className="h-5 w-5 shrink-0 text-indigo-700" aria-hidden />
                                    <div className="min-w-0">
                                        <dt className="text-xs font-medium uppercase tracking-wide text-slate-500">Admission no.</dt>
                                        <dd className="font-semibold text-slate-900">{student.admission_no ?? '—'}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>

                        <div className={studentsTableClass() + ' !shadow-[0_1px_3px_rgba(15,23,42,0.06)]'}>
                            <UiTableWrap>
                                <UiTable>
                                    <UiTHead>
                                        <UiHeadRow className="bg-slate-100">
                                            <UiTH className="text-xs font-semibold uppercase tracking-wide text-slate-600">Group</UiTH>
                                            <UiTH className="text-xs font-semibold uppercase tracking-wide text-slate-600">Fee type</UiTH>
                                            <UiTH className="text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Fees amount</UiTH>
                                            <UiTH className="text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Paid</UiTH>
                                            <UiTH className="text-right text-xs font-semibold uppercase tracking-wide text-slate-600">Remained</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {lines.length === 0 ? (
                                            <UiTableEmptyRow colSpan={5} message="No fee lines for this student." />
                                        ) : (
                                            lines.map((row) => (
                                                <UiTR key={row.id}>
                                                    <UiTD className="align-middle font-medium text-slate-900">{lineGroup(row)}</UiTD>
                                                    <UiTD className="align-middle text-slate-700">{lineType(row)}</UiTD>
                                                    <UiTD className="align-middle text-right font-medium tabular-nums text-slate-800">{money(row.fees_amount)}</UiTD>
                                                    <UiTD className="align-middle text-right tabular-nums text-emerald-700">{money(row.paid_amount)}</UiTD>
                                                    <UiTD className="align-middle text-right font-semibold tabular-nums text-amber-800">
                                                        <span className="inline-flex min-w-[6.5rem] justify-end rounded-full bg-amber-50 px-2.5 py-1">
                                                            {money(row.remained_amount)}
                                                        </span>
                                                    </UiTD>
                                                </UiTR>
                                            ))
                                        )}
                                    </UiTBody>
                                </UiTable>
                            </UiTableWrap>
                        </div>

                        <section
                            className="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-[0_1px_3px_rgba(15,23,42,0.06),0_4px_12px_rgba(15,23,42,0.04)] ring-1 ring-slate-900/[0.04]"
                            aria-label="Collection form"
                        >
                            <div className="relative overflow-hidden border-b border-cyan-100 bg-gradient-to-br from-cyan-700 via-teal-700 to-emerald-700 px-4 py-5 text-white sm:px-6">
                                <div className="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/10" aria-hidden />
                                <div className="pointer-events-none absolute bottom-0 right-16 h-24 w-24 rounded-full bg-cyan-200/10" aria-hidden />

                                <div className="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                                    <div className="flex min-w-0 gap-4">
                                        <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-sm ring-1 ring-white/20">
                                            <IconBanknote className="h-6 w-6" aria-hidden />
                                        </div>
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h2 className="text-lg font-bold tracking-tight sm:text-xl">Payment workspace</h2>
                                                <span className="inline-flex rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-cyan-50 ring-1 ring-white/20">
                                                    Collection form
                                                </span>
                                            </div>
                                            <p className="mt-1 max-w-2xl text-sm leading-relaxed text-cyan-50/90">
                                                Record payment details, choose the bank account, and complete the fee collection for
                                                <span className="font-semibold text-white"> {studentName(student)}</span>.
                                            </p>
                                            <div className="mt-3 flex flex-wrap gap-2 text-xs font-medium">
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 ring-1 ring-white/20">
                                                    <span className="h-1.5 w-1.5 rounded-full bg-emerald-200" aria-hidden />
                                                    Student #{studentId}
                                                </span>
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 ring-1 ring-white/20">
                                                    <IconClipboardCheck className="h-3.5 w-3.5" aria-hidden />
                                                    {lines.length} fee line{lines.length === 1 ? '' : 's'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-2 rounded-2xl bg-white/10 p-3 ring-1 ring-white/15 lg:min-w-[18rem]">
                                        <div>
                                            <p className="text-xs text-cyan-50/75">Paid</p>
                                            <p className="text-lg font-bold tabular-nums">{money(totalPaid)}</p>
                                        </div>
                                        <div>
                                            <p className="text-xs text-cyan-50/75">Remaining</p>
                                            <p className="text-lg font-bold tabular-nums">{money(totalRemaining)}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form onSubmit={submitPayment} className="space-y-5 p-4 sm:p-5">
                                {success ? (
                                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                                        {success}
                                    </div>
                                ) : null}

                                <div>
                                    <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <h3 className="text-sm font-semibold text-slate-900">Select fee line</h3>
                                            <p className="text-xs text-slate-500">Only one line is selected at a time. Use “Apply” to decide how the amount is allocated.</p>
                                        </div>
                                        {selectedLine ? (
                                            <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">
                                                Selected remained: {money(selectedLine.remained_amount)}
                                            </span>
                                        ) : null}
                                    </div>
                                    <div className="grid gap-3 lg:grid-cols-2">
                                        {lines.map((row) => {
                                            const active = String(row.id) === String(selectedFeeId);
                                            return (
                                                <button
                                                    key={row.id}
                                                    type="button"
                                                    onClick={() => selectLine(row)}
                                                    className={`rounded-2xl border p-4 text-left transition ${
                                                        active
                                                            ? 'border-cyan-500 bg-cyan-50 ring-2 ring-cyan-100'
                                                            : 'border-slate-200 bg-white hover:border-cyan-200 hover:bg-slate-50'
                                                    }`}
                                                >
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p className="text-sm font-bold text-slate-900">{lineType(row)}</p>
                                                            <p className="mt-0.5 text-xs text-slate-500">{lineGroup(row)}</p>
                                                        </div>
                                                        <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${active ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-600'}`}>
                                                            {active ? 'Selected' : 'Choose'}
                                                        </span>
                                                    </div>
                                                    <div className="mt-3 grid grid-cols-3 gap-2 text-xs">
                                                        <div>
                                                            <p className="text-slate-500">Fee</p>
                                                            <p className="font-semibold tabular-nums text-slate-900">{money(row.fees_amount)}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-slate-500">Paid</p>
                                                            <p className="font-semibold tabular-nums text-emerald-700">{money(row.paid_amount)}</p>
                                                        </div>
                                                        <div>
                                                            <p className="text-slate-500">Remained</p>
                                                            <p className="font-semibold tabular-nums text-amber-800">{money(row.remained_amount)}</p>
                                                        </div>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>

                                <div className="grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
                                    <label className="block">
                                        <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">Due date</span>
                                        <input
                                            type="date"
                                            className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                                            value={form.date}
                                            onChange={(e) => setField('date', e.target.value)}
                                            required
                                        />
                                    </label>
                                    <label className="block">
                                        <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">Payment method</span>
                                        <select
                                            className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                                            value={form.payment_method}
                                            onChange={(e) => setField('payment_method', e.target.value)}
                                            required
                                        >
                                            {paymentMethods.map((item) => (
                                                <option key={item.value} value={item.value}>
                                                    {item.label}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="block">
                                        <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">Amount</span>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                                            value={form.amounts}
                                            onChange={(e) => setField('amounts', e.target.value)}
                                            placeholder="Enter amount"
                                            required
                                        />
                                    </label>
                                    <label className="block">
                                        <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">Account number</span>
                                        <select
                                            className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                                            value={form.account_id}
                                            onChange={(e) => setField('account_id', e.target.value)}
                                            required
                                        >
                                            <option value="">Select account number</option>
                                            {accountOptions.map((account) => (
                                                <option key={account.id} value={account.id}>
                                                    {[account.account_number, account.bank_name].filter(Boolean).join(' - ')}
                                                </option>
                                            ))}
                                        </select>
                                    </label>
                                    <label className="block">
                                        <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">Transaction receipt number</span>
                                        <input
                                            type="text"
                                            className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                                            value={form.comment}
                                            onChange={(e) => setField('comment', e.target.value)}
                                            placeholder="Enter transaction receipt number"
                                        />
                                    </label>
                                    <label className="block">
                                        <span className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600">Apply</span>
                                        <select
                                            className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                                            value={form.status}
                                            onChange={(e) => setField('status', e.target.value)}
                                        >
                                            <option value="1">All</option>
                                            <option value="2">One Only</option>
                                        </select>
                                    </label>
                                </div>

                                <div className="flex flex-col gap-2 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                                    <p className="text-xs text-slate-500">
                                        Submitting posts directly to the existing fee collection endpoint. The list refreshes after success.
                                    </p>
                                    <button
                                        type="submit"
                                        disabled={saving || !selectedFeeId}
                                        className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                    >
                                        <IconBanknote className="h-4 w-4" aria-hidden />
                                        {saving ? 'Saving payment…' : 'Confirm collection'}
                                    </button>
                                </div>
                            </form>
                        </section>
                    </div>
                ) : null}
            </div>
        </Layout>
    );
}
