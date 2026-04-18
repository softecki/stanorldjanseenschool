import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
    AccountCard,
    AccountEmptyState,
    AccountPageHeader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from './components/AccountUi';

import { xhrJson } from '../api/xhrJson';
import { UiPageLoader } from '../ui/UiKit';

export function AccountFullPageLoader(props) {
    return <UiPageLoader {...props} />;
}

/** Outer chrome matching Fees module (max width + padded shell). */
export function AccountsPageShell({ children }) {
    return <div className="mx-auto max-w-7xl p-6">{children}</div>;
}

/** Card header row: title + optional actions. */
export function AccountsSectionHeader({ title, subtitle, actions }) {
    return (
        <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-800">{title}</h1>
                    {subtitle ? <p className="mt-1 text-sm text-gray-500">{subtitle}</p> : null}
                </div>
                {actions ? <div className="flex flex-wrap items-center gap-2">{actions}</div> : null}
            </div>
        </div>
    );
}

export const btnPrimary = 'inline-flex rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700';
export const btnGhost = 'inline-flex rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50';
export const inputClass = 'rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 shadow-inner focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100';

export function AccountsHomePageComponent({ Layout }) {
    const tiles = useMemo(
        () => [
            { to: '/accounts/chart-of-accounts', title: 'Chart of Accounts', desc: 'Ledger structure', icon: '📒' },
            { to: '/accounts/payment-methods', title: 'Payment Methods', desc: 'How you get paid', icon: '💳' },
            { to: '/accounts/account-heads', title: 'Account Heads', desc: 'Categories for entries', icon: '📄' },
            { to: '/accounts/income', title: 'Income', desc: 'Record income', icon: '📈' },
            { to: '/accounts/expense', title: 'Expense', desc: 'Record expenses', icon: '📉' },
            { to: '/accounts/cash', title: 'Cash', desc: 'Cash position snapshot', icon: '💵' },
            { to: '/accounts/deposits', title: 'Deposits', desc: 'Bank deposits', icon: '🏦' },
            { to: '/accounts/payments', title: 'Payments', desc: 'Outgoing payments', icon: '🧾' },
            { to: '/accounts/transactions', title: 'Transactions', desc: 'Journal-style list', icon: '↔️' },
            { to: '/accounts/suppliers', title: 'Suppliers', desc: 'Vendor directory', icon: '🚚' },
            { to: '/accounts/invoices', title: 'Invoices', desc: 'Billing documents', icon: '📋' },
            { to: '/accounts/product', title: 'Products', desc: 'Inventory & product cash', icon: '📦' },
            { to: '/accounts/item', title: 'Items', desc: 'Stock items', icon: '🏷️' },
            { to: '/accounts/balance', title: 'Balance', desc: 'Account balances', icon: '⚖️' },
        ],
        [],
    );

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title="Accounts"
                    subtitle="Accounting tools and ledgers in one place — layout aligned with Fees and other admin modules."
                />
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {tiles.map((t) => (
                        <Link
                            key={t.to}
                            to={t.to}
                            className="group flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md"
                        >
                            <span className="text-2xl" aria-hidden>
                                {t.icon}
                            </span>
                            <span className="mt-3 text-lg font-semibold text-gray-900 group-hover:text-blue-700">{t.title}</span>
                            <span className="mt-1 text-sm text-gray-500">{t.desc}</span>
                        </Link>
                    ))}
                </div>
            </AccountsPageShell>
        </Layout>
    );
}

export function extractRows(responseData) {
    const d = responseData?.data;
    if (Array.isArray(d)) return d;
    if (d && Array.isArray(d.data)) return d.data;
    if (responseData?.data?.data && Array.isArray(responseData.data.data)) return responseData.data.data;
    return [];
}

/** Generic list: name column + edit link + loading. */
export function AccountsCrudListPage({ Layout, title, subtitle, endpoint, createTo, rowLabel = (row) => row.name || row.title || `#${row.id}` }) {
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(endpoint, { headers: xhrJson })
            .then((r) => setRows(extractRows(r.data)))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'))
            .finally(() => setLoading(false));
    }, [endpoint]);

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={title}
                    subtitle={subtitle}
                    actions={
                        createTo ? (
                            <Link to={createTo} className={btnPrimary}>
                                Create
                            </Link>
                        ) : null
                    }
                />
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader text={`Loading ${title.toLowerCase()}…`} /> : null}
                {!loading ? (
                    <AccountCard>
                        {!rows.length ? (
                            <AccountEmptyState message="No records yet. Create one to get started." />
                        ) : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>Name</AccountTH>
                                        <AccountTH className="text-right">Actions</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-gray-100 bg-white">
                                    {rows.map((row) => (
                                        <AccountTR key={row.id}>
                                            <AccountTD>
                                                <Link className="font-medium text-blue-600 hover:text-blue-800" to={createTo.replace('/create', `/${row.id}/edit`)}>
                                                    {rowLabel(row)}
                                                </Link>
                                            </AccountTD>
                                            <AccountTD className="text-right">
                                                <Link className="text-sm font-medium text-amber-700 hover:text-amber-900" to={createTo.replace('/create', `/${row.id}/edit`)}>
                                                    Edit
                                                </Link>
                                            </AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </AccountCard>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

export function AccountsSimpleFormPage({ Layout, title, edit, id, loadPath, storePath, updatePath, backTo, children }) {
    const nav = useNavigate();
    const [form, setForm] = useState({});
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setLoading(true);
        const url = edit ? `${loadPath}/edit/${id}` : `${loadPath}/create`;
        axios
            .get(url, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                if (edit) setForm(r.data?.data || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
    }, [edit, id, loadPath]);

    const submit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setErr('');
        try {
            if (edit) await axios.put(`${updatePath}/${id}`, form, { headers: xhrJson });
            else await axios.post(storePath, form, { headers: xhrJson });
            nav(backTo);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader title={title} />
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        {typeof children === 'function' ? children({ form, setForm, meta }) : children}
                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link to={backTo} className={btnGhost}>
                                Cancel
                            </Link>
                            <button type="submit" disabled={saving} className={btnPrimary + ' disabled:opacity-60'}>
                                {saving ? 'Saving…' : edit ? 'Update' : 'Create'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

/* —— Exported pages —— */
