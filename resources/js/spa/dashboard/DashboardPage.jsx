import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { UiHeadRow, UiPageLoader, UiTable, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../ui/UiKit';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export function DashboardPage({ Layout }) {
    const [data, setData] = useState({});
    const [extra, setExtra] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        Promise.allSettled([
            axios.get('/dashboard', { headers: xhrJson }),
            axios.get('/fees-collection-current-month', { headers: xhrJson }),
            axios.get('/income-expense-current-month', { headers: xhrJson }),
            axios.get('/today-attendance', { headers: xhrJson }),
            axios.get('/events-current-month', { headers: xhrJson }),
        ]).then((results) => {
            const [dashboardRes, ...extraResults] = results;
            if (dashboardRes.status === 'fulfilled') {
                setData(dashboardRes.value.data?.data || {});
            } else {
                setErr(dashboardRes.reason?.response?.data?.message || 'Failed to load dashboard.');
            }

            const payload = {};
            const keys = ['fees', 'incomeExpense', 'attendance', 'events'];
            extraResults.forEach((r, i) => {
                if (r.status === 'fulfilled') payload[keys[i]] = r.value.data;
            });
            setExtra(payload);
        }).finally(() => setLoading(false));
    }, []);

    const numberCards = Object.entries(data || {}).filter(([, v]) => typeof v === 'number').slice(0, 8);
    const objectCards = Object.entries(extra || {}).map(([k, v]) => {
        if (typeof v === 'number') return [k, v];
        if (v && typeof v === 'object') {
            const nested = Object.values(v).find((n) => typeof n === 'number');
            return [k, nested ?? 0];
        }
        return [k, 0];
    });
    const listSections = Object.entries(data || {}).filter(([, v]) => Array.isArray(v)).slice(0, 3);
    const chartRows = [...numberCards.slice(0, 4), ...objectCards.slice(0, 4)];
    const maxChartValue = Math.max(1, ...chartRows.map(([, v]) => Number(v || 0)));

    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900">Dashboard</h1>
                    <p className="mt-1 text-sm text-gray-500">Overview of key metrics and activity</p>
                </div>
                {loading ? <UiPageLoader text="Loading dashboard…" /> : null}
                {err ? (
                    <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        {err}
                    </div>
                ) : null}

                {!loading ? <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {numberCards.map(([k, v]) => (
                        <div
                            key={k}
                            className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm ring-1 ring-gray-100"
                        >
                            <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {k.replaceAll('_', ' ')}
                            </p>
                            <p className="mt-2 text-2xl font-bold text-gray-900">{v}</p>
                        </div>
                    ))}
                    {objectCards.map(([k, v]) => (
                        <div
                            key={k}
                            className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm ring-1 ring-gray-100"
                        >
                            <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {k.replaceAll('_', ' ')}
                            </p>
                            <p className="mt-2 text-2xl font-bold text-blue-700">{v}</p>
                        </div>
                    ))}
                </div> : null}

                {!loading ? <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm ring-1 ring-gray-100">
                    <h2 className="text-lg font-semibold text-gray-800">Performance</h2>
                    <p className="mt-1 text-sm text-gray-500">Relative comparison of selected metrics</p>
                    <div className="mt-6 space-y-4">
                        {chartRows.map(([k, v]) => {
                            const pct = Math.max(4, Math.round((Number(v || 0) / maxChartValue) * 100));
                            return (
                                <div key={k}>
                                    <div className="mb-1 flex items-center justify-between text-xs font-medium text-gray-600">
                                        <span className="capitalize">{k.replaceAll('_', ' ')}</span>
                                        <span className="text-gray-900">{Number(v || 0)}</span>
                                    </div>
                                    <div className="h-2 overflow-hidden rounded-full bg-gray-100">
                                        <div className="h-2 rounded-full bg-blue-600 transition-all" style={{ width: `${pct}%` }} />
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div> : null}

                {!loading ? <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    {listSections.map(([k, rows]) => (
                        <div key={k} className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                            <div className="border-b border-gray-200 px-4 py-3">
                                <h2 className="text-sm font-semibold text-gray-800 capitalize">{k.replaceAll('_', ' ')}</h2>
                            </div>
                            <div className="overflow-x-auto">
                                <UiTable>
                                    <UiTHead>
                                        <UiHeadRow>
                                            <UiTH>#</UiTH>
                                            <UiTH>Details</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.slice(0, 6).map((row, idx) => (
                                            <UiTR key={idx}>
                                                <UiTD className="whitespace-nowrap text-gray-500">{idx + 1}</UiTD>
                                                <UiTD>
                                                    {typeof row === 'object' ? Object.values(row).slice(0, 2).join(' | ') : String(row)}
                                                </UiTD>
                                            </UiTR>
                                        ))}
                                    </UiTBody>
                                </UiTable>
                                {!rows.length ? (
                                    <p className="px-4 py-6 text-center text-sm text-gray-500">No records.</p>
                                ) : null}
                            </div>
                        </div>
                    ))}
                </div> : null}
            </div>
        </Layout>
    );
}
