import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AccountEmptyState, AccountTable, AccountTD, AccountTH, AccountTHead, AccountTR } from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import { AccountFullPageLoader, AccountsPageShell, AccountsSectionHeader, btnGhost, btnPrimary } from '../AccountsModuleShared';
import { IconEdit, IconTrash, uiIconBtnClass } from '../../ui/UiKit';

function rowsFromPayload(data) {
    const payload = data?.data;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload)) return payload;
    return [];
}

function money(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) return '0.00';
    return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

export function ProductPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deletingId, setDeletingId] = useState(null);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/product/cash', { headers: xhrJson })
            .then((r) => {
                setRows(rowsFromPayload(r.data));
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load products.'))
            .finally(() => setLoading(false));
    }, []);

    const totals = useMemo(
        () =>
            rows.reduce(
                (sum, row) => ({
                    quantity: sum.quantity + (Number(row.quantity) || 0),
                    remained: sum.remained + (Number(row.remained) || 0),
                    sold: sum.sold + (Number(row.itemout) || 0),
                    value: sum.value + ((Number(row.remained) || 0) * (Number(row.price) || 0)),
                }),
                { quantity: 0, remained: 0, sold: 0, value: 0 },
            ),
        [rows],
    );

    const remove = async (id) => {
        if (!window.confirm('Delete this product record?')) return;
        setDeletingId(id);
        setErr('');
        try {
            await axios.delete(`/product/delete/${id}`, { headers: xhrJson });
            setRows((current) => current.filter((row) => String(row.id) !== String(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete product.');
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Products'}
                    subtitle="Product stock, sold quantity, remaining quantity, and pricing."
                    actions={
                        <>
                            <Link to="/product/sell" className={btnGhost}>
                                Sell Product
                            </Link>
                            <Link to="/product/create" className={btnPrimary}>
                                Create Product
                            </Link>
                        </>
                    }
                />

                <div className="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Products</p>
                        <p className="mt-1 text-2xl font-semibold text-slate-900">{rows.length}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Total quantity</p>
                        <p className="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{totals.quantity}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Remaining</p>
                        <p className="mt-1 text-2xl font-semibold tabular-nums text-emerald-700">{totals.remained}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Stock value</p>
                        <p className="mt-1 text-2xl font-semibold tabular-nums text-blue-700">{money(totals.value)}</p>
                    </div>
                </div>

                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading products..." /> : null}
                {!loading ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {!rows.length ? (
                            <AccountEmptyState message="No products found." />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH className="w-16">#</AccountTH>
                                        <AccountTH>Item</AccountTH>
                                        <AccountTH className="text-right">Quantity</AccountTH>
                                        <AccountTH className="text-right">Sold</AccountTH>
                                        <AccountTH className="text-right">Remaining</AccountTH>
                                        <AccountTH className="text-right">Price</AccountTH>
                                        <AccountTH className="text-right">Value</AccountTH>
                                        <AccountTH className="text-right">Actions</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={row.id} className="hover:bg-slate-50">
                                            <AccountTD className="tabular-nums text-slate-500">{index + 1}</AccountTD>
                                            <AccountTD>
                                                <p className="font-semibold text-slate-900">{row.item_name || row.name || `Product #${row.id}`}</p>
                                                {row.item_description ? <p className="mt-1 max-w-xs truncate text-xs text-slate-500">{row.item_description}</p> : null}
                                            </AccountTD>
                                            <AccountTD className="text-right tabular-nums text-slate-700">{Number(row.quantity || 0)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums text-slate-700">{Number(row.itemout || 0)}</AccountTD>
                                            <AccountTD className="text-right font-semibold tabular-nums text-emerald-700">{Number(row.remained || 0)}</AccountTD>
                                            <AccountTD className="text-right tabular-nums text-slate-700">{money(row.price)}</AccountTD>
                                            <AccountTD className="text-right font-semibold tabular-nums text-slate-900">{money((Number(row.remained) || 0) * (Number(row.price) || 0))}</AccountTD>
                                            <AccountTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link to={`/product/${row.id}/edit`} className={`${uiIconBtnClass} text-blue-700 hover:bg-blue-50`} title="Edit" aria-label="Edit">
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

