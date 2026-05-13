import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AccountTable, AccountTD, AccountTH, AccountTHead, AccountTR } from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import { AccountFullPageLoader, AccountsPageShell } from '../AccountsModuleShared';

function formatMoney(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) return '0.00';
    return number.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

export function AccountingDashboardPage({ Layout }) {
    const [meta, setMeta] = useState(null);
    const [dashData, setDashData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/accounting/dashboard', { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || null);
                setDashData(r.data?.data || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load accounting dashboard.'))
            .finally(() => setLoading(false));
    }, []);

    const banks = dashData?.bank_accounts || [];
    const incomes = dashData?.recent_incomes || [];
    const expenses = dashData?.recent_expenses || [];
    const totalBankBalance = useMemo(() => banks.reduce((sum, b) => sum + (Number(b?.balance) || 0), 0), [banks]);

    const kpis = [
        {
            label: 'Total income',
            value: formatMoney(meta?.total_income),
            hint: 'Other income + fees',
            tone: 'from-emerald-50 to-white text-emerald-800 border-emerald-100',
        },
        {
            label: 'Total expense',
            value: formatMoney(meta?.total_expense),
            hint: 'Session',
            tone: 'from-rose-50 to-white text-rose-800 border-rose-100',
        },
        {
            label: 'Net balance',
            value: formatMoney(meta?.balance),
            hint: 'Income - expense',
            tone: 'from-blue-50 to-white text-blue-800 border-blue-100',
        },
        {
            label: 'Fees collected',
            value: formatMoney(meta?.fees_collected),
            hint: 'Included in income',
            tone: 'from-violet-50 to-white text-violet-800 border-violet-100',
        },
        {
            label: 'Today income',
            value: formatMoney(meta?.today_income),
            hint: 'Other income + fees',
            tone: 'from-teal-50 to-white text-teal-800 border-teal-100',
        },
        {
            label: 'Today expense',
            value: formatMoney(meta?.today_expense),
            hint: 'Daily snapshot',
            tone: 'from-amber-50 to-white text-amber-800 border-amber-100',
        },
    ];

    return (
        <Layout>
            <AccountsPageShell>
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading dashboard…" /> : null}
                {!loading && !err ? (
                    <div className="space-y-6">
                        <div className="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 px-5 py-4 text-slate-100 shadow-sm">
                            <p className="text-[11px] uppercase tracking-[0.16em] text-slate-300">Financial summary</p>
                            <div className="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p className="text-xs text-slate-300">Net balance</p>
                                    <p className="text-xl font-semibold tabular-nums text-white">{formatMoney(meta?.balance)}</p>
                                </div>
                                <div>
                                    <p className="text-xs text-slate-300">Bank position</p>
                                    <p className="text-xl font-semibold tabular-nums text-white">{formatMoney(totalBankBalance)}</p>
                                </div>
                                <div>
                                    <p className="text-xs text-slate-300">Recent income rows</p>
                                    <p className="text-xl font-semibold tabular-nums text-white">{incomes.length}</p>
                                </div>
                                <div>
                                    <p className="text-xs text-slate-300">Recent expense rows</p>
                                    <p className="text-xl font-semibold tabular-nums text-white">{expenses.length}</p>
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {kpis.map((k) => (
                                <div
                                    key={k.label}
                                    className={`rounded-2xl border bg-gradient-to-br p-4 shadow-[0_1px_3px_rgba(15,23,42,0.08),0_8px_24px_rgba(15,23,42,0.04)] ${k.tone}`}
                                >
                                    <p className="text-[11px] font-semibold uppercase tracking-[0.14em] opacity-80">{k.label}</p>
                                    <p className="mt-2 text-2xl font-semibold tabular-nums">{k.value}</p>
                                    <p className="mt-1 text-xs opacity-70">{k.hint}</p>
                                </div>
                            ))}
                        </div>

                        <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div className="mb-3 flex items-center justify-between">
                                <h2 className="text-lg font-semibold text-slate-900">Bank accounts</h2>
                                <p className="text-xs font-medium text-slate-500">{banks.length} account{banks.length === 1 ? '' : 's'}</p>
                            </div>
                            {!banks.length ? (
                                <p className="text-sm text-slate-500">No bank accounts on file.</p>
                            ) : (
                                <AccountTable>
                                    <AccountTHead>
                                        <AccountTR>
                                            <AccountTH>Bank</AccountTH>
                                            <AccountTH>Account</AccountTH>
                                            <AccountTH className="text-right">Current balance</AccountTH>
                                        </AccountTR>
                                    </AccountTHead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {banks.map((b) => (
                                            <AccountTR key={b.id}>
                                                <AccountTD>{b.bank_name || '—'}</AccountTD>
                                                <AccountTD className="text-slate-600">
                                                    {[b.account_name, b.account_number].filter(Boolean).join(' · ') || '—'}
                                                </AccountTD>
                                                <AccountTD className="text-right font-semibold tabular-nums text-slate-900">
                                                    {formatMoney(b.balance)}
                                                </AccountTD>
                                            </AccountTR>
                                        ))}
                                    </tbody>
                                </AccountTable>
                            )}
                        </div>

                        <div className="grid gap-6 lg:grid-cols-2">
                            <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div className="mb-3 flex items-center justify-between">
                                    <h2 className="text-lg font-semibold text-emerald-800">Recent income</h2>
                                    <Link to="/income" className="text-xs font-medium text-emerald-700 hover:text-emerald-900">
                                        View all
                                    </Link>
                                </div>
                                {!incomes.length ? (
                                    <p className="text-sm text-slate-500">No recent income entries.</p>
                                ) : (
                                    <AccountTable>
                                        <AccountTHead>
                                            <AccountTR>
                                                <AccountTH>Date</AccountTH>
                                                <AccountTH>Description</AccountTH>
                                                <AccountTH>Head</AccountTH>
                                                <AccountTH className="text-right">Amount</AccountTH>
                                            </AccountTR>
                                        </AccountTHead>
                                        <tbody className="divide-y divide-slate-100 bg-white">
                                            {incomes.map((row) => (
                                                <AccountTR key={row.id}>
                                                    <AccountTD>{row.date || '—'}</AccountTD>
                                                    <AccountTD>{row.name || '—'}</AccountTD>
                                                    <AccountTD className="text-slate-600">{row.head_name || '—'}</AccountTD>
                                                    <AccountTD className="text-right tabular-nums font-medium text-emerald-700">
                                                        {formatMoney(row.amount)}
                                                    </AccountTD>
                                                </AccountTR>
                                            ))}
                                        </tbody>
                                    </AccountTable>
                                )}
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div className="mb-3 flex items-center justify-between">
                                    <h2 className="text-lg font-semibold text-rose-800">Recent expenses</h2>
                                    <Link to="/expense" className="text-xs font-medium text-rose-700 hover:text-rose-900">
                                        View all
                                    </Link>
                                </div>
                                {!expenses.length ? (
                                    <p className="text-sm text-slate-500">No recent expense entries.</p>
                                ) : (
                                    <AccountTable>
                                        <AccountTHead>
                                            <AccountTR>
                                                <AccountTH>Date</AccountTH>
                                                <AccountTH>Description</AccountTH>
                                                <AccountTH>Head</AccountTH>
                                                <AccountTH className="text-right">Amount</AccountTH>
                                            </AccountTR>
                                        </AccountTHead>
                                        <tbody className="divide-y divide-slate-100 bg-white">
                                            {expenses.map((row) => (
                                                <AccountTR key={row.id}>
                                                    <AccountTD>{row.date || '—'}</AccountTD>
                                                    <AccountTD>{row.name || '—'}</AccountTD>
                                                    <AccountTD className="text-slate-600">{row.head_name || '—'}</AccountTD>
                                                    <AccountTD className="text-right tabular-nums font-medium text-rose-700">
                                                        {formatMoney(row.amount)}
                                                    </AccountTD>
                                                </AccountTR>
                                            ))}
                                        </tbody>
                                    </AccountTable>
                                )}
                            </div>
                        </div>
                    </div>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

