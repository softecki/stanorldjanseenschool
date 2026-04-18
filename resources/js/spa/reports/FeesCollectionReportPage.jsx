import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import {
    AccountCard,
    AccountEmptyState,
    AccountFullPageLoader,
    AccountPageHeader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../accounts/components/AccountUi';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

function mappedClassOptions(classes = []) {
    return classes
        .map((c) => ({
            id: String(c?.class?.id ?? c?.id ?? ''),
            name: c?.class?.name ?? c?.name ?? '—',
        }))
        .filter((c) => c.id);
}

function fmtNum(n) {
    if (n === null || n === undefined || n === '') return '—';
    const x = Number(n);
    if (Number.isNaN(x)) return String(n);
    return x.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function statusForRemained(remained) {
    const r = Number(remained);
    if (r > 0) return { label: 'Unpaid', className: 'text-red-600' };
    if (r < 0) return { label: 'Over paid', className: 'text-amber-600' };
    return { label: 'Paid', className: 'text-emerald-600' };
}

function isDetailRow(row) {
    return row && typeof row.type_name === 'string';
}

export function FeesCollectionReportPage() {
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});

    const [classes, setClasses] = useState([]);

    const [filters, setFilters] = useState({
        class: '0',
        section: '0',
        dates: '',
        amount: '',
    });

    const classList = useMemo(() => mappedClassOptions(classes), [classes]);

    const loadIndex = useCallback(async (p = 1) => {
        setErr('');
        setBusy(true);
        try {
            const { data } = await axios.get('/report-fees-collection', {
                headers: xhrJson,
                params: { page: p },
            });
            setRows(data?.data || []);
            setMeta(data?.meta || {});
            setClasses(data?.meta?.classes || []);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load fees collection report.');
        } finally {
            setBusy(false);
        }
    }, []);

    useEffect(() => {
        let cancelled = false;
        (async () => {
            setLoading(true);
            await loadIndex(1);
            if (!cancelled) setLoading(false);
        })();
        return () => {
            cancelled = true;
        };
    }, [loadIndex]);

    const runSearch = async (e, pageOverride = 1) => {
        if (e?.preventDefault) e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const { data } = await axios.post(
                `/report-fees-collection/search?page=${pageOverride}`,
                {
                    class: filters.class,
                    section: filters.section,
                    dates: filters.dates,
                    amount: filters.amount,
                },
                { headers: xhrJson }
            );
            setRows(data?.data || []);
            setMeta(data?.meta || {});
            setClasses(data?.meta?.classes || []);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Search failed.');
        } finally {
            setBusy(false);
        }
    };

    const resetToSummary = async () => {
        setFilters({ class: '0', section: '0', dates: '', amount: '' });
        await loadIndex(1);
    };

    const pagination = meta.pagination;
    const isFiltered = Boolean(meta.filters);
    const detailMode = rows.length > 0 && isDetailRow(rows[0]);

    const goPage = (p) => {
        if (!pagination) return;
        if (p < 1 || p > pagination.last_page) return;
        if (isFiltered) runSearch(null, p);
        else loadIndex(p);
    };

    const exportPdf = meta.pdf_download_url;
    const exportExcel = meta.excel_download_url;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <AccountPageHeader
                    title={meta.title || 'Fees collection'}
                    actions={
                        <div className="flex flex-wrap gap-2">
                            <Link
                                to="/reports/fees-summary"
                                className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                            >
                                Fees summary
                            </Link>
                            <Link
                                to="/reports/students"
                                className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                            >
                                Students
                            </Link>
                            <Link
                                to="/reports/fees-by-year"
                                className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                            >
                                Fees by year
                            </Link>
                            <Link
                                to="/reports"
                                className="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                All reports
                            </Link>
                        </div>
                    }
                />

                <p className="-mt-2 text-sm text-gray-500">
                    Per-student totals for the current session, or detailed assignment lines when you search with filters.
                </p>

                {err ? <p className="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{err}</p> : null}

                <AccountCard>
                    <form onSubmit={runSearch} className="space-y-4 border-b border-gray-100 p-5">
                        <h2 className="text-sm font-semibold uppercase tracking-wide text-gray-500">Filters</h2>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <label className="block text-sm font-medium text-gray-700">
                                Class
                                <select
                                    className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm"
                                    value={filters.class}
                                    onChange={(e) => setFilters({ ...filters, class: e.target.value })}
                                >
                                    <option value="0">All classes</option>
                                    {classList.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-gray-700">
                                Balance status
                                <select
                                    className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm"
                                    value={filters.section}
                                    onChange={(e) => setFilters({ ...filters, section: e.target.value })}
                                >
                                    <option value="0">All</option>
                                    <option value="1">Fully paid (no balance)</option>
                                    <option value="2">With balance</option>
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-gray-700">
                                Date range (optional)
                                <input
                                    className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm"
                                    placeholder="YYYY-MM-DD - YYYY-MM-DD"
                                    value={filters.dates}
                                    onChange={(e) => setFilters({ ...filters, dates: e.target.value })}
                                />
                            </label>
                            <label className="block text-sm font-medium text-gray-700">
                                Amount (optional)
                                <input
                                    className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm"
                                    value={filters.amount}
                                    onChange={(e) => setFilters({ ...filters, amount: e.target.value })}
                                />
                            </label>
                            <div className="flex flex-col justify-end gap-2 sm:flex-row sm:items-end">
                                <button
                                    type="submit"
                                    disabled={busy}
                                    className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
                                >
                                    {busy ? 'Working…' : 'Apply search'}
                                </button>
                                <button
                                    type="button"
                                    disabled={busy}
                                    onClick={resetToSummary}
                                    className="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-60"
                                >
                                    Reset summary
                                </button>
                            </div>
                        </div>
                        <p className="text-xs text-gray-500">
                            «Balance status» filters by remaining fee balance (not classroom section). Use a date range to
                            narrow by assignment date; exports require class, status, and dates.
                        </p>
                    </form>

                    <div className="flex flex-wrap items-center gap-3 border-b border-gray-100 px-5 py-3">
                        {exportPdf ? (
                            <a
                                href={exportPdf}
                                className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-800 hover:bg-gray-50"
                            >
                                Download PDF
                            </a>
                        ) : null}
                        {exportExcel ? (
                            <a
                                href={exportExcel}
                                className="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-900 hover:bg-emerald-100"
                            >
                                Download Excel
                            </a>
                        ) : null}
                        {pagination ? (
                            <span className="text-sm text-gray-500">
                                Page {pagination.current_page} of {pagination.last_page} · {pagination.total} rows
                            </span>
                        ) : null}
                    </div>

                    <div className="p-2">
                        {loading ? (
                            <AccountFullPageLoader text="Loading report…" />
                        ) : rows.length === 0 ? (
                            <div className="p-4">
                                <AccountEmptyState message="No rows for this view." />
                            </div>
                        ) : detailMode ? (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Class</AccountTH>
                                        <AccountTH>Fee type</AccountTH>
                                        <AccountTH className="text-right">Fees</AccountTH>
                                        <AccountTH className="text-right">Paid</AccountTH>
                                        <AccountTH className="text-right">Balance</AccountTH>
                                        <AccountTH>Recorded</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-gray-100 bg-white">
                                    {rows.map((row, idx) => {
                                        const st = statusForRemained(row.remained_amount);
                                        return (
                                            <AccountTR key={row.id ? `${row.id}-${idx}` : idx}>
                                                <AccountTD>{(pagination?.per_page || 20) * ((pagination?.current_page || 1) - 1) + idx + 1}</AccountTD>
                                                <AccountTD className="font-medium text-gray-900">
                                                    {row.first_name} {row.last_name}
                                                </AccountTD>
                                                <AccountTD>{row.class_name}</AccountTD>
                                                <AccountTD className="max-w-[220px] truncate" title={row.type_name}>
                                                    {row.type_name}
                                                </AccountTD>
                                                <AccountTD className="text-right tabular-nums">{fmtNum(row.fees_amount)}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{fmtNum(row.paid_amount)}</AccountTD>
                                                <AccountTD className={`text-right tabular-nums font-medium ${st.className}`}>
                                                    {fmtNum(row.remained_amount)}
                                                </AccountTD>
                                                <AccountTD className="text-xs text-gray-500">
                                                    {row.created_at ? new Date(row.created_at).toLocaleString() : '—'}
                                                </AccountTD>
                                            </AccountTR>
                                        );
                                    })}
                                </tbody>
                            </AccountTable>
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Class</AccountTH>
                                        <AccountTH className="text-right">Outstanding (bal.)</AccountTH>
                                        <AccountTH className="text-right">Q1 + Q2</AccountTH>
                                        <AccountTH className="text-right">Main fees</AccountTH>
                                        <AccountTH className="text-right">Paid</AccountTH>
                                        <AccountTH className="text-right">Balance</AccountTH>
                                        <AccountTH>Status</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-gray-100 bg-white">
                                    {rows.map((row, idx) => {
                                        const st = statusForRemained(row.remained_amount);
                                        return (
                                            <AccountTR key={row.id ?? idx}>
                                                <AccountTD>{(pagination?.per_page || 20) * ((pagination?.current_page || 1) - 1) + idx + 1}</AccountTD>
                                                <AccountTD className="font-medium text-gray-900">
                                                    {row.first_name} {row.last_name}
                                                </AccountTD>
                                                <AccountTD>{row.class_name}</AccountTD>
                                                <AccountTD className="text-right tabular-nums text-gray-700">
                                                    {fmtNum(row.outstanding_remained_amount)}
                                                </AccountTD>
                                                <AccountTD className="text-right tabular-nums">
                                                    {fmtNum(Number(row.quater_one || 0) + Number(row.quater_two || 0))}
                                                </AccountTD>
                                                <AccountTD className="text-right tabular-nums">{fmtNum(row.fees_amount)}</AccountTD>
                                                <AccountTD className="text-right tabular-nums">{fmtNum(row.paid_amount)}</AccountTD>
                                                <AccountTD className="text-right tabular-nums font-medium">{fmtNum(row.remained_amount)}</AccountTD>
                                                <AccountTD className={st.className}>{st.label}</AccountTD>
                                            </AccountTR>
                                        );
                                    })}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>

                    {pagination && pagination.last_page > 1 ? (
                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4">
                            <button
                                type="button"
                                disabled={busy || pagination.current_page <= 1}
                                onClick={() => goPage(pagination.current_page - 1)}
                                className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                disabled={busy || pagination.current_page >= pagination.last_page}
                                onClick={() => goPage(pagination.current_page + 1)}
                                className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                            >
                                Next
                            </button>
                        </div>
                    ) : null}
                </AccountCard>
            </div>
        </AdminLayout>
    );
}
