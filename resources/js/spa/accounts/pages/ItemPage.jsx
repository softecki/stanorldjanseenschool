import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AccountEmptyState, AccountTable, AccountTD, AccountTH, AccountTHead, AccountTR } from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import { AccountFullPageLoader, AccountsPageShell, AccountsSectionHeader, btnPrimary } from '../AccountsModuleShared';
import { IconEdit, IconTrash, uiIconBtnClass } from '../../ui/UiKit';

function rowsFromPayload(data) {
    const payload = data?.data;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload)) return payload;
    return [];
}

export function ItemPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deletingId, setDeletingId] = useState(null);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/item/cash', { headers: xhrJson })
            .then((r) => {
                setRows(rowsFromPayload(r.data));
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load items.'))
            .finally(() => setLoading(false));
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this item?')) return;
        setDeletingId(id);
        setErr('');
        try {
            await axios.delete(`/item/delete/${id}`, { headers: xhrJson });
            setRows((current) => current.filter((row) => String(row.id) !== String(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete item.');
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Items'}
                    subtitle="Item catalog used by product inventory and product sales."
                    actions={
                        <Link to="/item/create" className={btnPrimary}>
                            Create Item
                        </Link>
                    }
                />

                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading items..." /> : null}
                {!loading ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {!rows.length ? (
                            <AccountEmptyState message="No items found." />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH className="w-16">#</AccountTH>
                                        <AccountTH>Name</AccountTH>
                                        <AccountTH>Description</AccountTH>
                                        <AccountTH>Created</AccountTH>
                                        <AccountTH className="text-right">Actions</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={row.id} className="hover:bg-slate-50">
                                            <AccountTD className="tabular-nums text-slate-500">{index + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.name || `Item #${row.id}`}</AccountTD>
                                            <AccountTD className="max-w-xl truncate text-slate-600">{row.description || '-'}</AccountTD>
                                            <AccountTD className="text-slate-600">{row.created_at || '-'}</AccountTD>
                                            <AccountTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link to={`/item/${row.id}/edit`} className={`${uiIconBtnClass} text-blue-700 hover:bg-blue-50`} title="Edit" aria-label="Edit">
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

