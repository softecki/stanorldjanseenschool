import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { paginateState } from '../communication/CommunicationModuleShared';
import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import {
    IconPlus,
    UiButton,
    UiHeadRow,
    UiIconButtonDelete,
    UiInlineLoader,
    UiPageLoader,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';

function formatErr(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (d.errors && typeof d.errors === 'object') {
        return Object.entries(d.errors)
            .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(' ') : v}`)
            .join(' · ');
    }
    return 'Request failed.';
}

const API = '/salary';

/** Salary batch list + run batch + payment status (maps legacy `/salary` routes). */
export function StaffSalaryBatchPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [from, setFrom] = useState(null);
    const [to, setTo] = useState(null);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [loading, setLoading] = useState(true);
    const [showBatchPanel, setShowBatchPanel] = useState(false);
    const [batchName, setBatchName] = useState('');
    const [batchMetaLoading, setBatchMetaLoading] = useState(false);
    const [runningBatch, setRunningBatch] = useState(false);
    const [showEditPanel, setShowEditPanel] = useState(false);
    const [editRow, setEditRow] = useState(null);
    const [editStatus, setEditStatus] = useState('0');
    const [editLoading, setEditLoading] = useState(false);
    const [savingEdit, setSavingEdit] = useState(false);
    const [deleteBusyId, setDeleteBusyId] = useState(null);

    const load = useCallback((p = 1) => {
        setLoading(true);
        setErr('');
        const q = p > 1 ? `?page=${p}` : '';
        return axios
            .get(`${API}${q}`, { headers: xhrJson })
            .then((r) => {
                const st = paginateState(r);
                setRows(st.rows);
                setPage(st.page);
                setLastPage(st.lastPage);
                setTotal(st.total);
                setFrom(st.from);
                setTo(st.to);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(formatErr(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load(1);
    }, [load]);

    const openBatchPanel = async () => {
        setErr('');
        setShowBatchPanel(true);
        setBatchMetaLoading(true);
        try {
            const { data } = await axios.get(`${API}/create`, { headers: xhrJson });
            setBatchName(String(data?.meta?.batch_date ?? ''));
        } catch (ex) {
            setErr(formatErr(ex));
            setShowBatchPanel(false);
        } finally {
            setBatchMetaLoading(false);
        }
    };

    const runBatch = async (e) => {
        e.preventDefault();
        setErr('');
        setRunningBatch(true);
        try {
            await axios.post(
                `${API}/store`,
                { name: batchName.trim() },
                { headers: { ...xhrJson, 'Content-Type': 'application/json' } },
            );
            setInfo('Batch processed for current month (new rows only where missing).');
            setTimeout(() => setInfo(''), 4000);
            setShowBatchPanel(false);
            await load(page);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setRunningBatch(false);
        }
    };

    const openEdit = async (id) => {
        setErr('');
        setShowEditPanel(true);
        setEditLoading(true);
        setEditRow(null);
        try {
            const { data } = await axios.get(`${API}/edit/${id}`, { headers: xhrJson });
            const row = data?.data;
            setEditRow(row);
            setEditStatus(row?.payment_status != null ? String(row.payment_status) : '0');
        } catch (ex) {
            setErr(formatErr(ex));
            setShowEditPanel(false);
        } finally {
            setEditLoading(false);
        }
    };

    const saveEdit = async (e) => {
        e.preventDefault();
        if (!editRow?.id) return;
        setErr('');
        setSavingEdit(true);
        try {
            await axios.put(
                `${API}/update/${editRow.id}`,
                { status: editStatus },
                { headers: { ...xhrJson, 'Content-Type': 'application/json' } },
            );
            setInfo('Payment status updated.');
            setTimeout(() => setInfo(''), 3000);
            setShowEditPanel(false);
            setEditRow(null);
            await load(page);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSavingEdit(false);
        }
    };

    const remove = async (id) => {
        if (!window.confirm('Delete this salary payment row?')) return;
        setDeleteBusyId(id);
        setErr('');
        try {
            const { data } = await axios.delete(`${API}/delete/${id}`, { headers: xhrJson });
            if (data?.success === false) {
                setErr(data?.message || 'Delete failed.');
                return;
            }
            if (Array.isArray(data) && data[1] === 'error') {
                setErr(data[0] || 'Delete failed.');
                return;
            }
            setInfo('Deleted.');
            setTimeout(() => setInfo(''), 3000);
            await load(page);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setDeleteBusyId(null);
        }
    };

    const isPaid = (r) => Number(r.payment_status) === 1;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl space-y-6 p-6">
                <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-indigo-900 to-violet-950 px-6 py-8 text-white shadow-xl sm:px-8">
                    <div className="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200/90">Staff manage</p>
                            <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{meta.title || 'Salary batch'}</h1>
                            <p className="mt-2 max-w-xl text-sm text-indigo-100/90">
                                Run a batch for the current calendar month (creates unpaid rows per staff member when none exist). Update payment status
                                or remove mistaken rows.
                            </p>
                        </div>
                        <UiButton
                            type="button"
                            onClick={openBatchPanel}
                            className="border-0 bg-white text-indigo-900 shadow-lg hover:bg-indigo-50"
                            leftIcon={<IconPlus className="h-4 w-4" />}
                        >
                            Run batch
                        </UiButton>
                    </div>
                </section>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {info ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        {info}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading payments…" />
                ) : (
                    <>
                        <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-1 shadow-lg">
                            <UiTableWrap className="overflow-hidden rounded-xl border-0 shadow-none">
                                <UiTable className="text-sm">
                                    <UiTHead className="bg-slate-50/90">
                                        <UiHeadRow>
                                            <UiTH>Staff</UiTH>
                                            <UiTH>Amount</UiTH>
                                            <UiTH>Period</UiTH>
                                            <UiTH>Batch</UiTH>
                                            <UiTH>Status</UiTH>
                                            <UiTH className="text-right">Actions</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.length ? (
                                            rows.map((r) => (
                                                <UiTR key={r.id}>
                                                    <UiTD className="font-medium text-slate-900">
                                                        {[r.first_name, r.last_name].filter(Boolean).join(' ') || '—'}
                                                    </UiTD>
                                                    <UiTD>{r.amount ?? '—'}</UiTD>
                                                    <UiTD>
                                                        {r.month ?? '—'}/{r.year ?? '—'}
                                                    </UiTD>
                                                    <UiTD>{r.batchnumber ?? '—'}</UiTD>
                                                    <UiTD>
                                                        <span
                                                            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                                                                isPaid(r) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'
                                                            }`}
                                                        >
                                                            {isPaid(r) ? 'Paid' : 'Unpaid'}
                                                        </span>
                                                    </UiTD>
                                                    <UiTD className="text-right">
                                                        <div className="flex flex-wrap items-center justify-end gap-2">
                                                            {!isPaid(r) ? (
                                                                <>
                                                                    <UiButton type="button" variant="outline" className="h-9 px-3 text-xs" onClick={() => openEdit(r.id)}>
                                                                        Update
                                                                    </UiButton>
                                                                    <UiIconButtonDelete onClick={() => remove(r.id)} busy={deleteBusyId === r.id} label="Delete payment" />
                                                                </>
                                                            ) : (
                                                                <span className="text-xs text-slate-500">Paid</span>
                                                            )}
                                                        </div>
                                                    </UiTD>
                                                </UiTR>
                                            ))
                                        ) : (
                                            <UiTableEmptyRow colSpan={6} message="No salary payment rows yet." />
                                        )}
                                    </UiTBody>
                                </UiTable>
                            </UiTableWrap>
                        </div>

                        {total > 0 && from != null && to != null ? (
                            <p className="text-center text-xs text-slate-500">
                                Showing <span className="font-medium text-slate-700">{from}</span>–
                                <span className="font-medium text-slate-700">{to}</span> of{' '}
                                <span className="font-medium text-slate-700">{total}</span>
                            </p>
                        ) : null}

                        <UiPager
                            page={page}
                            lastPage={lastPage}
                            onPrev={() => load(page - 1)}
                            onNext={() => load(page + 1)}
                            className="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3"
                        />
                    </>
                )}

                {showBatchPanel ? (
                    <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center" onClick={() => setShowBatchPanel(false)} role="presentation">
                        <div
                            className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
                            onClick={(e) => e.stopPropagation()}
                            role="dialog"
                            aria-modal="true"
                        >
                            <h2 className="text-lg font-semibold text-slate-900">Run salary batch</h2>
                            <p className="mt-2 text-sm text-slate-600">
                                Creates payment rows for every staff member for the current month when a row does not already exist. Batch code is stored
                                on each new row.
                            </p>
                            {batchMetaLoading ? (
                                <div className="flex justify-center py-10">
                                    <UiInlineLoader />
                                </div>
                            ) : (
                                <form onSubmit={runBatch} className="mt-4 space-y-4">
                                    <label className="block text-sm font-medium text-slate-700">
                                        Batch code (e.g. YYYYMM)
                                        <input
                                            className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            value={batchName}
                                            onChange={(e) => setBatchName(e.target.value)}
                                            required
                                        />
                                    </label>
                                    <div className="flex justify-end gap-2">
                                        <UiButton type="button" variant="secondary" onClick={() => setShowBatchPanel(false)}>
                                            Cancel
                                        </UiButton>
                                        <UiButton type="submit" disabled={runningBatch}>
                                            {runningBatch ? (
                                                <>
                                                    <UiInlineLoader /> Processing…
                                                </>
                                            ) : (
                                                'Run batch'
                                            )}
                                        </UiButton>
                                    </div>
                                </form>
                            )}
                        </div>
                    </div>
                ) : null}

                {showEditPanel ? (
                    <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center" onClick={() => setShowEditPanel(false)} role="presentation">
                        <div
                            className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
                            onClick={(e) => e.stopPropagation()}
                            role="dialog"
                            aria-modal="true"
                        >
                            <h2 className="text-lg font-semibold text-slate-900">Payment status</h2>
                            {editLoading ? (
                                <div className="flex justify-center py-10">
                                    <UiInlineLoader />
                                </div>
                            ) : (
                                <form onSubmit={saveEdit} className="mt-4 space-y-4">
                                    <p className="text-sm text-slate-600">
                                        {[editRow?.first_name, editRow?.last_name].filter(Boolean).join(' ')} — amount {editRow?.amount ?? '—'}
                                    </p>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Status
                                        <select
                                            className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            value={editStatus}
                                            onChange={(e) => setEditStatus(e.target.value)}
                                        >
                                            <option value="0">Unpaid</option>
                                            <option value="1">Paid</option>
                                        </select>
                                    </label>
                                    <div className="flex justify-end gap-2">
                                        <UiButton type="button" variant="secondary" onClick={() => setShowEditPanel(false)}>
                                            Cancel
                                        </UiButton>
                                        <UiButton type="submit" disabled={savingEdit}>
                                            {savingEdit ? (
                                                <>
                                                    <UiInlineLoader /> Saving…
                                                </>
                                            ) : (
                                                'Save'
                                            )}
                                        </UiButton>
                                    </div>
                                </form>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}
