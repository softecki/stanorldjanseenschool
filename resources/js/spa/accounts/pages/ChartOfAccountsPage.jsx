import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { AccountEmptyState, AccountTable, AccountTD, AccountTH, AccountTHead, AccountTR } from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import { AccountFullPageLoader, AccountsPageShell, AccountsSectionHeader, btnGhost, btnPrimary } from '../AccountsModuleShared';
import { IconEdit, IconTrash, IconView, uiIconBtnClass } from '../../ui/UiKit';

function typeBadge(type) {
    const t = String(type || '').toLowerCase();
    if (t === 'income') return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    if (t === 'expense') return 'bg-rose-50 text-rose-700 border-rose-200';
    if (t === 'asset') return 'bg-blue-50 text-blue-700 border-blue-200';
    if (t === 'liability') return 'bg-amber-50 text-amber-700 border-amber-200';
    return 'bg-slate-50 text-slate-700 border-slate-200';
}

function statusBadge(status) {
    return Number(status) === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700';
}

function formatDateTime(value) {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return String(value);
    return d.toLocaleString();
}

export function ChartOfAccountsPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [pager, setPager] = useState({ current_page: 1, last_page: 1, total: 0, per_page: 0 });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deletingId, setDeletingId] = useState(null);
    const [page, setPage] = useState(1);
    const [filters, setFilters] = useState({ q: '', type: '', status: '' });
    const [appliedFilters, setAppliedFilters] = useState({ q: '', type: '', status: '' });

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/chart-of-accounts', { headers: xhrJson, params: { page, ...appliedFilters } })
            .then((r) => {
                const payload = r.data?.data;
                setRows(Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : []);
                setMeta(r.data?.meta || {});
                setPager({
                    current_page: Number(payload?.current_page || 1),
                    last_page: Number(payload?.last_page || 1),
                    total: Number(payload?.total || 0),
                    per_page: Number(payload?.per_page || 0),
                });
                const m = r.data?.meta?.filters || {};
                setFilters((prev) => ({ ...prev, ...m }));
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load chart of accounts.'))
            .finally(() => setLoading(false));
    }, [page, appliedFilters]);

    const remove = async (id) => {
        if (!window.confirm('Delete this account?')) return;
        setDeletingId(id);
        try {
            await axios.delete(`/chart-of-accounts/delete/${id}`, { headers: xhrJson });
            setRows((prev) => prev.filter((r) => String(r.id) !== String(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete account.');
        } finally {
            setDeletingId(null);
        }
    };
    const pagination = pager;
    const firstIndex = (Number(pagination.current_page || 1) - 1) * Number(pagination.per_page || 0);

    const onSearch = (e) => {
        e.preventDefault();
        setAppliedFilters({ ...filters });
        setPage(1);
    };

    const onClear = () => {
        const clear = { q: '', type: '', status: '' };
        setFilters(clear);
        setAppliedFilters(clear);
        setPage(1);
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Chart of Accounts'}
                    subtitle="Ledger structure with clear type/status tags and fast actions."
                    actions={
                        <Link to="/chart-of-accounts/create" className={btnPrimary}>
                            Create account
                        </Link>
                    }
                />
                <form onSubmit={onSearch} className="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-5">
                    <input
                        className="rounded-lg border border-slate-200 px-3 py-2 text-sm"
                        placeholder="Search name, code, or description"
                        value={filters.q}
                        onChange={(e) => setFilters((s) => ({ ...s, q: e.target.value }))}
                    />
                    <select
                        className="rounded-lg border border-slate-200 px-3 py-2 text-sm"
                        value={filters.type}
                        onChange={(e) => setFilters((s) => ({ ...s, type: e.target.value }))}
                    >
                        <option value="">All types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                        <option value="asset">Asset</option>
                        <option value="liability">Liability</option>
                    </select>
                    <select
                        className="rounded-lg border border-slate-200 px-3 py-2 text-sm"
                        value={filters.status}
                        onChange={(e) => setFilters((s) => ({ ...s, status: e.target.value }))}
                    >
                        <option value="">All statuses</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <button type="submit" className={btnPrimary}>
                        Search
                    </button>
                    <button type="button" onClick={onClear} className={btnGhost}>
                        Clear
                    </button>
                </form>
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading chart of accounts…" /> : null}
                {!loading ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {!rows.length ? (
                            <AccountEmptyState message="No accounts found yet." />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH className="w-16">#</AccountTH>
                                        <AccountTH>Account</AccountTH>
                                        <AccountTH>Code</AccountTH>
                                        <AccountTH>Type</AccountTH>
                                        <AccountTH>Parent</AccountTH>
                                        <AccountTH>Children</AccountTH>
                                        <AccountTH>Status</AccountTH>
                                        <AccountTH>Description</AccountTH>
                                        <AccountTH>Created</AccountTH>
                                        <AccountTH>Updated</AccountTH>
                                        <AccountTH className="text-right">Actions</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, idx) => (
                                        <AccountTR key={row.id} className="hover:bg-slate-50">
                                            <AccountTD className="tabular-nums text-slate-500">{firstIndex + idx + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.name || `Account #${row.id}`}</AccountTD>
                                            <AccountTD className="font-mono text-xs text-slate-700">{row.code || '—'}</AccountTD>
                                            <AccountTD>
                                                <span className={`inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${typeBadge(row.type)}`}>
                                                    {String(row.type || 'N/A').toUpperCase()}
                                                </span>
                                            </AccountTD>
                                            <AccountTD className="text-slate-600">{row?.parent?.name || '—'}</AccountTD>
                                            <AccountTD className="text-slate-600">{Number(row?.children_count || 0)}</AccountTD>
                                            <AccountTD>
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${statusBadge(row.status)}`}>
                                                    {Number(row.status) === 1 ? 'Active' : 'Inactive'}
                                                </span>
                                            </AccountTD>
                                            <AccountTD className="max-w-xs truncate text-slate-600">{row.description || '—'}</AccountTD>
                                            <AccountTD className="text-xs text-slate-600">{formatDateTime(row.created_at)}</AccountTD>
                                            <AccountTD className="text-xs text-slate-600">{formatDateTime(row.updated_at)}</AccountTD>
                                            <AccountTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link to={`/chart-of-accounts/${row.id}`} className={`${uiIconBtnClass} text-slate-700 hover:bg-slate-50`} title="View" aria-label="View">
                                                        <IconView />
                                                    </Link>
                                                    <Link to={`/chart-of-accounts/${row.id}/edit`} className={`${uiIconBtnClass} text-blue-700 hover:bg-blue-50`} title="Edit" aria-label="Edit">
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
                {!loading && pagination.last_page > 1 ? (
                    <div className="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                        <span>
                            Page {pagination.current_page} of {pagination.last_page} ({pagination.total} total)
                        </span>
                        <div className="flex gap-2">
                            <button
                                type="button"
                                className={btnGhost}
                                disabled={pagination.current_page <= 1}
                                onClick={() => setPage((p) => Math.max(1, p - 1))}
                            >
                                Previous
                            </button>
                            <button
                                type="button"
                                className={btnGhost}
                                disabled={pagination.current_page >= pagination.last_page}
                                onClick={() => setPage((p) => p + 1)}
                            >
                                Next
                            </button>
                        </div>
                    </div>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

export function ChartOfAccountsViewPage({ Layout }) {
    const { id } = useParams();
    const [row, setRow] = useState(null);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(`/chart-of-accounts/show/${id}`, { headers: xhrJson })
            .then((r) => {
                setRow(r.data?.data || null);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load account details.'))
            .finally(() => setLoading(false));
    }, [id]);

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Account Details'}
                    subtitle="Full chart account details including hierarchy and metadata."
                    actions={
                        <>
                            <Link to="/chart-of-accounts" className={btnGhost}>
                                Back
                            </Link>
                            {row?.id ? (
                                <Link to={`/chart-of-accounts/${row.id}/edit`} className={btnPrimary}>
                                    Edit
                                </Link>
                            ) : null}
                        </>
                    }
                />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading account details…" /> : null}
                {!loading && row ? (
                    <div className="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Account name</p>
                            <p className="mt-1 text-base font-semibold text-slate-900">{row.name || '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Code</p>
                            <p className="mt-1 font-mono text-sm text-slate-800">{row.code || '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Type</p>
                            <p className="mt-1 text-sm text-slate-800">{String(row.type || '—').toUpperCase()}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                            <p className="mt-1 text-sm text-slate-800">{Number(row.status) === 1 ? 'Active' : 'Inactive'}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Parent account</p>
                            <p className="mt-1 text-sm text-slate-800">{row?.parent?.name || '—'}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Children count</p>
                            <p className="mt-1 text-sm text-slate-800">{Array.isArray(row.children) ? row.children.length : 0}</p>
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

