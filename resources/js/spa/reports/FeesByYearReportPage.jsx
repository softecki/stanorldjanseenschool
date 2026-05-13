import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';
import {
    AccountCard,
    AccountEmptyState,
    AccountFullPageLoader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../accounts/components/AccountUi';

function fmtMoney(value) {
    const number = Number(value ?? 0);
    if (!Number.isFinite(number)) return '0.00';
    return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function currentYear() {
    return String(new Date().getFullYear());
}

function StatCard({ label, value, tone }) {
    return (
        <div className={`rounded-2xl border p-5 shadow-sm ${tone}`}>
            <p className="text-sm font-medium opacity-80">{label}</p>
            <p className="mt-2 text-2xl font-bold tracking-tight">{value}</p>
        </div>
    );
}

export function FeesByYearReportPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState({ year: currentYear(), class: '' });
    const [bulkYear, setBulkYear] = useState(currentYear());
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [actionBusy, setActionBusy] = useState('');
    const [err, setErr] = useState('');
    const [message, setMessage] = useState('');

    const years = useMemo(() => meta.years || [], [meta.years]);
    const classes = useMemo(() => meta.classes || [], [meta.classes]);
    const selectedYear = String(meta.selected_year || filters.year || currentYear());
    const totalOutstanding = useMemo(
        () => rows.reduce((sum, row) => sum + Number(row.total_outstandingbalance || 0), 0),
        [rows]
    );

    const applyResponse = (data) => {
        setRows(data?.data || []);
        setMeta(data?.meta || {});
        setFilters((current) => ({
            ...current,
            year: String(data?.meta?.selected_year || current.year),
            class: String(data?.meta?.selected_class || current.class || ''),
        }));
        setBulkYear(String(data?.meta?.selected_year || bulkYear || currentYear()));
    };

    const loadIndex = useCallback(async () => {
        setLoading(true);
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-fees-by-year', { headers: xhrJson });
            applyResponse(data);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load fees by year report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadIndex();
    }, [loadIndex]);

    const search = async (e) => {
        if (e?.preventDefault) e.preventDefault();
        setBusy(true);
        setErr('');
        setMessage('');
        try {
            const { data } = await axios.post('/report-fees-by-year/search', filters, { headers: xhrJson });
            applyResponse(data);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search fees by year.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    const bulkRecalculate = async () => {
        const year = bulkYear || filters.year || selectedYear;
        if (!window.confirm(`Recalculate balances for all students in ${year}?`)) return;
        setActionBusy('bulk');
        setErr('');
        setMessage('');
        try {
            const { data } = await axios.post('/report-fees-by-year/bulk-recalculate', { year }, { headers: xhrJson });
            setMessage(`Bulk recalculation completed. Processed ${data.success_count || 0} of ${data.total_students || 0} students.`);
            await search(null);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Bulk recalculation failed.');
        } finally {
            setActionBusy('');
        }
    };

    const generateOutstanding = async () => {
        if (!window.confirm('Generate Outstanding Balance for 2026 from previous balances?')) return;
        setActionBusy('generate');
        setErr('');
        setMessage('');
        try {
            const { data } = await axios.post('/report-fees-by-year/generate-outstanding-balance-2026', {}, { headers: xhrJson });
            setMessage(`Outstanding balance generated. Created/updated: ${data.created_count || 0}, skipped: ${data.skipped_count || 0}.`);
            await search(null);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Outstanding balance generation failed.');
        } finally {
            setActionBusy('');
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-blue-200">Fees assignment by year</p>
                            <div className="mt-5 flex flex-wrap gap-2">
                                <Link to="/reports" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                    All reports
                                </Link>
                            </div>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                            <p className="text-3xl font-bold">{rows.length}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-blue-100">Records for {selectedYear}</p>
                        </div>
                    </div>
                </div>

                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}
                {message ? <p className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{message}</p> : null}

                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard label="Selected year" value={selectedYear} tone="border-blue-100 bg-blue-50 text-blue-900" />
                    <StatCard label="Total records" value={rows.length.toLocaleString()} tone="border-emerald-100 bg-emerald-50 text-emerald-900" />
                    <StatCard label="Outstanding balance" value={fmtMoney(totalOutstanding)} tone="border-red-100 bg-red-50 text-red-900" />
                </div>

                <AccountCard>
                    <form onSubmit={search} className="border-b border-slate-100 p-5">
                        <div className="mb-4 flex items-center justify-between gap-3">
                            <h2 className="text-lg font-semibold text-slate-950">Filtering</h2>
                        </div>
                        <div className="flex flex-wrap items-end gap-3">
                            <div className="w-full sm:w-[150px]">
                                <label className="block text-sm font-medium text-slate-700">
                                    Bulk year
                                    <input
                                        type="number"
                                        min="2000"
                                        max="2100"
                                        placeholder="Enter Year"
                                        value={bulkYear}
                                        onChange={(e) => setBulkYear(e.target.value)}
                                        className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    />
                                </label>
                            </div>
                            <button
                                type="button"
                                disabled={Boolean(actionBusy)}
                                onClick={bulkRecalculate}
                                className="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 disabled:opacity-60"
                            >
                                {actionBusy === 'bulk' ? 'Recalculating...' : 'Bulk Recalculate Balances'}
                            </button>
                            <button
                                type="button"
                                disabled={Boolean(actionBusy)}
                                onClick={generateOutstanding}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
                            >
                                {actionBusy === 'generate' ? 'Generating...' : 'Generate Outstanding Balance for 2026'}
                            </button>
                            <label className="block w-full text-sm font-medium text-slate-700 sm:w-[180px]">
                                Year
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.year}
                                    onChange={(e) => setFilters((current) => ({ ...current, year: e.target.value }))}
                                >
                                    <option value="">Select year</option>
                                    {years.length === 0 ? <option value={filters.year}>{filters.year}</option> : null}
                                    {years.map((yearItem) => (
                                        <option key={yearItem.year} value={yearItem.year}>{yearItem.year}</option>
                                    ))}
                                </select>
                            </label>
                            <label className="block w-full text-sm font-medium text-slate-700 sm:w-[220px]">
                                Class
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.class}
                                    onChange={(e) => setFilters((current) => ({ ...current, class: e.target.value }))}
                                >
                                    <option value="">Select class (All)</option>
                                    {classes.map((classItem) => (
                                        <option key={classItem.id} value={classItem.id}>{classItem.name}</option>
                                    ))}
                                </select>
                            </label>
                            <button
                                type="submit"
                                disabled={busy}
                                className="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 disabled:opacity-60"
                            >
                                {busy ? 'Searching...' : 'Search'}
                            </button>
                        </div>
                    </form>

                    <div className="p-2">
                        {loading ? (
                            <AccountFullPageLoader text="Loading fees by year..." />
                        ) : rows.length === 0 ? (
                            <div className="p-4"><AccountEmptyState message="No fees assignment rows found." /></div>
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Class</AccountTH>
                                        <AccountTH className="text-right">Total outstanding balance</AccountTH>
                                        <AccountTH className="text-right">Action</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={`${row.student_id}-${index}`}>
                                            <AccountTD>{index + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.first_name} {row.last_name}</AccountTD>
                                            <AccountTD>{row.class_name || 'N/A'}</AccountTD>
                                            <AccountTD className="text-right tabular-nums font-semibold text-red-700">{fmtMoney(row.total_outstandingbalance)}</AccountTD>
                                            <AccountTD className="text-right">
                                                <Link
                                                    to={`/reports/fees-by-year/${row.student_id}?year=${selectedYear}`}
                                                    className="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                                                >
                                                    View
                                                </Link>
                                            </AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>
                </AccountCard>
            </div>
        </AdminLayout>
    );
}

export function FeesByYearDetailPage() {
    const { studentId } = useParams();
    const query = new URLSearchParams(window.location.search);
    const [year, setYear] = useState(query.get('year') || currentYear());
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState('');
    const [err, setErr] = useState('');
    const [message, setMessage] = useState('');

    const load = useCallback(async (selectedYear) => {
        setLoading(true);
        setErr('');
        try {
            const response = await axios.get(`/report-fees-by-year/detail/${studentId}`, {
                headers: xhrJson,
                params: { year: selectedYear },
            });
            setData(response.data?.data || null);
            setYear(String(response.data?.meta?.selected_year || selectedYear));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load student fees detail.');
        } finally {
            setLoading(false);
        }
    }, [studentId]);

    useEffect(() => {
        load(year);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [load]);

    const recalculate = async () => {
        if (!window.confirm('Recalculate balances for this student?')) return;
        setBusy('recalculate');
        setErr('');
        setMessage('');
        try {
            const response = await axios.post(`/report-fees-by-year/recalculate/${studentId}/${year}`, {}, { headers: xhrJson });
            setMessage(`Balances recalculated. Transactions: ${response.data?.total_transactions || 0}, amount: ${fmtMoney(response.data?.total_amount)}.`);
            await load(year);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Recalculation failed.');
        } finally {
            setBusy('');
        }
    };

    const generateOutstandingForStudent = async () => {
        if (!window.confirm('Generate 2026 outstanding balance for this student?')) return;
        setBusy('generate');
        setErr('');
        setMessage('');
        try {
            const response = await axios.post(`/report-fees-by-year/generate-outstanding-balance-2026/${studentId}`, {}, { headers: xhrJson });
            setMessage(response.data?.message || 'Outstanding balance generated.');
            await load(year);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Outstanding generation failed.');
        } finally {
            setBusy('');
        }
    };

    const student = data?.student;
    const feesGroups = data?.feesGroups || [];
    const transactions = data?.transactions || [];

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-blue-200">Student fees detail</p>
                            {student ? <p className="mt-3 text-2xl font-bold">{student.first_name} {student.last_name}</p> : null}
                            <div className="mt-5 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    disabled={Boolean(busy)}
                                    onClick={recalculate}
                                    className="rounded-lg bg-amber-400 px-3.5 py-2 text-sm font-semibold text-slate-950 shadow-sm transition hover:bg-amber-300 disabled:opacity-60"
                                >
                                    {busy === 'recalculate' ? 'Recalculating...' : 'Recalculate Balances'}
                                </button>
                                {String(year) === '2025' ? (
                                    <button
                                        type="button"
                                        disabled={Boolean(busy)}
                                        onClick={generateOutstandingForStudent}
                                        className="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-blue-50 disabled:opacity-60"
                                    >
                                        {busy === 'generate' ? 'Generating...' : 'Generate 2026 Outstanding'}
                                    </button>
                                ) : null}
                                <Link to="/reports/fees-by-year" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                    Back to report
                                </Link>
                            </div>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                            <p className="text-3xl font-bold">{year}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-blue-100">Selected year</p>
                        </div>
                    </div>
                </div>

                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}
                {message ? <p className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{message}</p> : null}

                {loading ? (
                    <AccountFullPageLoader text="Loading student fees detail..." />
                ) : !student ? (
                    <AccountCard><div className="p-4"><AccountEmptyState message="Student detail not found." /></div></AccountCard>
                ) : (
                    <>
                        <AccountCard>
                            <div className="grid gap-4 p-5 md:grid-cols-4">
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</p>
                                    <p className="mt-1 font-semibold text-slate-900">{student.first_name} {student.last_name}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Class</p>
                                    <p className="mt-1 font-semibold text-slate-900">{student.class_name || 'N/A'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Section</p>
                                    <p className="mt-1 font-semibold text-slate-900">{student.section_name || 'N/A'}</p>
                                </div>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Year</p>
                                    <input
                                        type="number"
                                        min="2000"
                                        max="2100"
                                        value={year}
                                        onChange={(e) => setYear(e.target.value)}
                                        onBlur={() => load(year)}
                                        className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    />
                                </div>
                            </div>
                        </AccountCard>

                        <AccountCard>
                            <div className="border-b border-slate-100 p-5">
                                <h2 className="text-lg font-semibold text-slate-950">Fees Groups Breakdown</h2>
                            </div>
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Fees Group</AccountTH>
                                        <AccountTH className="text-right">Fees Amount</AccountTH>
                                        <AccountTH className="text-right">Paid Amount</AccountTH>
                                        <AccountTH className="text-right">Remained Amount</AccountTH>
                                        <AccountTH className="text-right">Outstanding Balance</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {feesGroups.map((group, index) => (
                                        <AccountTR key={group.fees_group_id || index}>
                                            <AccountTD>{index + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{group.fees_group_name}</AccountTD>
                                            <AccountTD className="text-right tabular-nums">{fmtMoney(group.total_fees_amount)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums text-emerald-700">{fmtMoney(group.total_paid_amount)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums font-semibold text-red-700">{fmtMoney(group.total_remained_amount)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums">{fmtMoney(group.total_outstandingbalance)}</AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        </AccountCard>

                        <AccountCard>
                            <div className="border-b border-slate-100 p-5">
                                <h2 className="text-lg font-semibold text-slate-950">Transactions</h2>
                            </div>
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Transaction ID</AccountTH>
                                        <AccountTH>Date</AccountTH>
                                        <AccountTH>Fees Group</AccountTH>
                                        <AccountTH>Fees Type</AccountTH>
                                        <AccountTH className="text-right">Amount</AccountTH>
                                        <AccountTH>Payment Method</AccountTH>
                                        <AccountTH>Bank Account</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {transactions.map((transaction, index) => (
                                        <AccountTR key={transaction.id || index}>
                                            <AccountTD>{index + 1}</AccountTD>
                                            <AccountTD>{transaction.transaction_id || 'N/A'}</AccountTD>
                                            <AccountTD>{transaction.date || 'N/A'}</AccountTD>
                                            <AccountTD>{transaction.fees_group_name || 'N/A'}</AccountTD>
                                            <AccountTD>{transaction.fees_type_name || 'N/A'}</AccountTD>
                                            <AccountTD className="text-right tabular-nums">{fmtMoney(transaction.amount)}</AccountTD>
                                            <AccountTD>{transaction.payment_method || 'N/A'}</AccountTD>
                                            <AccountTD>{transaction.account_name || transaction.account_number || 'N/A'}</AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        </AccountCard>
                    </>
                )}
            </div>
        </AdminLayout>
    );
}
