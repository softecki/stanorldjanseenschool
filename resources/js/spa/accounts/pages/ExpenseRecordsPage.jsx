import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AccountEmptyState, AccountTable, AccountTD, AccountTH, AccountTHead, AccountTR } from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import { AccountFullPageLoader, AccountsPageShell, AccountsSectionHeader, btnGhost, btnPrimary } from '../AccountsModuleShared';
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

function headName(row) {
    return row?.head?.name || row?.status_name || '-';
}

function bankName(row) {
    const bank = row?.bank_account;
    return bank ? [bank.bank_name, bank.account_name, bank.account_number].filter(Boolean).join(' - ') : row?.account_number || '-';
}

export function ExpenseRecordsPage({
    Layout,
    title,
    subtitle,
    endpoint,
    createTo,
    editBase,
    deleteBase,
    createLabel = 'Create',
}) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [deletingId, setDeletingId] = useState(null);

    const loadRows = () => {
        setLoading(true);
        setErr('');
        axios
            .get(endpoint, { headers: xhrJson })
            .then((r) => {
                setRows(extractRows(r.data));
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || `Failed to load ${title.toLowerCase()}.`))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        loadRows();
    }, [endpoint]);

    const totalAmount = useMemo(() => rows.reduce((sum, row) => sum + (Number(row.amount) || 0), 0), [rows]);

    const remove = async (id) => {
        if (!window.confirm(`Delete this ${title.toLowerCase()} record?`)) return;
        setDeletingId(id);
        setErr('');
        try {
            await axios.delete(`${deleteBase}/${id}`, { headers: xhrJson });
            setRows((current) => current.filter((row) => String(row.id) !== String(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || `Failed to delete ${title.toLowerCase()} record.`);
        } finally {
            setDeletingId(null);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || title}
                    subtitle={subtitle}
                    actions={
                        createTo ? (
                            <Link to={createTo} className={btnPrimary}>
                                {createLabel}
                            </Link>
                        ) : null
                    }
                />

                <div className="mb-4 grid gap-3 sm:grid-cols-2">
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Records</p>
                        <p className="mt-1 text-2xl font-semibold text-slate-900">{rows.length}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Total amount</p>
                        <p className="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{formatMoney(totalAmount)}</p>
                    </div>
                </div>

                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader text={`Loading ${title.toLowerCase()}...`} /> : null}
                {!loading ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {!rows.length ? (
                            <AccountEmptyState message={`No ${title.toLowerCase()} records found.`} />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH className="w-16">#</AccountTH>
                                        <AccountTH>Name</AccountTH>
                                        <AccountTH>Head / Status</AccountTH>
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
                                            <AccountTD className="font-semibold text-slate-900">{row.name || `Record #${row.id}`}</AccountTD>
                                            <AccountTD className="text-slate-600">{headName(row)}</AccountTD>
                                            <AccountTD className="text-slate-600">{bankName(row)}</AccountTD>
                                            <AccountTD className="text-slate-600">{formatDate(row.date || row.created_at)}</AccountTD>
                                            <AccountTD className="text-right font-semibold tabular-nums text-slate-900">{formatMoney(row.amount)}</AccountTD>
                                            <AccountTD className="text-slate-600">{row.invoice_number || '-'}</AccountTD>
                                            <AccountTD className="max-w-xs truncate text-slate-600">{row.description || '-'}</AccountTD>
                                            <AccountTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        to={`${editBase}/${row.id}/edit`}
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
