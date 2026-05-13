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
    return <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{children}</div>;
}

/** Card header row: title + optional actions. */
export function AccountsSectionHeader({ title, subtitle, actions }) {
    return (
        <div className="mb-5 rounded-2xl border border-slate-200/90 bg-gradient-to-r from-slate-50 via-white to-white p-5 shadow-[0_1px_3px_rgba(15,23,42,0.08),0_8px_24px_rgba(15,23,42,0.04)]">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p className="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Accounts</p>
                    <h1 className="mt-1 text-2xl font-semibold text-slate-900">{title}</h1>
                    {subtitle ? <p className="mt-1 text-sm text-slate-600">{subtitle}</p> : null}
                </div>
                {actions ? <div className="flex flex-wrap items-center gap-2">{actions}</div> : null}
            </div>
        </div>
    );
}

export const btnPrimary =
    'inline-flex rounded-lg bg-blue-600 px-3.5 py-2 text-sm font-medium text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-blue-700';
export const btnGhost =
    'inline-flex rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50';
export const inputClass =
    'rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-inner focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100';

export function AccountsHomePageComponent({ Layout }) {
    const sections = useMemo(
        () => [
            {
                title: 'Overview & setup',
                description: 'Configure the foundation used by every account entry and report.',
                accent: 'from-blue-600 to-indigo-600',
                items: [
                    { to: '/accounting/dashboard', title: 'Accounting Dashboard', desc: 'Review KPIs, bank balances, and recent financial activity.' },
                    { to: '/chart-of-accounts', title: 'Chart of Accounts', desc: 'Define the ledger structure used across the school.' },
                    { to: '/payment-methods', title: 'Payment Methods', desc: 'Manage cash, bank, and digital payment channels.' },
                    { to: '/account-heads', title: 'Account Heads', desc: 'Set reporting categories for income and expense entries.' },
                ],
            },
            {
                title: 'Money movement',
                description: 'Capture and review income, spending, cash, deposits, and payments.',
                accent: 'from-emerald-600 to-teal-600',
                items: [
                    { to: '/income', title: 'Income', desc: 'Capture non-fee income and other incoming funds.' },
                    { to: '/expense', title: 'Expense', desc: 'Track spending with categories, dates, and bank links.' },
                    { to: '/cash', title: 'Cash', desc: 'Review cash position and movement snapshots.' },
                    { to: '/deposits', title: 'Deposits', desc: 'Record and review bank deposit activity.' },
                    { to: '/payments', title: 'Payments', desc: 'Manage outgoing bank and cash payments.' },
                    { to: '/account-transactions', title: 'Transactions', desc: 'Open the unified journal-style ledger view.' },
                ],
            },
            {
                title: 'Operations',
                description: 'Manage supporting records for invoices, suppliers, stock, and items.',
                accent: 'from-purple-600 to-fuchsia-600',
                items: [
                    { to: '/suppliers', title: 'Suppliers', desc: 'Maintain supplier directory and contact details.' },
                    { to: '/invoices', title: 'Invoices', desc: 'Track billing and invoice records.' },
                    { to: '/product', title: 'Products', desc: 'Manage products, stock, and product sales from one page.' },
                    { to: '/item', title: 'Items', desc: 'Review item definitions used by product records.' },
                ],
            },
        ],
        [],
    );

    const totalSections = sections.reduce((total, section) => total + section.items.length, 0);

    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-8 p-6">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-200">Accounts center</p>
                            <h1 className="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Accounting</h1>
                            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-200">
                                Manage school finance setup, daily transactions, stock records, and operational accounting pages from one workspace.
                            </p>
                            <div className="mt-5 flex flex-wrap gap-2">
                                <Link to="/accounting/dashboard" className="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-emerald-50">
                                    Open Dashboard
                                </Link>
                                <Link
                                    to="/account-transactions"
                                    className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15"
                                >
                                    View Transactions
                                </Link>
                            </div>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                            <p className="text-3xl font-bold">{totalSections}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-emerald-100">Available sections</p>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6">
                    {sections.map((section) => (
                        <section key={section.title} className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className={`h-1.5 bg-gradient-to-r ${section.accent}`} />
                            <div className="border-b border-slate-100 p-5">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2 className="text-lg font-semibold text-slate-950">{section.title}</h2>
                                        <p className="mt-1 text-sm text-slate-500">{section.description}</p>
                                    </div>
                                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {section.items.length} sections
                                    </span>
                                </div>
                            </div>
                            <div className="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                                {section.items.map((item) => (
                                    <Link
                                        key={item.to}
                                        to={item.to}
                                        className="group rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-white hover:shadow-md"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 className="font-semibold text-slate-900 group-hover:text-emerald-700">{item.title}</h3>
                                                <p className="mt-2 text-sm leading-5 text-slate-500">{item.desc}</p>
                                            </div>
                                            <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-sm font-bold text-slate-400 shadow-sm ring-1 ring-slate-200 group-hover:bg-emerald-600 group-hover:text-white group-hover:ring-emerald-600">
                                                &rarr;
                                            </span>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>
            </div>
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
