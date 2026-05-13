import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import {
    IconBanknote,
    IconList,
    IconUser,
    IconUsers,
    UiActionGroup,
    UiHeadRow,
    UiPageLoader,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';
import { AnimatedMoney } from '../ui/AnimatedMoney';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

const DASHBOARD_TABLE_EXCLUDE = new Set(['events', 'expense_list', 'fees_groups', 'last_fees_collects']);

const METRIC_THEMES = {
    student: {
        Icon: IconUser,
        card: 'bg-gradient-to-br from-[#3d5d94] to-[#392C7D] text-white shadow-lg shadow-indigo-900/25 ring-1 ring-white/10',
        iconWrap: 'bg-white/15 text-white ring-1 ring-white/25',
        label: 'text-white/90',
        value: 'text-white',
    },
    parent: {
        Icon: IconUsers,
        card: 'bg-gradient-to-br from-amber-400 via-amber-500 to-orange-600 text-slate-900 shadow-lg shadow-amber-900/20 ring-1 ring-amber-300/50',
        iconWrap: 'bg-black/10 text-slate-900 ring-1 ring-black/10',
        label: 'text-slate-800/90',
        value: 'text-slate-950',
    },
    balance: {
        Icon: IconBanknote,
        card: 'bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-lg shadow-emerald-900/25 ring-1 ring-emerald-300/30',
        iconWrap: 'bg-white/15 text-white ring-1 ring-white/25',
        label: 'text-emerald-50',
        value: 'text-white',
    },
    income: {
        Icon: IconBanknote,
        card: 'bg-gradient-to-br from-violet-500 to-purple-700 text-white shadow-lg shadow-violet-900/25 ring-1 ring-white/10',
        iconWrap: 'bg-white/15 text-white ring-1 ring-white/25',
        label: 'text-violet-100',
        value: 'text-white',
    },
    fees_collect: {
        Icon: IconBanknote,
        card: 'bg-gradient-to-br from-sky-500 to-blue-700 text-white shadow-lg shadow-blue-900/25 ring-1 ring-sky-200/30',
        iconWrap: 'bg-white/15 text-white ring-1 ring-white/25',
        label: 'text-sky-50',
        value: 'text-white',
    },
    unpaid_amount: {
        Icon: IconBanknote,
        card: 'bg-gradient-to-br from-rose-500 to-red-700 text-white shadow-lg shadow-rose-900/25 ring-1 ring-rose-200/30',
        iconWrap: 'bg-white/15 text-white ring-1 ring-white/25',
        label: 'text-rose-50',
        value: 'text-white',
    },
    fees: {
        Icon: IconBanknote,
        card: 'bg-gradient-to-br from-cyan-500 to-indigo-600 text-white shadow-lg shadow-cyan-900/20 ring-1 ring-cyan-200/30',
        iconWrap: 'bg-white/15 text-white ring-1 ring-white/25',
        label: 'text-cyan-50',
        value: 'text-white',
    },
    default: {
        Icon: IconList,
        card: 'bg-gradient-to-br from-slate-600 to-slate-900 text-white shadow-lg shadow-slate-900/30 ring-1 ring-slate-500/40',
        iconWrap: 'bg-white/10 text-white ring-1 ring-white/20',
        label: 'text-slate-200',
        value: 'text-white',
    },
};

function getMetricTheme(key) {
    const k = String(key).toLowerCase();
    return METRIC_THEMES[k] || METRIC_THEMES.default;
}

function formatCardValue(key, val) {
    if (typeof val !== 'number' || !Number.isFinite(val)) return String(val ?? '—');
    const k = String(key).toLowerCase();
    if (['fees_collect', 'unpaid_amount', 'balance', 'income'].includes(k)) {
        return val.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    return val.toLocaleString();
}

function usesMoneyFormat(key) {
    const k = String(key).toLowerCase();
    return ['fees_collect', 'unpaid_amount', 'balance', 'income', 'fees'].includes(k);
}

function MetricCard({ metricKey, value }) {
    const theme = getMetricTheme(metricKey);
    const Icon = theme.Icon;
    const label = metricKey.replaceAll('_', ' ');
    return (
        <div className={`rounded-xl p-5 transition hover:-translate-y-0.5 hover:shadow-xl ${theme.card}`}>
            <div className="flex items-start gap-4">
                <div className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-xl ${theme.iconWrap}`}>
                    <Icon className="h-6 w-6" aria-hidden />
                </div>
                <div className="min-w-0 flex-1">
                    <p className={`text-xs font-bold uppercase tracking-wide ${theme.label}`}>{label}</p>
                    <p className={`mt-2 text-xl font-bold tabular-nums tracking-tight sm:text-2xl ${theme.value}`}>
                        {typeof value === 'number' && Number.isFinite(value) ? (
                            <AnimatedMoney
                                value={value}
                                minimumFractionDigits={usesMoneyFormat(metricKey) ? 2 : 0}
                                maximumFractionDigits={usesMoneyFormat(metricKey) ? 2 : 0}
                            />
                        ) : (
                            formatCardValue(metricKey, value)
                        )}
                    </p>
                </div>
            </div>
        </div>
    );
}

function feesCollectStudentName(row) {
    const s = row?.student;
    if (!s) return '—';
    const fn = s.first_name || s.firstName || '';
    const ln = s.last_name || s.lastName || '';
    const combined = `${fn} ${ln}`.trim();
    return combined || s.full_name || s.fullName || s.name || '—';
}

function feesCollectFeeLabel(row) {
    const assign = row?.fees_assign_child || row?.feesAssignChild;
    const master = assign?.fees_master || assign?.feesMaster;
    return master?.name || master?.title || '—';
}

function feesCollectAmount(row) {
    const n = Number(row?.amount ?? row?.paid_amount ?? 0);
    return Number.isFinite(n)
        ? n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        : '—';
}

function feesCollectDate(row) {
    const raw = row?.date || row?.created_at;
    if (!raw) return '—';
    const d = typeof raw === 'string' ? new Date(raw) : raw;
    if (Number.isNaN(d?.getTime?.())) return String(raw);
    return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
}

export function DashboardPage({ Layout }) {
    const [data, setData] = useState({});
    const [extra, setExtra] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    const loadDashboard = useCallback(() => {
        setLoading(true);
        setErr('');
        Promise.allSettled([
            axios.get('/dashboard', { headers: xhrJson }),
            axios.get('/fees-collection-current-month', { headers: xhrJson }),
        ])
            .then((results) => {
                const [dashboardRes, feesRes] = results;
                if (dashboardRes.status === 'fulfilled') {
                    setData(dashboardRes.value.data?.data || {});
                } else {
                    setErr(dashboardRes.reason?.response?.data?.message || 'Failed to load dashboard.');
                }

                const payload = {};
                if (feesRes.status === 'fulfilled') {
                    payload.fees = feesRes.value.data;
                }
                setExtra(payload);
            })
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        loadDashboard();
    }, [loadDashboard]);

    const numberCards = Object.entries(data || {})
        .filter(([k, v]) => typeof v === 'number' && k !== 'expense')
        .slice(0, 8);
    const objectCards = Object.entries(extra || {}).map(([k, v]) => {
        if (typeof v === 'number') return [k, v];
        if (v && typeof v === 'object') {
            const nested = Object.values(v).find((n) => typeof n === 'number');
            return [k, nested ?? 0];
        }
        return [k, 0];
    });
    const listSections = Object.entries(data || {})
        .filter(([k, v]) => Array.isArray(v) && !DASHBOARD_TABLE_EXCLUDE.has(k))
        .slice(0, 3);

    const lastFeesCollects = Array.isArray(data?.last_fees_collects) ? data.last_fees_collects : [];

    const pulseLabel = useMemo(() => {
        const now = new Date();
        const label = now.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
        const hour = now.getHours();
        const greet = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';
        return { label, greet };
    }, []);

    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-6">
                {!loading ? (
                    <section className="relative overflow-hidden rounded-2xl border border-gray-200 bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-950 p-6 text-white shadow-xl ring-1 ring-white/10">
                        <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.12),transparent_45%)]" />
                        <div className="relative flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p className="text-[11px] font-bold uppercase tracking-[0.25em] text-blue-200/90">Dashboard</p>
                                <h2 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{pulseLabel.greet}</h2>
                                <p className="mt-2 max-w-2xl text-sm text-blue-100/90">{pulseLabel.label}</p>
                                <p className="mt-3 max-w-2xl text-sm text-blue-100/80">
                                    Snapshot of enrolment, finance signals, and recent collections — refresh anytime for the latest figures.
                                </p>
                            </div>
                            <div className="rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-right backdrop-blur-sm">
                                <p className="text-[11px] font-semibold uppercase tracking-wide text-blue-100/80">Live metrics</p>
                                <p className="mt-1 text-sm font-semibold text-white">{numberCards.length + objectCards.length} cards</p>
                            </div>
                        </div>
                    </section>
                ) : null}
                {loading ? <UiPageLoader text="Loading dashboard…" /> : null}
                {err ? (
                    <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        {err}
                    </div>
                ) : null}

                {!loading ? (
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {numberCards.map(([k, v]) => (
                            <MetricCard key={k} metricKey={k} value={v} />
                        ))}
                        {objectCards.map(([k, v]) => (
                            <MetricCard key={`extra-${k}`} metricKey={k} value={v} />
                        ))}
                    </div>
                ) : null}

                {!loading ? (
                    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                        <div className="border-b border-gray-200 px-4 py-3">
                            <h2 className="text-sm font-semibold text-gray-800">Last fee collections</h2>
                        </div>
                        <UiTableWrap className="border-0 shadow-none">
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH className="w-10">#</UiTH>
                                        <UiTH>Student</UiTH>
                                        <UiTH>Class</UiTH>
                                        <UiTH>Amount (TZS)</UiTH>
                                        <UiTH>Date</UiTH>
                                        <UiTH>Fee</UiTH>
                                        <UiTH className="text-right">Actions</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {lastFeesCollects.length === 0 ? (
                                        <UiTableEmptyRow colSpan={7} message="No recent fee collections." />
                                    ) : (
                                        lastFeesCollects.slice(0, 5).map((row, idx) => (
                                            <UiTR key={row.id ?? idx}>
                                                <UiTD className="whitespace-nowrap text-gray-500">{idx + 1}</UiTD>
                                                <UiTD className="font-medium text-gray-900">{feesCollectStudentName(row)}</UiTD>
                                                <UiTD>{row.student_class_name || '—'}</UiTD>
                                                <UiTD className="whitespace-nowrap tabular-nums">{feesCollectAmount(row)}</UiTD>
                                                <UiTD className="whitespace-nowrap">{feesCollectDate(row)}</UiTD>
                                                <UiTD>{feesCollectFeeLabel(row)}</UiTD>
                                                <UiTD>
                                                    <div className="flex justify-end">
                                                        <UiActionGroup viewTo={`/fees/collections/${row.id}`} hideDelete />
                                                    </div>
                                                </UiTD>
                                            </UiTR>
                                        ))
                                    )}
                                </UiTBody>
                            </UiTable>
                        </UiTableWrap>
                    </div>
                ) : null}

                {!loading && listSections.length > 0 ? (
                    <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
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
                                                        {typeof row === 'object'
                                                            ? Object.values(row)
                                                                  .slice(0, 2)
                                                                  .join(' | ')
                                                            : String(row)}
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
                    </div>
                ) : null}
            </div>
        </Layout>
    );
}
