import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AccountEmptyState, AccountTable, AccountTD, AccountTH, AccountTHead, AccountTR } from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import { AccountFullPageLoader, AccountsPageShell, AccountsSectionHeader, btnPrimary } from '../AccountsModuleShared';
import { IconEdit, IconTrash, uiIconBtnClass } from '../../ui/UiKit';

function extractRows(responseData) {
    const payload = responseData?.data;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload)) return payload;
    return [];
}

function formatMoney(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) return '0.00';
    return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(value) {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString();
}

function bankName(row) {
    const bank = row?.bank_account;
    return bank ? [bank.bank_name, bank.account_name, bank.account_number].filter(Boolean).join(' - ') : row?.account_number || '-';
}

export function IncomePage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deletingId, setDeletingId] = useState(null);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/income', { headers: xhrJson })
            .then((r) => {
                setRows(extractRows(r.data));
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load income records.'))
            .finally(() => setLoading(false));
    }, []);

    const totalAmount = useMemo(() => rows.reduce((sum, row) => sum + (Number(row.amount) || 0), 0), [rows]);

    const remove = async (id) => {
        if (!window.confirm('Delete this income record?')) return;
        setDeletingId(id);
        setErr('');
        try {
            await axios.delete(`/income/delete/${id}`, { headers: xhrJson });
            setRows((current) => current.filter((row) => String(row.id) !== String(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete income record.');
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Income'}
                    subtitle="Recorded income entries with heads, bank accounts, and references."
                    actions={
                        <Link to="/income/create" className={btnPrimary}>
                            Create income
                        </Link>
                    }
                />

                <div className="mb-4 grid gap-3 sm:grid-cols-2">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Records</p>
                        <p className="mt-1 text-2xl font-semibold text-slate-900">{rows.length}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Total income</p>
                        <p className="mt-1 text-2xl font-semibold tabular-nums text-emerald-700">{formatMoney(totalAmount)}</p>
                    </div>
                </div>

                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading income records..." /> : null}
                {!loading ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {!rows.length ? (
                            <AccountEmptyState message="No income records found." />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH className="w-16">#</AccountTH>
                                        <AccountTH>Name</AccountTH>
                                        <AccountTH>Head</AccountTH>
                                        <AccountTH>Bank account</AccountTH>
                                        <AccountTH>Date</AccountTH>
                                        <AccountTH className="text-right">Amount</AccountTH>
                                        <AccountTH>Reference</AccountTH>
                                        <AccountTH>Description</AccountTH>
                                        <AccountTH className="text-right">Actions</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={row.id} className="hover:bg-slate-50">
                                            <AccountTD className="tabular-nums text-slate-500">{index + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.name || `Income #${row.id}`}</AccountTD>
                                            <AccountTD className="text-slate-600">{row?.head?.name || '-'}</AccountTD>
                                            <AccountTD className="text-slate-600">{bankName(row)}</AccountTD>
                                            <AccountTD className="text-slate-600">{formatDate(row.date || row.created_at)}</AccountTD>
                                            <AccountTD className="text-right font-semibold tabular-nums text-emerald-700">{formatMoney(row.amount)}</AccountTD>
                                            <AccountTD className="text-slate-600">{row.invoice_number || '-'}</AccountTD>
                                            <AccountTD className="max-w-xs truncate text-slate-600">{row.description || '-'}</AccountTD>
                                            <AccountTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        to={`/income/${row.id}/edit`}
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

