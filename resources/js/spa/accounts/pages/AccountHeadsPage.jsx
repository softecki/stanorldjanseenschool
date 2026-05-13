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
import { IconBookOpen, IconCalendar, IconEdit, IconHash, IconReceipt, IconTag, IconTrash, IconView, uiIconBtnClass } from '../../ui/UiKit';

function accountHeadTypeBadge(type) {
    return String(type || '').toLowerCase() === 'income'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-rose-200 bg-rose-50 text-rose-700';
}

function accountHeadStatusBadge(status) {
    return Number(status) === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700';
}

function DetailCard({ icon, label, children, tone = 'bg-slate-50 text-slate-700' }) {
    return (
        <div className="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${tone}`}>{icon}</div>
            <div className="min-w-0">
                <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{label}</p>
                <div className="mt-1 text-sm text-slate-800">{children}</div>
            </div>
        </div>
    );
}

export function AccountHeadsPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deletingId, setDeletingId] = useState(null);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/account-head', { headers: xhrJson })
            .then((r) => {
                const payload = r.data?.data;
                setRows(Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : []);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load account heads.'))
            .finally(() => setLoading(false));
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this account head?')) return;
        setDeletingId(id);
        try {
            await axios.delete(`/account-head/delete/${id}`, { headers: xhrJson });
            setRows((prev) => prev.filter((r) => String(r.id) !== String(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete account head.');
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Account Heads'}
                    subtitle="Classify accounting entries with clear head type and status."
                    actions={
                        <Link to="/account-heads/create" className={btnPrimary}>
                            Create account head
                        </Link>
                    }
                />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading account heads…" /> : null}
                {!loading ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {!rows.length ? (
                            <AccountEmptyState message="No account heads found yet." />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH className="w-16">#</AccountTH>
                                        <AccountTH>Name</AccountTH>
                                        <AccountTH>Type</AccountTH>
                                        <AccountTH>Status</AccountTH>
                                        <AccountTH>Created</AccountTH>
                                        <AccountTH className="text-right">Actions</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, idx) => (
                                        <AccountTR key={row.id} className="hover:bg-slate-50">
                                            <AccountTD className="tabular-nums text-slate-500">{idx + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.name || `Head #${row.id}`}</AccountTD>
                                            <AccountTD>
                                                <span className={`inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${accountHeadTypeBadge(row.type)}`}>
                                                    {String(row.type || 'N/A').toUpperCase()}
                                                </span>
                                            </AccountTD>
                                            <AccountTD>
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${accountHeadStatusBadge(row.status)}`}
                                                >
                                                    {Number(row.status) === 1 ? 'Active' : 'Inactive'}
                                                </span>
                                            </AccountTD>
                                            <AccountTD className="text-slate-600">{row.created_at || '—'}</AccountTD>
                                            <AccountTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        to={`/account-heads/${row.id}`}
                                                        className={`${uiIconBtnClass} text-slate-700 hover:bg-slate-50`}
                                                        title="View"
                                                        aria-label="View"
                                                    >
                                                        <IconView />
                                                    </Link>
                                                    <Link
                                                        to={`/account-heads/${row.id}/edit`}
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

export function AccountHeadViewPage({ Layout }) {
    const { id } = useParams();
    const [row, setRow] = useState(null);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(`/account-head/show/${id}`, { headers: xhrJson })
            .then((r) => {
                setRow(r.data?.data || null);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load account head details.'))
            .finally(() => setLoading(false));
    }, [id]);

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Account Head Details'}
                    subtitle="View full account head metadata."
                    actions={
                        <>
                            <Link to="/account-heads" className={btnGhost}>
                                Back
                            </Link>
                            {row?.id ? (
                                <Link to={`/account-heads/${row.id}/edit`} className={btnPrimary}>
                                    Edit
                                </Link>
                            ) : null}
                        </>
                    }
                />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading account head details…" /> : null}
                {!loading && row ? (
                    <div className="space-y-5">
                        <div className="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-5 text-white shadow-sm">
                            <div className="flex min-w-0 items-center gap-4">
                                <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                                    <IconBookOpen className="h-8 w-8" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-xs font-semibold uppercase tracking-[0.16em] text-slate-300">Account head</p>
                                    <h2 className="mt-1 truncate text-2xl font-semibold">{row.name || '—'}</h2>
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <DetailCard icon={<IconTag className="h-5 w-5" />} label="Head name" tone="bg-indigo-50 text-indigo-700">
                                <span className="text-base font-semibold text-slate-900">{row.name || '—'}</span>
                            </DetailCard>
                            <DetailCard icon={<IconReceipt className="h-5 w-5" />} label="Type" tone="bg-blue-50 text-blue-700">
                                <span className={`inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${accountHeadTypeBadge(row.type)}`}>
                                    {String(row.type || '—').toUpperCase()}
                                </span>
                            </DetailCard>
                            <DetailCard icon={<IconHash className="h-5 w-5" />} label="Status" tone="bg-emerald-50 text-emerald-700">
                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${accountHeadStatusBadge(row.status)}`}>
                                    {Number(row.status) === 1 ? 'Active' : 'Inactive'}
                                </span>
                            </DetailCard>
                            <DetailCard icon={<IconCalendar className="h-5 w-5" />} label="Created" tone="bg-amber-50 text-amber-700">
                                {row.created_at || '—'}
                            </DetailCard>
                            <DetailCard icon={<IconCalendar className="h-5 w-5" />} label="Last updated" tone="bg-sky-50 text-sky-700">
                                {row.updated_at || '—'}
                            </DetailCard>
                        </div>
                    </div>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

