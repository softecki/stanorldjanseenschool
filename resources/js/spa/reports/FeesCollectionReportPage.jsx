import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
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

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

function mappedClassOptions(classes = []) {
    return classes
        .map((c) => ({
            id: String(c?.class?.id ?? c?.classes_id ?? c?.id ?? ''),
            name: c?.class?.name ?? c?.name ?? '—',
        }))
        .filter((c) => c.id);
}

function mappedSectionOptions(sections = []) {
    return sections
        .map((s) => ({
            id: String(s?.section?.id ?? s?.section_id ?? s?.id ?? ''),
            name: s?.section?.name ?? s?.name ?? '—',
        }))
        .filter((s) => s.id);
}

function mappedFeeGroups(groups = []) {
    return groups
        .map((g) => ({
            id: String(g?.id ?? ''),
            name: g?.name ?? '—',
        }))
        .filter((g) => g.id);
}

function fmtMoney(n) {
    if (n === null || n === undefined || n === '') return '—';
    const x = Number(n);
    if (Number.isNaN(x)) return String(n);
    return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function statusForRemained(remained, paid) {
    const r = Number(remained || 0);
    const p = Number(paid || 0);
    if (r <= 0) return { label: 'Paid', className: 'bg-emerald-50 text-emerald-700 ring-emerald-100' };
    if (p > 0) return { label: 'Partial', className: 'bg-amber-50 text-amber-700 ring-amber-100' };
    return { label: 'With balance', className: 'bg-red-50 text-red-700 ring-red-100' };
}

function defaultFilters() {
    return {
        class: '0',
        section: '0',
        balance_status: '0',
        date_from: '',
        date_to: '',
        fee_group_id: '0',
        payment_percentage: '',
    };
}

function StatCard({ label, value, hint, tone }) {
    return (
        <div className={`rounded-2xl border p-5 shadow-sm ${tone}`}>
            <p className="text-sm font-medium opacity-80">{label}</p>
            <p className="mt-2 text-2xl font-bold tracking-tight">{value}</p>
            {hint ? <p className="mt-1 text-xs opacity-75">{hint}</p> : null}
        </div>
    );
}

export function FeesCollectionReportPage() {
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [classes, setClasses] = useState([]);
    const [filters, setFilters] = useState(defaultFilters);
    const [hasSearched, setHasSearched] = useState(false);
    const [sectionsLoading, setSectionsLoading] = useState(false);
    const dateFromRef = useRef(null);
    const dateToRef = useRef(null);

    const classList = useMemo(() => mappedClassOptions(classes), [classes]);
    const sectionList = useMemo(() => mappedSectionOptions(meta.sections), [meta.sections]);
    const feeGroupList = useMemo(() => mappedFeeGroups(meta.fee_groups), [meta.fee_groups]);
    const totals = meta.totals || {};
    const percentageOptions = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];

    const applyResponse = (data) => {
        setRows(data?.data || []);
        setMeta(data?.meta || {});
        setClasses(data?.meta?.classes || []);
    };

    const loadIndex = useCallback(async (p = 1) => {
        setErr('');
        setBusy(true);
        try {
            const { data } = await axios.get('/report-fees-collection', {
                headers: xhrJson,
                params: { page: p },
            });
            applyResponse(data);
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
            const payload = {
                ...filters,
                dates: filters.date_from && filters.date_to ? `${filters.date_from} - ${filters.date_to}` : '',
            };
            const { data } = await axios.post(`/report-fees-collection/search?page=${pageOverride}`, payload, { headers: xhrJson });
            applyResponse(data);
            setHasSearched(true);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Search failed.');
        } finally {
            setBusy(false);
        }
    };

    const resetToSummary = async () => {
        setFilters(defaultFilters());
        setHasSearched(false);
        await loadIndex(1);
    };

    const updateFilter = (key, value) => {
        setFilters((current) => ({
            ...current,
            [key]: value,
            ...(key === 'class' ? { section: '0' } : {}),
        }));
    };

    const handleClassChange = async (classId) => {
        updateFilter('class', classId);
        setMeta((current) => ({ ...current, sections: [] }));

        if (!classId || classId === '0') return;

        setSectionsLoading(true);
        try {
            const { data } = await axios.get(`/report-fees-collection/sections/${classId}`, { headers: xhrJson });
            setMeta((current) => ({ ...current, sections: data?.data || [] }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load class sections.');
        } finally {
            setSectionsLoading(false);
        }
    };

    const openDatePicker = (inputRef) => {
        const input = inputRef.current;
        if (!input) return;

        input.focus();
        if (typeof input.showPicker === 'function') {
            try {
                input.showPicker();
            } catch (error) {
                // Some browsers block showPicker in edge cases; focusing still lets the native control work.
            }
        }
    };

    const pagination = meta.pagination;
    const exportPdf = meta.pdf_download_url;
    const exportExcel = meta.excel_download_url;

    const goPage = (p) => {
        if (!pagination || p < 1 || p > pagination.last_page) return;
        if (hasSearched) runSearch(null, p);
        else loadIndex(p);
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

                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Total fees assigned"
                        value={fmtMoney(totals.total_fees_amount)}
                        hint="All assigned fees for matching students"
                        tone="border-blue-100 bg-blue-50 text-blue-900"
                    />
                    <StatCard
                        label="Total paid"
                        value={fmtMoney(totals.paid_amount)}
                        hint="Paid amount recorded on assignments"
                        tone="border-emerald-100 bg-emerald-50 text-emerald-900"
                    />
                    <StatCard
                        label="Remaining amount"
                        value={fmtMoney(totals.remained_amount)}
                        hint="Unpaid balance for matching students"
                        tone="border-red-100 bg-red-50 text-red-900"
                    />
                    <StatCard
                        label="Assigned fee items"
                        value={(totals.assigned_items_count || 0).toLocaleString()}
                        hint="Fee assignment lines included"
                        tone="border-violet-100 bg-violet-50 text-violet-900"
                    />
                </div>

                <AccountCard>
                    <form onSubmit={runSearch} className="space-y-5 border-b border-slate-100 p-5">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 className="text-lg font-semibold text-slate-950">Filter report</h2>
                                <p className="mt-1 text-sm text-slate-500">Use one or more filters, then export the same result to PDF or Excel.</p>
                            </div>
                            {pagination ? (
                                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    Page {pagination.current_page} of {pagination.last_page} · {pagination.total} rows
                                </span>
                            ) : null}
                        </div>

                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <label className="block text-sm font-medium text-slate-700">
                                Class
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.class}
                                    onChange={(e) => handleClassChange(e.target.value)}
                                >
                                    <option value="0">All classes</option>
                                    {classList.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700">
                                Section
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 disabled:bg-slate-50 disabled:text-slate-400"
                                    value={filters.section}
                                    onChange={(e) => updateFilter('section', e.target.value)}
                                    disabled={filters.class === '0' || sectionsLoading}
                                >
                                    <option value="0">{sectionsLoading ? 'Loading sections...' : 'All sections'}</option>
                                    {sectionList.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700">
                                Fee group
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.fee_group_id}
                                    onChange={(e) => updateFilter('fee_group_id', e.target.value)}
                                >
                                    <option value="0">All fee groups</option>
                                    {feeGroupList.map((g) => (
                                        <option key={g.id} value={g.id}>
                                            {g.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700">
                                Balance status
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.balance_status}
                                    onChange={(e) => updateFilter('balance_status', e.target.value)}
                                >
                                    <option value="0">All statuses</option>
                                    <option value="paid">Paid</option>
                                    <option value="partial">Partial paid</option>
                                    <option value="unpaid">Unpaid</option>
                                    <option value="with_balance">With balance</option>
                                </select>
                            </label>
                            <div className="rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-4 shadow-sm md:col-span-2 xl:col-span-2">
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <label className="block cursor-pointer text-sm font-medium text-slate-600">
                                        From
                                        <div
                                            className="mt-1 flex cursor-pointer items-center gap-3 rounded-2xl border border-white bg-white px-4 py-3 shadow-sm ring-1 ring-blue-100 focus-within:ring-2 focus-within:ring-blue-300"
                                            onClick={() => openDatePicker(dateFromRef)}
                                        >
                                            <span className="rounded-lg bg-blue-100 px-2 py-1 text-xs font-bold text-blue-700" aria-hidden>
                                                Calendar
                                            </span>
                                            <input
                                                ref={dateFromRef}
                                                type="date"
                                                className="w-full border-0 bg-transparent text-base text-slate-900 outline-none"
                                                value={filters.date_from}
                                                onChange={(e) => updateFilter('date_from', e.target.value)}
                                            />
                                        </div>
                                    </label>
                                    <label className="block cursor-pointer text-sm font-medium text-slate-600">
                                        To
                                        <div
                                            className="mt-1 flex cursor-pointer items-center gap-3 rounded-2xl border border-white bg-white px-4 py-3 shadow-sm ring-1 ring-blue-100 focus-within:ring-2 focus-within:ring-blue-300"
                                            onClick={() => openDatePicker(dateToRef)}
                                        >
                                            <span className="rounded-lg bg-blue-100 px-2 py-1 text-xs font-bold text-blue-700" aria-hidden>
                                                Calendar
                                            </span>
                                            <input
                                                ref={dateToRef}
                                                type="date"
                                                className="w-full border-0 bg-transparent text-base text-slate-900 outline-none"
                                                value={filters.date_to}
                                                onChange={(e) => updateFilter('date_to', e.target.value)}
                                            />
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <label className="block text-sm font-medium text-slate-700">
                                Paid percentage
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.payment_percentage}
                                    onChange={(e) => updateFilter('payment_percentage', e.target.value)}
                                >
                                    <option value="">All percentages</option>
                                    {percentageOptions.map((percentage) => (
                                        <option key={percentage} value={percentage}>
                                            {percentage}% or less paid
                                        </option>
                                    ))}
                                </select>
                                <span className="mt-1 block text-xs text-slate-500">Calculated from total fees assigned versus paid amount.</span>
                            </label>
                        </div>

                        <div className="flex flex-wrap justify-end gap-2">
                            <button
                                type="button"
                                disabled={busy}
                                onClick={resetToSummary}
                                className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-60"
                            >
                                Reset
                            </button>
                            <button
                                type="submit"
                                disabled={busy}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
                            >
                                {busy ? 'Working…' : 'Apply filters'}
                            </button>
                        </div>
                    </form>

                    <div className="p-2">
                        {loading ? (
                            <AccountFullPageLoader text="Loading report…" />
                        ) : rows.length === 0 ? (
                            <div className="p-4">
                                <AccountEmptyState message="No students found for this filter." />
                            </div>
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Class</AccountTH>
                                        <AccountTH>Section</AccountTH>
                                        <AccountTH className="text-right">Total fees</AccountTH>
                                        <AccountTH className="text-right">Paid</AccountTH>
                                        <AccountTH className="text-right">Remaining</AccountTH>
                                        <AccountTH>Status</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, idx) => {
                                        const st = statusForRemained(row.remained_amount, row.paid_amount);
                                        return (
                                            <AccountTR key={`${row.id ?? 'student'}-${idx}`}>
                                                <AccountTD>{(pagination?.per_page || 20) * ((pagination?.current_page || 1) - 1) + idx + 1}</AccountTD>
                                                <AccountTD>
                                                    <div className="font-semibold text-slate-900">
                                                        {row.first_name} {row.last_name}
                                                    </div>
                                                    <div className="text-xs text-slate-500">{row.mobile || 'No phone'}</div>
                                                </AccountTD>
                                                <AccountTD>{row.class_name || '—'}</AccountTD>
                                                <AccountTD>{row.section_name || '—'}</AccountTD>
                                                <AccountTD className="text-right tabular-nums font-semibold text-slate-900">
                                                    {fmtMoney(row.total_fees_amount)}
                                                </AccountTD>
                                                <AccountTD className="text-right tabular-nums text-emerald-700">{fmtMoney(row.paid_amount)}</AccountTD>
                                                <AccountTD className="text-right tabular-nums font-semibold text-red-700">{fmtMoney(row.remained_amount)}</AccountTD>
                                                <AccountTD>
                                                    <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${st.className}`}>
                                                        {st.label}
                                                    </span>
                                                </AccountTD>
                                            </AccountTR>
                                        );
                                    })}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>

                    {pagination && pagination.last_page > 1 ? (
                        <div className="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                            <button
                                type="button"
                                disabled={busy || pagination.current_page <= 1}
                                onClick={() => goPage(pagination.current_page - 1)}
                                className="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40"
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                disabled={busy || pagination.current_page >= pagination.last_page}
                                onClick={() => goPage(pagination.current_page + 1)}
                                className="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-40"
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
