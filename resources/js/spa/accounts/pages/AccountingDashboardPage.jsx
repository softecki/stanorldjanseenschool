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
} from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import {
    AccountFullPageLoader,
    AccountsCrudListPage,
    AccountsHomePageComponent,
    AccountsPageShell,
    AccountsSectionHeader,
    AccountsSimpleFormPage,
    btnGhost,
    btnPrimary,
    extractRows,
    inputClass,
} from '../AccountsModuleShared';

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

    const kpis = meta
        ? [
              { label: 'Total income', value: formatMoney(meta.total_income), tone: 'text-emerald-700' },
              { label: 'Total expense', value: formatMoney(meta.total_expense), tone: 'text-rose-700' },
              { label: 'Net balance', value: formatMoney(meta.balance), tone: 'text-blue-700' },
              { label: 'Fees collected', value: formatMoney(meta.fees_collected), tone: 'text-gray-800' },
              { label: 'Today income', value: formatMoney(meta.today_income), tone: 'text-gray-800' },
              { label: 'Today expense', value: formatMoney(meta.today_expense), tone: 'text-gray-800' },
          ]
        : [];

    const banks = dashData?.bank_accounts || [];
    const incomes = dashData?.recent_incomes || [];
    const expenses = dashData?.recent_expenses || [];

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta?.title || 'Financial dashboard'}
                    subtitle="Session totals, fees collected, and the latest income and expense lines."
                />
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader text="Loading dashboard…" /> : null}
                {!loading && !err ? (
                    <div className="space-y-6">
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {kpis.map((k) => (
                                <div key={k.label} className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">{k.label}</p>
                                    <p className={`mt-2 text-2xl font-semibold tabular-nums ${k.tone}`}>{k.value}</p>
                                </div>
                            ))}
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <h2 className="mb-3 text-lg font-semibold text-gray-800">Bank accounts</h2>
                            {!banks.length ? (
                                <p className="text-sm text-gray-500">No bank accounts on file.</p>
                            ) : (
                                <AccountTable>
                                    <AccountTHead>
                                        <AccountTR>
                                            <AccountTH>Bank</AccountTH>
                                            <AccountTH>Account</AccountTH>
                                            <AccountTH className="text-right">Balance</AccountTH>
                                        </AccountTR>
                                    </AccountTHead>
                                    <tbody className="divide-y divide-gray-100 bg-white">
                                        {banks.map((b) => (
                                            <AccountTR key={b.id}>
                                                <AccountTD>{b.bank_name || '—'}</AccountTD>
                                                <AccountTD className="text-gray-600">
                                                    {[b.account_name, b.account_number].filter(Boolean).join(' · ') || '—'}
                                                </AccountTD>
                                                <AccountTD className="text-right font-medium tabular-nums">{formatMoney(b.balance)}</AccountTD>
                                            </AccountTR>
                                        ))}
                                    </tbody>
                                </AccountTable>
                            )}
                        </div>

                        <div className="grid gap-6 lg:grid-cols-2">
                            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <h2 className="mb-3 text-lg font-semibold text-gray-800">Recent income</h2>
                                {!incomes.length ? (
                                    <p className="text-sm text-gray-500">No recent income entries.</p>
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
                                        <tbody className="divide-y divide-gray-100 bg-white">
                                            {incomes.map((row) => (
                                                <AccountTR key={row.id}>
                                                    <AccountTD>{row.date || '—'}</AccountTD>
                                                    <AccountTD>{row.name || '—'}</AccountTD>
                                                    <AccountTD className="text-gray-600">{row.head_name || '—'}</AccountTD>
                                                    <AccountTD className="text-right tabular-nums">{formatMoney(row.amount)}</AccountTD>
                                                </AccountTR>
                                            ))}
                                        </tbody>
                                    </AccountTable>
                                )}
                            </div>
                            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <h2 className="mb-3 text-lg font-semibold text-gray-800">Recent expenses</h2>
                                {!expenses.length ? (
                                    <p className="text-sm text-gray-500">No recent expense entries.</p>
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
                                        <tbody className="divide-y divide-gray-100 bg-white">
                                            {expenses.map((row) => (
                                                <AccountTR key={row.id}>
                                                    <AccountTD>{row.date || '—'}</AccountTD>
                                                    <AccountTD>{row.name || '—'}</AccountTD>
                                                    <AccountTD className="text-gray-600">{row.head_name || '—'}</AccountTD>
                                                    <AccountTD className="text-right tabular-nums">{formatMoney(row.amount)}</AccountTD>
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

