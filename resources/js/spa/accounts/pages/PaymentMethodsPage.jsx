import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import {
    AccountEmptyState,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import {
    AccountFullPageLoader,
    AccountsPageShell,
    AccountsSectionHeader,
    btnGhost,
    btnPrimary,
} from '../AccountsModuleShared';
import { IconEdit, IconTrash, IconView, uiIconBtnClass } from '../../ui/UiKit';

export function PaymentMethodsPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deletingId, setDeletingId] = useState(null);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/payment-methods', { headers: xhrJson })
            .then((r) => {
                const payload = r.data?.data;
                setRows(Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : []);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load payment methods.'))
            .finally(() => setLoading(false));
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this payment method?')) return;
        setDeletingId(id);
        try {
            await axios.delete(`/payment-methods/delete/${id}`, { headers: xhrJson });
            setRows((prev) => prev.filter((r) => String(r.id) !== String(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete payment method.');
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Payment Methods'}
                    subtitle="Manage available payment channels with rich details and status visibility."
                    actions={
                        <Link to="/payment-methods/create" className={btnPrimary}>
                            Create payment method
                        </Link>
                    }
                />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading payment methods…" /> : null}
                {!loading ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {!rows.length ? (
                            <AccountEmptyState message="No payment methods found yet." />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH className="w-16">#</AccountTH>
                                        <AccountTH>Name</AccountTH>
                                        <AccountTH>Status</AccountTH>
                                        <AccountTH>Description</AccountTH>
                                        <AccountTH>Created</AccountTH>
                                        <AccountTH className="text-right">Actions</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, idx) => (
                                        <AccountTR key={row.id} className="hover:bg-slate-50">
                                            <AccountTD className="tabular-nums text-slate-500">{idx + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.name || `Method #${row.id}`}</AccountTD>
                                            <AccountTD>
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${
                                                        Number(row.is_active) === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700'
                                                    }`}
                                                >
                                                    {Number(row.is_active) === 1 ? 'Active' : 'Inactive'}
                                                </span>
                                            </AccountTD>
                                            <AccountTD className="max-w-xs truncate text-slate-600">{row.description || '—'}</AccountTD>
                                            <AccountTD className="whitespace-nowrap text-slate-600">{row.created_at || '—'}</AccountTD>
                                            <AccountTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        to={`/payment-methods/${row.id}`}
                                                        className={`${uiIconBtnClass} text-slate-700 hover:bg-slate-50`}
                                                        title="View"
                                                        aria-label="View"
                                                    >
                                                        <IconView />
                                                    </Link>
                                                    <Link
                                                        to={`/payment-methods/${row.id}/edit`}
                                                        className={`${uiIconBtnClass} text-blue-700 hover:bg-blue-50`}
                                                        title="Edit"
                                                        aria-label="Edit"
                                                    >
                                                        <IconEdit />
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        onClick={() => remove(row.id)}
                                                        disabled={deletingId === row.id}
                                                        className={`${uiIconBtnClass} text-rose-700 hover:bg-rose-50 disabled:opacity-50`}
                                                        title="Delete"
                                                        aria-label="Delete"
                                                    >
                                                        <IconTrash />
                                                    </button>
                                                </div>
                                            </AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

export function PaymentMethodViewPage({ Layout }) {
    const { id } = useParams();
    const [row, setRow] = useState(null);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(`/payment-methods/show/${id}`, { headers: xhrJson })
            .then((r) => {
                setRow(r.data?.data || null);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load payment method details.'))
            .finally(() => setLoading(false));
    }, [id]);

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Payment Method Details'}
                    subtitle="Review full payment method data."
                    actions={
                        <>
                            <Link to="/payment-methods" className={btnGhost}>
                                Back
                            </Link>
                            {row?.id ? (
                                <Link to={`/payment-methods/${row.id}/edit`} className={btnPrimary}>
                                    Edit
                                </Link>
                            ) : null}
                        </>
                    }
                />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading payment method details…" /> : null}
                {!loading && row ? (
                    <div className="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Name</p>
                            <p className="mt-1 text-base font-semibold text-slate-900">{row.name || '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                            <p className="mt-1 text-sm text-slate-800">{Number(row.is_active) === 1 ? 'Active' : 'Inactive'}</p>
                        </div>
                        <div className="sm:col-span-2">
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Description</p>
                            <p className="mt-1 whitespace-pre-wrap text-sm text-slate-700">{row.description || '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Created</p>
                            <p className="mt-1 text-sm text-slate-700">{row.created_at || '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Last updated</p>
                            <p className="mt-1 text-sm text-slate-700">{row.updated_at || '—'}</p>
                        </div>
                    </div>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

