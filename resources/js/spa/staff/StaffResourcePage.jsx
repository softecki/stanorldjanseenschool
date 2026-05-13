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

/**
 * Simple name + status CRUD for staff reference data (`/department`, `/designation`).
 *
 * @param {{ apiBase: string; subtitle: string; heroEyebrow?: string }} props
 */
export function StaffResourcePage({ apiBase, subtitle, heroEyebrow = 'Staff manage' }) {
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
    const [panelLoading, setPanelLoading] = useState(false);
    const [showPanel, setShowPanel] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [form, setForm] = useState({ name: '', status: 1 });
    const [saving, setSaving] = useState(false);
    const [deleteBusyId, setDeleteBusyId] = useState(null);

    const load = useCallback(
        (p = 1) => {
            setLoading(true);
            setErr('');
            const q = p > 1 ? `?page=${p}` : '';
            return axios
                .get(`${apiBase}${q}`, { headers: xhrJson })
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
        },
        [apiBase],
    );

    useEffect(() => {
        load(1);
    }, [load]);

    const closePanel = () => {
        setShowPanel(false);
        setEditingId(null);
        setForm({ name: '', status: 1 });
        setPanelLoading(false);
    };

    const openCreate = async () => {
        setErr('');
        setEditingId(null);
        setShowPanel(true);
        setPanelLoading(true);
        setForm({ name: '', status: 1 });
        try {
            await axios.get(`${apiBase}/create`, { headers: xhrJson });
        } catch (ex) {
            setErr(formatErr(ex));
            closePanel();
        } finally {
            setPanelLoading(false);
        }
    };

    const openEdit = async (id) => {
        setErr('');
        setShowPanel(true);
        setEditingId(id);
        setPanelLoading(true);
        try {
            const { data } = await axios.get(`${apiBase}/edit/${id}`, { headers: xhrJson });
            const row = data?.data;
            setForm({
                name: row?.name ?? '',
                status: row?.status !== undefined && row?.status !== null ? Number(row.status) : 1,
            });
        } catch (ex) {
            setErr(formatErr(ex));
            closePanel();
        } finally {
            setPanelLoading(false);
        }
    };

    const save = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            const body = { name: form.name.trim(), status: Number(form.status) };
            if (editingId) {
                await axios.put(`${apiBase}/update/${editingId}`, body, { headers: xhrJson });
            } else {
                await axios.post(`${apiBase}/store`, body, { headers: xhrJson });
            }
            setInfo(editingId ? 'Updated.' : 'Created.');
            setTimeout(() => setInfo(''), 3000);
            closePanel();
            await load(page);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSaving(false);
        }
    };

    const remove = async (id) => {
        if (!window.confirm('Delete this record?')) return;
        setDeleteBusyId(id);
        setErr('');
        try {
            const { data } = await axios.delete(`${apiBase}/delete/${id}`, { headers: xhrJson });
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

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl space-y-6 p-6">
                <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-indigo-900 to-violet-950 px-6 py-8 text-white shadow-xl sm:px-8">
                    <div className="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200/90">{heroEyebrow}</p>
                            <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{meta.title || 'Staff'}</h1>
                            <p className="mt-2 max-w-xl text-sm text-indigo-100/90">{subtitle}</p>
                        </div>
                        <UiButton
                            type="button"
                            onClick={openCreate}
                            className="border-0 bg-white text-indigo-900 shadow-lg hover:bg-indigo-50"
                            leftIcon={<IconPlus className="h-4 w-4" />}
                        >
                            Add new
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
                    <UiPageLoader text="Loading…" />
                ) : (
                    <>
                        <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-1 shadow-lg">
                            <UiTableWrap className="overflow-hidden rounded-xl border-0 shadow-none">
                                <UiTable className="text-sm">
                                    <UiTHead className="bg-slate-50/90">
                                        <UiHeadRow>
                                            <UiTH>Name</UiTH>
                                            <UiTH>Status</UiTH>
                                            <UiTH className="text-right">Actions</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.length ? (
                                            rows.map((r) => {
                                                const active = Number(r.status) === 1;
                                                return (
                                                    <UiTR key={r.id}>
                                                        <UiTD className="font-semibold text-slate-900">{r.name}</UiTD>
                                                        <UiTD>
                                                            <span
                                                                className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${
                                                                    active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'
                                                                }`}
                                                            >
                                                                {active ? 'Active' : 'Inactive'}
                                                            </span>
                                                        </UiTD>
                                                        <UiTD className="text-right">
                                                            <div className="flex flex-wrap items-center justify-end gap-2">
                                                                <UiButton
                                                                    type="button"
                                                                    variant="outline"
                                                                    className="h-9 px-3 text-xs"
                                                                    onClick={() => openEdit(r.id)}
                                                                >
                                                                    Edit
                                                                </UiButton>
                                                                <UiIconButtonDelete
                                                                    onClick={() => remove(r.id)}
                                                                    busy={deleteBusyId === r.id}
                                                                    label="Delete"
                                                                />
                                                            </div>
                                                        </UiTD>
                                                    </UiTR>
                                                );
                                            })
                                        ) : (
                                            <UiTableEmptyRow colSpan={3} message="No records yet." />
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

                {showPanel ? (
                    <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center" onClick={closePanel} role="presentation">
                        <div
                            className="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
                            onClick={(e) => e.stopPropagation()}
                            role="dialog"
                            aria-modal="true"
                        >
                            <div className="mb-4 flex items-center justify-between gap-2">
                                <h2 className="text-lg font-semibold text-slate-900">{editingId ? 'Edit' : 'Create'}</h2>
                                <button type="button" className="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" onClick={closePanel}>
                                    Close
                                </button>
                            </div>
                            {panelLoading ? (
                                <div className="flex justify-center py-12">
                                    <UiInlineLoader />
                                </div>
                            ) : (
                                <form onSubmit={save} className="space-y-4">
                                    <label className="block text-sm font-medium text-slate-700">
                                        Name
                                        <input
                                            className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            value={form.name}
                                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                                            required
                                        />
                                    </label>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Status
                                        <select
                                            className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            value={form.status}
                                            onChange={(e) => setForm({ ...form, status: Number(e.target.value) })}
                                            required
                                        >
                                            <option value={1}>Active</option>
                                            <option value={0}>Inactive</option>
                                        </select>
                                    </label>
                                    <div className="flex justify-end gap-2 border-t border-slate-100 pt-4">
                                        <UiButton type="button" variant="secondary" onClick={closePanel}>
                                            Cancel
                                        </UiButton>
                                        <UiButton type="submit" disabled={saving}>
                                            {saving ? (
                                                <>
                                                    <UiInlineLoader /> Saving…
                                                </>
                                            ) : editingId ? (
                                                'Update'
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

export function DepartmentPage() {
    return (
        <StaffResourcePage
            apiBase="/department"
            subtitle="Departments group staff and HR settings. Active records stay available for assignments."
        />
    );
}

export function DesignationPage() {
    return (
        <StaffResourcePage
            apiBase="/designation"
            subtitle="Job titles and designations used on staff profiles."
        />
    );
}
