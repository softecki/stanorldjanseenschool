import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { paginateState } from '../communication/CommunicationModuleShared';
import { xhrJson } from '../api/xhrJson';
import {
    IconPlus,
    UiButton,
    UiButtonLink,
    UiHeadRow,
    UiIconLinkEdit,
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

function staffName(row) {
    const n = `${row.first_name || ''} ${row.last_name || ''}`.trim();
    return n || row.name || '—';
}

function formatErr(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (Array.isArray(d)) return d[0] || 'Request failed.';
    return 'Request failed.';
}

/**
 * Staff list (paginated `GET /users`) with edit, delete, and active/inactive actions.
 */
export function StaffUsersTable({ pageTitle = 'Staff', subtitle }) {
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
    const [busyId, setBusyId] = useState(null);
    const [busyAction, setBusyAction] = useState(null);

    const load = useCallback((p = 1) => {
        setLoading(true);
        setErr('');
        const q = p > 1 ? `?page=${p}` : '';
        return axios
            .get(`/users${q}`, { headers: xhrJson })
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

    const remove = async (id) => {
        if (!window.confirm('Delete this staff member and their login user? This cannot be undone.')) return;
        setBusyId(id);
        setBusyAction('delete');
        setErr('');
        try {
            const { data } = await axios.delete(`/users/delete/${id}`, { headers: xhrJson });
            if (data?.success === false) {
                setErr(data?.message || 'Delete failed.');
                return;
            }
            if (Array.isArray(data) && data[1] && data[1] !== 'Success') {
                setErr(data[0] || 'Delete failed.');
                return;
            }
            setInfo(data?.message || data?.[0] || 'Deleted.');
            setTimeout(() => setInfo(''), 3500);
            await load(page);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setBusyId(null);
            setBusyAction(null);
        }
    };

    const setStatus = async (id, type) => {
        setBusyId(id);
        setBusyAction(type);
        setErr('');
        try {
            await axios.post('/users/status', { type, ids: [id] }, { headers: xhrJson });
            setInfo(type === 'active' ? 'Marked active.' : 'Marked inactive.');
            setTimeout(() => setInfo(''), 2500);
            await load(page);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setBusyId(null);
            setBusyAction(null);
        }
    };

    return (
        <div className="mx-auto max-w-7xl space-y-6 p-6">
            <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-slate-900 to-indigo-950 px-6 py-8 text-white shadow-xl sm:px-8">
                <div className="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">Staff manage</p>
                        <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{meta.title || pageTitle}</h1>
                        {subtitle ? <p className="mt-2 max-w-2xl text-sm text-slate-300">{subtitle}</p> : null}
                    </div>
                    <div className="flex flex-shrink-0 flex-wrap gap-2">
                        <a
                            href="/users/upload"
                            className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"
                        >
                            Upload
                        </a>
                        <UiButtonLink
                            to="/users/create"
                            className="border-0 bg-white text-slate-900 shadow-lg hover:bg-slate-100"
                            leftIcon={<IconPlus className="h-4 w-4" />}
                        >
                            Add staff
                        </UiButtonLink>
                    </div>
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
                <UiPageLoader text="Loading staff…" />
            ) : (
                <>
                    <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-1 shadow-lg shadow-slate-200/50">
                        <UiTableWrap className="overflow-hidden rounded-xl border-0 shadow-none">
                            <UiTable className="text-sm">
                                <UiTHead className="bg-slate-50/90">
                                    <UiHeadRow>
                                        <UiTH className="whitespace-nowrap">Staff ID</UiTH>
                                        <UiTH>Name</UiTH>
                                        <UiTH className="hidden md:table-cell">Role</UiTH>
                                        <UiTH className="hidden lg:table-cell">Department</UiTH>
                                        <UiTH className="hidden lg:table-cell">Designation</UiTH>
                                        <UiTH className="hidden xl:table-cell">Email</UiTH>
                                        <UiTH className="hidden xl:table-cell">Phone</UiTH>
                                        <UiTH>Status</UiTH>
                                        <UiTH className="text-right">Actions</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.length ? (
                                        rows.map((row) => {
                                            const active = Number(row.status) === 1;
                                            const busy = busyId === row.id;
                                            return (
                                                <UiTR key={row.id}>
                                                    <UiTD className="whitespace-nowrap font-mono text-xs text-slate-700">{row.staff_id ?? '—'}</UiTD>
                                                    <UiTD className="font-medium text-slate-900">{staffName(row)}</UiTD>
                                                    <UiTD className="hidden md:table-cell text-slate-700">{row.role?.name ?? '—'}</UiTD>
                                                    <UiTD className="hidden lg:table-cell text-slate-600">{row.department?.name ?? '—'}</UiTD>
                                                    <UiTD className="hidden lg:table-cell text-slate-600">{row.designation?.name ?? '—'}</UiTD>
                                                    <UiTD className="hidden max-w-[10rem] truncate xl:table-cell">{row.email ?? '—'}</UiTD>
                                                    <UiTD className="hidden whitespace-nowrap xl:table-cell">{row.phone ?? '—'}</UiTD>
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
                                                            <UiIconLinkEdit to={`/users/${row.id}/edit`} label="Edit staff" />
                                                            <UiButton
                                                                type="button"
                                                                variant="outline"
                                                                className="h-9 min-w-0 px-2 py-1 text-xs"
                                                                disabled={busy || active}
                                                                onClick={() => setStatus(row.id, 'active')}
                                                                title="Activate"
                                                            >
                                                                {busy && busyAction === 'active' ? <UiInlineLoader /> : 'Active'}
                                                            </UiButton>
                                                            <UiButton
                                                                type="button"
                                                                variant="outline"
                                                                className="h-9 min-w-0 px-2 py-1 text-xs"
                                                                disabled={busy || !active}
                                                                onClick={() => setStatus(row.id, 'inactive')}
                                                                title="Deactivate"
                                                            >
                                                                {busy && busyAction === 'inactive' ? <UiInlineLoader /> : 'Off'}
                                                            </UiButton>
                                                            <UiIconButtonDelete
                                                                onClick={() => remove(row.id)}
                                                                busy={busyId === row.id && busyAction === 'delete'}
                                                                label="Delete staff"
                                                            />
                                                        </div>
                                                    </UiTD>
                                                </UiTR>
                                            );
                                        })
                                    ) : (
                                        <UiTableEmptyRow colSpan={9} message="No staff records yet. Add staff or upload a spreadsheet." />
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
        </div>
    );
}
