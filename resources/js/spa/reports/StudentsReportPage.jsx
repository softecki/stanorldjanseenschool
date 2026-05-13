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

function classOptionsFrom(classes = []) {
    return (classes || [])
        .map((item) => ({ id: String(item?.class?.id ?? item?.classes_id ?? item?.id ?? ''), name: item?.class?.name ?? item?.name ?? '—' }))
        .filter((item) => item.id);
}

function sectionOptionsFrom(sections = []) {
    return (sections || [])
        .map((item) => ({ id: String(item?.section?.id ?? item?.section_id ?? item?.id ?? ''), name: item?.section?.name ?? item?.name ?? '—' }))
        .filter((item) => item.id);
}

function defaultFilters() {
    return {
        class: '0',
        section: '0',
        q: '',
        date_from: '',
        date_to: '',
    };
}

export function StudentsReportPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState(defaultFilters);
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');
    const [searched, setSearched] = useState(false);

    const classOptions = useMemo(() => classOptionsFrom(meta.classes || []), [meta.classes]);
    const sectionOptions = useMemo(() => sectionOptionsFrom(meta.sections || []), [meta.sections]);
    const pagination = meta.pagination || null;

    const applyResponse = (data) => {
        setRows(data?.data || []);
        setMeta(data?.meta || {});
    };

    const loadIndex = useCallback(async (page = 1) => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-students', {
                headers: xhrJson,
                params: { page },
            });
            applyResponse(data);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load students report.');
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
            const { data } = await axios.post(`/report-students/search?page=${page}`, payload, { headers: xhrJson });
            applyResponse(data);
            setSearched(true);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search students report.');
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

    const updateClass = (value) => {
        setFilters((current) => ({ ...current, class: value, section: '0' }));
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-blue-200">Students report</p>
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
                            <p className="text-3xl font-bold">{meta?.totals?.students_count || 0}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-blue-100">Students in report</p>
                        </div>
                    </div>
                </div>

                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}

                <AccountCard>
                    <form onSubmit={search} className="space-y-5 border-b border-slate-100 p-5">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <label className="block text-sm font-medium text-slate-700">
                                Class / group
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.class}
                                    onChange={(e) => updateClass(e.target.value)}
                                >
                                    <option value="0">All classes</option>
                                    <option value="N">New students</option>
                                    <option value="SHIFTED">Shifted students</option>
                                    {classOptions.map((item) => (
                                        <option key={item.id} value={item.id}>{item.name}</option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700">
                                Section
                                <select
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm disabled:bg-slate-50 disabled:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    value={filters.section}
                                    onChange={(e) => setFilters((current) => ({ ...current, section: e.target.value }))}
                                    disabled={['0', 'N', 'SHIFTED'].includes(filters.class)}
                                >
                                    <option value="0">All sections</option>
                                    {sectionOptions.map((item) => (
                                        <option key={item.id} value={item.id}>{item.name}</option>
                                    ))}
                                </select>
                            </label>
                            <label className="block text-sm font-medium text-slate-700 xl:col-span-2">
                                Search
                                <input
                                    className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="Name, admission no, student phone, guardian phone"
                                    value={filters.q}
                                    onChange={(e) => setFilters((current) => ({ ...current, q: e.target.value }))}
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
                            <AccountFullPageLoader text="Loading students report..." />
                        ) : rows.length === 0 ? (
                            <div className="p-4"><AccountEmptyState message="No students found for this filter." /></div>
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Class</AccountTH>
                                        <AccountTH>Section</AccountTH>
                                        <AccountTH>Phone</AccountTH>
                                        <AccountTH>Guardian phone</AccountTH>
                                        <AccountTH>Status</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={`${row.id}-${index}`}>
                                            <AccountTD>{(pagination?.per_page || 50) * ((pagination?.current_page || 1) - 1) + index + 1}</AccountTD>
                                            <AccountTD>
                                                <div className="font-semibold text-slate-900">{row.first_name} {row.last_name}</div>
                                                <div className="text-xs text-slate-500">{row.admission_no || 'No admission no'}</div>
                                            </AccountTD>
                                            <AccountTD>{row.class_name || '—'}</AccountTD>
                                            <AccountTD>{row.section_name || '—'}</AccountTD>
                                            <AccountTD>{row.mobile || '—'}</AccountTD>
                                            <AccountTD>{row.guardian_mobile || '—'}</AccountTD>
                                            <AccountTD>
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${String(row.active) === '2' ? 'bg-amber-50 text-amber-700 ring-amber-100' : 'bg-emerald-50 text-emerald-700 ring-emerald-100'}`}>
                                                    {String(row.active) === '2' ? 'Shifted' : 'Active'}
                                                </span>
                                            </AccountTD>
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
