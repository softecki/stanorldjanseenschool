import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { AccountsPageShell } from '../accounts/AccountsModuleShared';
import { AccountCard, AccountEmptyState, AccountFullPageLoader, AccountTable, AccountTD, AccountTH, AccountTHead, AccountTR } from '../accounts/components/AccountUi';
import { xhrJson } from '../api/xhrJson';

function fmtMoney(value) {
    const number = Number(value || 0);
    if (!Number.isFinite(number)) return '0.00';
    return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function compact(text, length = 80) {
    const value = String(text || '');
    return value.length > length ? `${value.slice(0, length)}...` : value || '—';
}

function StatCard({ label, value, tone }) {
    return (
        <div className={`rounded-2xl border p-5 shadow-sm ${tone}`}>
            <p className="text-sm font-medium opacity-80">{label}</p>
            <p className="mt-2 text-2xl font-bold tracking-tight">{value}</p>
        </div>
    );
}

export function BankReconciliationReportPage() {
    const [transactions, setTransactions] = useState([]);
    const [file, setFile] = useState(null);
    const [loading, setLoading] = useState(true);
    const [uploading, setUploading] = useState(false);
    const [err, setErr] = useState('');
    const [message, setMessage] = useState('');

    useEffect(() => {
        const loadExisting = async () => {
            setLoading(true);
            setErr('');
            try {
                const response = await axios.get('/accounting/bank-reconciliation/process', { headers: xhrJson });
                setTransactions(Array.isArray(response.data?.data) ? response.data.data : []);
            } catch (ex) {
                if (ex.response?.status !== 422) {
                    setErr(ex.response?.data?.message || 'Failed to load bank reconciliation report.');
                }
            } finally {
                setLoading(false);
            }
        };

        loadExisting();
    }, []);

    const summary = useMemo(() => {
        const total = transactions.length;
        const matched = transactions.filter((item) => item?.student_name).length;
        const unmatched = total - matched;
        const matchRate = total > 0 ? Math.round((matched / total) * 1000) / 10 : 0;

        return { total, matched, unmatched, matchRate };
    }, [transactions]);

    const upload = async (event) => {
        event.preventDefault();
        if (!file) {
            setErr('Please choose an Excel file first.');
            return;
        }

        setUploading(true);
        setErr('');
        setMessage('');

        try {
            const form = new FormData();
            form.append('excel_file', file);

            const response = await axios.post('/accounting/bank-reconciliation/upload', form, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            setTransactions(Array.isArray(response.data?.data) ? response.data.data : []);
            setMessage(response.data?.message || 'Bank statement uploaded successfully.');
            setFile(null);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to upload bank statement.');
        } finally {
            setUploading(false);
        }
    };

    const reset = async () => {
        setErr('');
        setMessage('');
        try {
            await axios.get('/accounting/bank-reconciliation/reset', { headers: xhrJson });
            setTransactions([]);
            setMessage('Session cleared. Ready for a new upload.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to reset bank reconciliation data.');
        }
    };

    return (
        <AdminLayout>
            <AccountsPageShell>
                <div className="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200">Accounting report</p>
                            <div className="mt-5 flex flex-wrap gap-2">
                                {transactions.length ? (
                                    <>
                                        <a href="/accounting/bank-reconciliation/generate-excel" className="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-emerald-50">
                                            Download Excel Report
                                        </a>
                                        <a href="/accounting/bank-reconciliation/generate-pdf" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                            Download PDF Report
                                        </a>
                                        <button type="button" onClick={reset} className="rounded-lg border border-amber-200 bg-amber-400 px-3.5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                                            Upload Another File
                                        </button>
                                    </>
                                ) : null}
                                <Link to="/reports" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                    Back to reports
                                </Link>
                            </div>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                            <p className="text-3xl font-bold">{summary.total}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-emerald-100">Transactions</p>
                        </div>
                    </div>
                </div>

                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">{err}</p> : null}
                {message ? <p className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">{message}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading bank reconciliation..." /> : null}

                {!loading ? (
                    <div className="space-y-5">
                        <AccountCard>
                            <form onSubmit={upload} className="space-y-5 p-5">
                                <div>
                                    <h2 className="text-lg font-semibold text-slate-950">Upload Bank Statement Excel</h2>
                                    <p className="mt-1 text-sm text-slate-500">
                                        Upload an Excel or CSV bank statement. The system extracts transactions and matches students from reference/control numbers.
                                    </p>
                                </div>

                                <label className="block text-sm font-medium text-slate-700">
                                    Excel file
                                    <input
                                        type="file"
                                        accept=".xlsx,.xls,.csv"
                                        className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                                        onChange={(event) => setFile(event.target.files?.[0] || null)}
                                    />
                                </label>

                                <div className="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
                                    <p className="font-semibold">Expected Excel Format</p>
                                    <p className="mt-1">Posting Date, Details, Value Date, Debit, Credit, Book Balance.</p>
                                    <p className="mt-1 text-xs text-blue-700">Maximum file size: 10MB. Supported formats: XLSX, XLS, CSV.</p>
                                </div>

                                <div className="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm text-amber-900">
                                    <p className="font-semibold">How it works</p>
                                    <ol className="mt-2 list-decimal space-y-1 pl-5">
                                        <li>Export your bank statement from your bank as Excel or CSV.</li>
                                        <li>Make sure each transaction is in its own row.</li>
                                        <li>The Details column should include a reference number where possible.</li>
                                        <li>The system matches transactions to students and prepares PDF/Excel reports.</li>
                                    </ol>
                                </div>

                                <div className="flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={uploading}
                                        className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-60"
                                    >
                                        {uploading ? 'Uploading...' : 'Upload Excel File'}
                                    </button>
                                </div>
                            </form>
                        </AccountCard>

                        <div className="grid gap-4 md:grid-cols-4">
                            <StatCard label="Total transactions" value={summary.total} tone="border-blue-100 bg-blue-50 text-blue-900" />
                            <StatCard label="Matched students" value={summary.matched} tone="border-emerald-100 bg-emerald-50 text-emerald-900" />
                            <StatCard label="Unmatched" value={summary.unmatched} tone="border-amber-100 bg-amber-50 text-amber-900" />
                            <StatCard label="Match rate" value={`${summary.matchRate}%`} tone="border-violet-100 bg-violet-50 text-violet-900" />
                        </div>

                        <AccountCard>
                            <div className="border-b border-slate-100 p-5">
                                <h2 className="text-lg font-semibold text-slate-950">Processed Transactions</h2>
                                <p className="mt-1 text-sm text-slate-500">
                                    Green student badges indicate successful matches. Blank student values are unmatched transactions.
                                </p>
                            </div>

                            {transactions.length ? (
                                <AccountTable>
                                    <AccountTHead>
                                        <AccountTR>
                                            <AccountTH>Posting Date</AccountTH>
                                            <AccountTH>Details</AccountTH>
                                            <AccountTH>Control Number</AccountTH>
                                            <AccountTH>Value Date</AccountTH>
                                            <AccountTH className="text-right">Debit</AccountTH>
                                            <AccountTH className="text-right">Credit</AccountTH>
                                            <AccountTH className="text-right">Book Balance</AccountTH>
                                            <AccountTH>Student Name</AccountTH>
                                        </AccountTR>
                                    </AccountTHead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {transactions.map((transaction, idx) => (
                                            <AccountTR key={idx}>
                                                <AccountTD>{transaction.posting_date || '—'}</AccountTD>
                                                <AccountTD className="max-w-[320px] truncate" title={transaction.details || ''}>
                                                    {compact(transaction.details)}
                                                </AccountTD>
                                                <AccountTD>{transaction.control_number || '—'}</AccountTD>
                                                <AccountTD>{transaction.value_date || '—'}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{Number(transaction.debit || 0) > 0 ? fmtMoney(transaction.debit) : '—'}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{Number(transaction.credit || 0) > 0 ? fmtMoney(transaction.credit) : '—'}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{fmtMoney(transaction.book_balance)}</AccountTD>
                                                <AccountTD>
                                                    {transaction.student_name ? (
                                                        <span className="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                                            {transaction.student_name}
                                                        </span>
                                                    ) : (
                                                        <span className="text-slate-400">—</span>
                                                    )}
                                                </AccountTD>
                                            </AccountTR>
                                        ))}
                                    </tbody>
                                </AccountTable>
                            ) : (
                                <div className="p-5">
                                    <AccountEmptyState message="No transactions uploaded yet. Upload an Excel file to begin reconciliation." />
                                </div>
                            )}
                        </AccountCard>
                    </div>
                ) : null}
            </AccountsPageShell>
        </AdminLayout>
    );
}

export function BankReconciliationProcessPage() {
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');
    const [message, setMessage] = useState('');

    const summary = useMemo(() => {
        const total = transactions.length;
        const matched = transactions.filter((item) => item?.student_name).length;
        const unmatched = total - matched;
        const matchRate = total > 0 ? Math.round((matched / total) * 1000) / 10 : 0;

        return { total, matched, unmatched, matchRate };
    }, [transactions]);

    const load = useCallback(async () => {
        setLoading(true);
        setErr('');
        try {
            const response = await axios.get('/accounting/bank-reconciliation/process', { headers: xhrJson });
            setTransactions(Array.isArray(response.data?.data) ? response.data.data : []);
        } catch (ex) {
            setTransactions([]);
            setErr(ex.response?.data?.message || 'No transactions to process. Please upload an Excel file first.');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const reset = async () => {
        setErr('');
        setMessage('');
        try {
            await axios.get('/accounting/bank-reconciliation/reset', { headers: xhrJson });
            setTransactions([]);
            setMessage('Session cleared. Ready for a new upload.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to reset bank reconciliation data.');
        }
    };

    return (
        <AdminLayout>
            <AccountsPageShell>
                <div className="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200">Bank reconciliation process</p>
                            <div className="mt-5 flex flex-wrap gap-2">
                                {transactions.length ? (
                                    <>
                                        <a href="/accounting/bank-reconciliation/generate-excel" className="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-emerald-50">
                                            Download Excel Report
                                        </a>
                                        <a href="/accounting/bank-reconciliation/generate-pdf" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                            Download PDF Report
                                        </a>
                                        <button type="button" onClick={reset} className="rounded-lg border border-amber-200 bg-amber-400 px-3.5 py-2 text-sm font-semibold text-slate-950 transition hover:bg-amber-300">
                                            Upload Another File
                                        </button>
                                    </>
                                ) : null}
                                <Link to="/reports/accounting/bank-reconciliation" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                    Upload bank statement
                                </Link>
                                <Link to="/reports" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                    Back to reports
                                </Link>
                            </div>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                            <p className="text-3xl font-bold">{summary.total}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-emerald-100">Transactions</p>
                        </div>
                    </div>
                </div>

                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700">{err}</p> : null}
                {message ? <p className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">{message}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading processed transactions..." /> : null}

                {!loading ? (
                    <div className="space-y-5">
                        <div className="grid gap-4 md:grid-cols-4">
                            <StatCard label="Total transactions" value={summary.total} tone="border-blue-100 bg-blue-50 text-blue-900" />
                            <StatCard label="Matched students" value={summary.matched} tone="border-emerald-100 bg-emerald-50 text-emerald-900" />
                            <StatCard label="Unmatched" value={summary.unmatched} tone="border-amber-100 bg-amber-50 text-amber-900" />
                            <StatCard label="Match rate" value={`${summary.matchRate}%`} tone="border-violet-100 bg-violet-50 text-violet-900" />
                        </div>

                        <AccountCard>
                            <div className="border-b border-slate-100 p-5">
                                <h2 className="text-lg font-semibold text-slate-950">Processed Transactions</h2>
                                <p className="mt-1 text-sm text-slate-500">
                                    Green student badges indicate successful matches. Blank student values are unmatched transactions.
                                </p>
                            </div>

                            {transactions.length ? (
                                <AccountTable>
                                    <AccountTHead>
                                        <AccountTR>
                                            <AccountTH>Posting Date</AccountTH>
                                            <AccountTH>Details</AccountTH>
                                            <AccountTH>Control Number</AccountTH>
                                            <AccountTH>Value Date</AccountTH>
                                            <AccountTH className="text-right">Debit</AccountTH>
                                            <AccountTH className="text-right">Credit</AccountTH>
                                            <AccountTH className="text-right">Book Balance</AccountTH>
                                            <AccountTH>Student Name</AccountTH>
                                        </AccountTR>
                                    </AccountTHead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {transactions.map((transaction, idx) => (
                                            <AccountTR key={idx}>
                                                <AccountTD>{transaction.posting_date || '—'}</AccountTD>
                                                <AccountTD className="max-w-[320px] truncate" title={transaction.details || ''}>
                                                    {compact(transaction.details)}
                                                </AccountTD>
                                                <AccountTD>{transaction.control_number || '—'}</AccountTD>
                                                <AccountTD>{transaction.value_date || '—'}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{Number(transaction.debit || 0) > 0 ? fmtMoney(transaction.debit) : '—'}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{Number(transaction.credit || 0) > 0 ? fmtMoney(transaction.credit) : '—'}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{fmtMoney(transaction.book_balance)}</AccountTD>
                                                <AccountTD>
                                                    {transaction.student_name ? (
                                                        <span className="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                                            {transaction.student_name}
                                                        </span>
                                                    ) : (
                                                        <span className="text-slate-400">—</span>
                                                    )}
                                                </AccountTD>
                                            </AccountTR>
                                        ))}
                                    </tbody>
                                </AccountTable>
                            ) : (
                                <div className="p-5">
                                    <AccountEmptyState message="No processed transactions found. Upload an Excel file to begin reconciliation." />
                                </div>
                            )}
                        </AccountCard>
                    </div>
                ) : null}
            </AccountsPageShell>
        </AdminLayout>
    );
}

