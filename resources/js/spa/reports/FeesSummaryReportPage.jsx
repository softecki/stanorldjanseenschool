import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
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

function classOptionsFrom(metaClasses = []) {
    return (metaClasses || [])
        .map((c) => ({ id: String(c?.class?.id ?? c?.classes_id ?? c?.id ?? ''), name: c?.class?.name ?? c?.name ?? '—' }))
        .filter((x) => x.id);
}

function feeGroupOptionsFrom(groups = []) {
    return (groups || []).map((g) => ({ id: String(g.id), name: g.name || '—' })).filter((g) => g.id);
}

function fmtMoney(value) {
    const number = Number(value ?? 0);
    if (!Number.isFinite(number)) return '0.00';
    return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function defaultFilters() {
    return {
        class: '0',
        fee_group_id: '0',
        section: '0',
        amount: '',
        date_from: '',
        date_to: '',
    };
}

function StatCard({ label, value, tone }) {
    return (
        <div className={`rounded-2xl border p-5 shadow-sm ${tone}`}>
            <p className="text-sm font-medium opacity-80">{label}</p>
            <p className="mt-2 text-2xl font-bold tracking-tight">{value}</p>
        </div>
    );
}

export function FeesSummaryReportPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState(defaultFilters);
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');
    const [searched, setSearched] = useState(false);

    const classOptions = useMemo(() => classOptionsFrom(meta.classes || []), [meta.classes]);
    const feeGroups = useMemo(() => feeGroupOptionsFrom(meta.fee_groups || []), [meta.fee_groups]);
    const totals = meta.totals || {};
    const pagination = meta.pagination || null;

    const applyResponse = (data) => {
        setRows(data?.data || []);
        setMeta(data?.meta || {});
    };

    const loadIndex = useCallback(async (page = 1) => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-fees-summary', {
                headers: xhrJson,
                params: { page },
            });
            applyResponse(data);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load fees summary.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadIndex(1);
    }, [loadIndex]);

    const search = async (e, page = 1) => {
        if (e?.preventDefault) e.preventDefault();
        setBusy(true);
        setErr('');
        try {
            const payload = {
                ...filters,
                dates: filters.date_from && filters.date_to ? `${filters.date_from} - ${filters.date_to}` : '',
            };
            const { data } = await axios.post(`/report-fees-summary/search?page=${page}`, payload, { headers: xhrJson });
            applyResponse(data);
            setSearched(true);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search fees summary.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    const reset = async () => {
        setFilters(defaultFilters());
        setSearched(false);
        await loadIndex(1);
    };

    const goPage = (page) => {
        if (!pagination || page < 1 || page > pagination.last_page) return;
        if (searched) search(null, page);
        else loadIndex(page);
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-blue-200">Fees report</p>
                            <div className="mt-5 flex flex-wrap gap-2">
                                {meta.pdf_download_url ? (
                                    <a href={meta.pdf_download_url} className="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-blue-50">
                                        Print PDF
                                    </a>
                                ) : null}
                                {meta.excel_download_url ? (
                                    <a href={meta.excel_download_url} className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                        Export Excel
                                    </a>
                                ) : null}
                                <Link to="/reports" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                                    All reports
                                </Link>
                            </div>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                            <p className="text-3xl font-bold">{totals.students_count || 0}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-blue-100">Students in report</p>
                        </div>
                    </div>
                </div>

                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="Total assigned" value={fmtMoney(totals.total_assigned_amount)} tone="border-blue-100 bg-blue-50 text-blue-900" />
                    <StatCard label="Fees (excl. outstanding)" value={fmtMoney(totals.fees_excluding_outstanding)} tone="border-slate-100 bg-slate-50 text-slate-900" />
                    <StatCard label="Paid (fee collections)" value={fmtMoney(totals.paid_from_collections)} tone="border-emerald-100 bg-emerald-50 text-emerald-900" />
                    <StatCard label="Remained" value={fmtMoney(totals.remained_after_collections)} tone="border-red-100 bg-red-50 text-red-900" />
                </div>

                <AccountCard>
                    <form onSubmit={search} className="space-y-5 border-b border-slate-100 p-5">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <label className="block text-sm font-medium text-slate-700">
                                Class
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.class}
                                    onChange={(e) => setFilters((current) => ({ ...current, class: e.target.value }))}
                                >
                                    <option value="0">All classes</option>
                                    {classOptions.map((item) => (
                                        <option key={item.id} value={item.id}>{item.name}</option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700">
                                Fee group
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.fee_group_id}
                                    onChange={(e) => setFilters((current) => ({ ...current, fee_group_id: e.target.value }))}
                                >
                                    <option value="0">All fee groups</option>
                                    {feeGroups.map((item) => (
                                        <option key={item.id} value={item.id}>{item.name}</option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700">
                                Balance status
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.section}
                                    onChange={(e) => setFilters((current) => ({ ...current, section: e.target.value }))}
                                >
                                    <option value="0">All statuses</option>
                                    <option value="1">Paid</option>
                                    <option value="2">With balance</option>
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700">
                                Remaining greater than
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.amount}
                                    onChange={(e) => setFilters((current) => ({ ...current, amount: e.target.value }))}
                                />
                            </label>
                            <div className="rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm md:col-span-2">
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="block text-sm font-medium text-slate-600">
                                        From
                                        <input
                                            type="date"
                                            className="mt-1 w-full rounded-2xl border border-white bg-white px-4 py-3 text-base text-slate-900 shadow-sm ring-1 ring-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                            value={filters.date_from}
                                            onChange={(e) => setFilters((current) => ({ ...current, date_from: e.target.value }))}
                                        />
                                    </label>
                                    <label className="block text-sm font-medium text-slate-600">
                                        To
                                        <input
                                            type="date"
                                            className="mt-1 w-full rounded-2xl border border-white bg-white px-4 py-3 text-base text-slate-900 shadow-sm ring-1 ring-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                            value={filters.date_to}
                                            onChange={(e) => setFilters((current) => ({ ...current, date_to: e.target.value }))}
                                        />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div className="flex flex-wrap justify-end gap-2">
                            <button type="button" disabled={busy} onClick={reset} className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60">
                                Reset
                            </button>
                            <button type="submit" disabled={busy} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                                {busy ? 'Working...' : 'Apply filters'}
                            </button>
                        </div>
                    </form>

                    <div className="p-2">
                        {loading ? (
                            <AccountFullPageLoader text="Loading fees summary..." />
                        ) : rows.length === 0 ? (
                            <div className="p-4"><AccountEmptyState message="No fees summary rows found." /></div>
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Class</AccountTH>
                                        <AccountTH>Phone</AccountTH>
                                        <AccountTH>Status</AccountTH>
                                        <AccountTH className="text-right">Outstanding Fee</AccountTH>
                                        <AccountTH className="text-right">Total Amount</AccountTH>
                                        <AccountTH className="text-right">Fees Amount</AccountTH>
                                        <AccountTH className="text-right">Paid Amount</AccountTH>
                                        <AccountTH className="text-right">Remained Amount</AccountTH>
                                        <AccountTH className="min-w-[180px] max-w-xs text-left">Comment</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={`${row.id}-${index}`}>
                                            <AccountTD>{(pagination?.per_page || 400) * ((pagination?.current_page || 1) - 1) + index + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.first_name} {row.last_name}</AccountTD>
                                            <AccountTD>{row.class_name || '—'}</AccountTD>
                                            <AccountTD>{row.mobile || '—'}</AccountTD>
                                            <AccountTD>
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${String(row.active) === '2' ? 'bg-amber-50 text-amber-700 ring-amber-100' : 'bg-emerald-50 text-emerald-700 ring-emerald-100'}`}>
                                                    {String(row.active) === '2' ? 'Shifted' : 'Active'}
                                                </span>
                                            </AccountTD>
                                            <AccountTD className="text-right tabular-nums">{fmtMoney(row.outstanding_remained_amount)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums font-semibold text-slate-900">{fmtMoney(row.total_assigned_amount)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums">{fmtMoney(row.fees_amount_excluding_outstanding)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums text-emerald-700">{fmtMoney(row.paid_from_collections)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums font-semibold text-red-700">{fmtMoney(row.remained_after_collections)}</AccountTD>
                                            <AccountTD className="max-w-xs whitespace-pre-wrap break-words text-left text-sm text-slate-700">{row.assign_comments || '—'}</AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>

                    {pagination && pagination.last_page > 1 ? (
                        <div className="flex items-center justify-between border-t border-slate-100 px-5 py-4">
                            <button type="button" disabled={busy || pagination.current_page <= 1} onClick={() => goPage(pagination.current_page - 1)} className="rounded-lg border border-slate-200 px-3 py-1.5 text-sm disabled:opacity-40">
                                Previous
                            </button>
                            <span className="text-sm text-slate-600">Page {pagination.current_page} of {pagination.last_page}</span>
                            <button type="button" disabled={busy || pagination.current_page >= pagination.last_page} onClick={() => goPage(pagination.current_page + 1)} className="rounded-lg border border-slate-200 px-3 py-1.5 text-sm disabled:opacity-40">
                                Next
                            </button>
                        </div>
                    ) : null}
                </AccountCard>
            </div>
        </AdminLayout>
    );
}
