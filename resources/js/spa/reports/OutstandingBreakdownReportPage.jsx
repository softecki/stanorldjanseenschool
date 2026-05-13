import React, { useEffect, useMemo, useState } from 'react';
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
        .map((c) => ({ id: String(c?.class?.id ?? c?.id ?? ''), name: c?.class?.name ?? c?.name ?? '—' }))
        .filter((x) => x.id);
}

function fmtMoney(n) {
    const x = Number(n ?? 0);
    if (!Number.isFinite(x)) return '0.00';
    return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export function OutstandingBreakdownReportPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');
    const [filters, setFilters] = useState({ class_id: '', fee_groups: [] });

    const classOptions = useMemo(() => classOptionsFrom(meta.classes || []), [meta.classes]);
    const feeGroups = useMemo(() => meta.fee_groups || [], [meta.fee_groups]);
    const selectedFeeGroups = filters.fee_groups.length ? filters.fee_groups : (meta.selected_fee_groups || []).map(String);
    const pagination = meta.pagination || null;
    const totals = meta.totals || {};
    const exportPdf = meta.pdf_download_url;
    const exportExcel = meta.excel_download_url;

    const load = async (page = 1, nextFilters = filters) => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-outstanding-breakdown', {
                headers: xhrJson,
                params: {
                    page,
                    class_id: nextFilters.class_id || '',
                    fee_groups: (nextFilters.fee_groups || []).join(','),
                },
            });
            setRows(data?.data || []);
            setMeta(data?.meta || {});
            setFilters((current) => ({
                ...current,
                fee_groups: current.fee_groups.length ? current.fee_groups : (data?.meta?.selected_fee_groups || []).map(String),
            }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load break down report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    useEffect(() => {
        load(1, { class_id: '', fee_groups: [] });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const onSearch = async (e) => {
        e.preventDefault();
        await load(1, filters);
    };

    const goPage = async (p) => {
        if (!pagination) return;
        if (p < 1 || p > pagination.last_page) return;
        await load(p, filters);
    };

    const toggleFeeGroup = (id) => {
        const value = String(id);
        setFilters((current) => {
            const exists = current.fee_groups.includes(value);
            return {
                ...current,
                fee_groups: exists ? current.fee_groups.filter((item) => item !== value) : [...current.fee_groups, value],
            };
        });
    };

    const findBreakdown = (row, feeGroupId) => {
        return (row.fee_breakdowns || []).find((item) => String(item.fee_group_id) === String(feeGroupId)) || {};
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-blue-200">Fees report</p>
                            <div className="mt-5 flex flex-wrap gap-2">
                                {exportPdf ? (
                                    <a href={exportPdf} className="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-blue-50">
                                        Print PDF
                                    </a>
                                ) : null}
                                {exportExcel ? (
                                    <a href={exportExcel} className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
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

                {err ? <p className="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{err}</p> : null}

                <div className="grid gap-4 md:grid-cols-3">
                    <div className="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-blue-900 shadow-sm">
                        <p className="text-sm font-medium opacity-80">Total fees</p>
                        <p className="mt-2 text-2xl font-bold">{fmtMoney(totals.total_fees_amount)}</p>
                    </div>
                    <div className="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 text-emerald-900 shadow-sm">
                        <p className="text-sm font-medium opacity-80">Total paid</p>
                        <p className="mt-2 text-2xl font-bold">{fmtMoney(totals.total_paid_amount)}</p>
                    </div>
                    <div className="rounded-2xl border border-red-100 bg-red-50 p-5 text-red-900 shadow-sm">
                        <p className="text-sm font-medium opacity-80">Total remained</p>
                        <p className="mt-2 text-2xl font-bold">{fmtMoney(totals.total_remained_amount)}</p>
                    </div>
                </div>

                <AccountCard>
                    <form onSubmit={onSearch} className="space-y-5 border-b border-slate-100 p-5">
                        <div className="grid gap-4 md:grid-cols-3">
                            <label className="block text-sm font-medium text-slate-700">
                                Class
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.class_id}
                                    onChange={(e) => setFilters((f) => ({ ...f, class_id: e.target.value }))}
                                >
                                    <option value="">All classes</option>
                                    {classOptions.map((c) => (
                                        <option key={c.id} value={c.id}>{c.name}</option>
                                    ))}
                                </select>
                            </label>
                            <div className="flex items-end">
                                <button
                                    type="submit"
                                    disabled={busy}
                                    className="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                                >
                                    {busy ? 'Loading...' : 'Apply filters'}
                                </button>
                            </div>
                        </div>

                        <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 className="text-sm font-semibold text-slate-900">Fee groups</h2>
                                    <p className="mt-1 text-xs text-slate-500">Checked fee groups become columns in the report table.</p>
                                </div>
                                <button
                                    type="button"
                                    className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                    onClick={() => setFilters((f) => ({ ...f, fee_groups: feeGroups.map((item) => String(item.id)) }))}
                                >
                                    Select all
                                </button>
                            </div>
                            <div className="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                {feeGroups.map((feeGroup) => (
                                    <label key={feeGroup.id} className="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm">
                                        <input
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                            checked={selectedFeeGroups.includes(String(feeGroup.id))}
                                            onChange={() => toggleFeeGroup(feeGroup.id)}
                                        />
                                        <span>{feeGroup.name}</span>
                                    </label>
                                ))}
                            </div>
                        </div>
                    </form>

                    <div className="px-4 py-3 text-sm text-gray-600">
                        Page {pagination?.current_page || 1} of {pagination?.last_page || 1}
                        <span className="mx-2">.</span>
                        Rows: <span className="font-semibold text-gray-900">{pagination?.total ?? 0}</span>
                    </div>

                    <div className="p-2">
                        {loading ? (
                            <AccountFullPageLoader text="Loading break down report..." />
                        ) : rows.length === 0 ? (
                            <div className="p-4"><AccountEmptyState message="No break down rows found." /></div>
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Phone</AccountTH>
                                        <AccountTH>Class</AccountTH>
                                        {feeGroups
                                            .filter((feeGroup) => selectedFeeGroups.includes(String(feeGroup.id)))
                                            .map((feeGroup) => (
                                                <React.Fragment key={feeGroup.id}>
                                                    <AccountTH className="text-right">{feeGroup.name} fees</AccountTH>
                                                    <AccountTH className="text-right">{feeGroup.name} paid</AccountTH>
                                                    <AccountTH className="text-right">{feeGroup.name} remained</AccountTH>
                                                </React.Fragment>
                                            ))}
                                        <AccountTH className="text-right">Total fees</AccountTH>
                                        <AccountTH className="text-right">Total paid</AccountTH>
                                        <AccountTH className="text-right">Total remained</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-gray-100 bg-white">
                                    {rows.map((row, idx) => (
                                        <AccountTR key={`${row.id}-${idx}`}>
                                            <AccountTD>{(pagination?.per_page || 100) * ((pagination?.current_page || 1) - 1) + idx + 1}</AccountTD>
                                            <AccountTD className="font-medium text-gray-900">{row.first_name} {row.last_name}</AccountTD>
                                            <AccountTD>{row.mobile || '—'}</AccountTD>
                                            <AccountTD>{row.class_name || '—'}</AccountTD>
                                            {feeGroups
                                                .filter((feeGroup) => selectedFeeGroups.includes(String(feeGroup.id)))
                                                .map((feeGroup) => {
                                                    const breakdown = findBreakdown(row, feeGroup.id);
                                                    return (
                                                        <React.Fragment key={feeGroup.id}>
                                                            <AccountTD className="text-right tabular-nums">{fmtMoney(breakdown.fees_amount)}</AccountTD>
                                                            <AccountTD className="text-right tabular-nums text-emerald-700">{fmtMoney(breakdown.paid_amount)}</AccountTD>
                                                            <AccountTD className="text-right tabular-nums font-semibold text-red-700">{fmtMoney(breakdown.remained_amount)}</AccountTD>
                                                        </React.Fragment>
                                                    );
                                                })}
                                            <AccountTD className="text-right tabular-nums font-semibold text-slate-900">{fmtMoney(row.total_fees_amount)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums font-semibold text-emerald-700">{fmtMoney(row.total_paid_amount)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums font-semibold text-red-700">{fmtMoney(row.total_remained_amount)}</AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>

                    {pagination && pagination.last_page > 1 ? (
                        <div className="flex items-center justify-between border-t border-gray-100 px-4 py-3">
                            <button type="button" onClick={() => goPage((pagination.current_page || 1) - 1)} disabled={busy || pagination.current_page <= 1} className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm disabled:opacity-40">
                                Previous
                            </button>
                            <span className="text-sm text-gray-600">Page {pagination.current_page} of {pagination.last_page}</span>
                            <button type="button" onClick={() => goPage((pagination.current_page || 1) + 1)} disabled={busy || pagination.current_page >= pagination.last_page} className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm disabled:opacity-40">
                                Next
                            </button>
                        </div>
                    ) : null}
                </AccountCard>
            </div>
        </AdminLayout>
    );
}

