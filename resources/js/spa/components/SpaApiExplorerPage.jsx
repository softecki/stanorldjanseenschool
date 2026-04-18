import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import {
    AccountCard,
    AccountEmptyState,
    AccountFullPageLoader,
    AccountPageHeader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../accounts/components/AccountUi';
import { UiHeadRow, UiTable, UiTableWrap, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../ui/UiKit';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

function resolveEndpoint(endpoint, endpointTemplate, routeParams) {
    if (endpoint) return endpoint;
    if (!endpointTemplate) return '';
    return Object.keys(routeParams || {}).reduce(
        (acc, key) => acc.replace(new RegExp(`:${key}\\b`, 'g'), routeParams[key] ?? ''),
        endpointTemplate,
    );
}

function cellText(val) {
    if (val === null || val === undefined) return '—';
    if (typeof val === 'boolean') return val ? 'Yes' : 'No';
    if (typeof val === 'number' && Number.isFinite(val)) return String(val);
    if (typeof val === 'string') return val.length > 240 ? `${val.slice(0, 240)}…` : val;
    try {
        const s = JSON.stringify(val);
        return s.length > 160 ? `${s.slice(0, 160)}…` : s;
    } catch {
        return String(val);
    }
}

function inferColumns(rows, max = 16) {
    if (!Array.isArray(rows) || !rows.length) return [];
    const keys = new Set();
    rows.slice(0, 40).forEach((row) => {
        if (row && typeof row === 'object' && !Array.isArray(row)) {
            Object.keys(row).forEach((k) => {
                if (!k.startsWith('_')) keys.add(k);
            });
        }
    });
    return Array.from(keys).slice(0, max);
}

function normalizePagination(payload) {
    const m = payload?.meta?.pagination;
    if (m && m.last_page) return m;
    const d = payload?.data;
    if (d && typeof d === 'object' && !Array.isArray(d) && Array.isArray(d.data)) {
        return {
            current_page: d.current_page,
            last_page: d.last_page,
            per_page: d.per_page,
            total: d.total,
        };
    }
    return null;
}

function extractRows(payload) {
    const d = payload?.data;
    if (Array.isArray(d)) return d;
    if (d && typeof d === 'object' && Array.isArray(d.data)) return d.data;
    const md = payload?.meta?.data;
    if (Array.isArray(md)) return md;
    return null;
}

function KeyValueGrid({ title, object }) {
    if (!object || typeof object !== 'object' || Array.isArray(object)) return null;
    const entries = Object.entries(object).filter(([k]) => !k.startsWith('_'));
    if (!entries.length) return null;
    return (
        <div className="mb-4">
            {title ? <h3 className="mb-2 text-sm font-semibold text-gray-800">{title}</h3> : null}
            <dl className="grid gap-3 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm md:grid-cols-2 lg:grid-cols-3">
                {entries.map(([k, v]) => (
                    <div key={k} className="min-w-0">
                        <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{k.replace(/_/g, ' ')}</dt>
                        <dd className="mt-1 break-words text-gray-900">{cellText(v)}</dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}

function MetaExtras({ meta }) {
    if (!meta || typeof meta !== 'object') return null;
    const { pagination: _p, data: _d, ...rest } = meta;
    const keys = Object.keys(rest);
    if (!keys.length) return null;
    return (
        <div className="space-y-4 border-t border-gray-100 pt-4">
            <h3 className="text-sm font-semibold text-gray-800">Context</h3>
            {keys.map((key) => {
                const val = rest[key];
                if (val === null || val === undefined) return null;
                if (Array.isArray(val)) {
                    if (!val.length) return null;
                    const cols = inferColumns(val, 8);
                    if (!cols.length) return null;
                    return (
                        <div key={key}>
                            <h4 className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">{key.replace(/_/g, ' ')}</h4>
                            <UiTableWrap>
                                <UiTable className="text-xs">
                                    <UiTHead>
                                        <UiHeadRow>
                                            {cols.map((c) => (
                                                <UiTH key={c} className="text-left normal-case">
                                                    {c.replace(/_/g, ' ')}
                                                </UiTH>
                                            ))}
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {val.slice(0, 50).map((row, i) => (
                                            <UiTR key={i}>
                                                {cols.map((c) => (
                                                    <UiTD key={c} className="max-w-[200px] truncate" title={cellText(row?.[c])}>
                                                        {cellText(row?.[c])}
                                                    </UiTD>
                                                ))}
                                            </UiTR>
                                        ))}
                                    </UiTBody>
                                </UiTable>
                            </UiTableWrap>
                        </div>
                    );
                }
                if (typeof val === 'object') {
                    return <KeyValueGrid key={key} title={key.replace(/_/g, ' ')} object={val} />;
                }
                return (
                    <p key={key} className="text-sm text-gray-700">
                        <span className="font-medium text-gray-500">{key.replace(/_/g, ' ')}: </span>
                        {cellText(val)}
                    </p>
                );
            })}
        </div>
    );
}

/**
 * Generic SPA shell for JSON admin endpoints: dynamic table when `data` is a list,
 * key/value panels for objects (e.g. settings `meta.data`), pagination when present,
 * plus collapsible raw JSON. Replaces bare `<pre>` report/legacy pages.
 */
export function SpaApiExplorerPage({
    Layout,
    title,
    subtitle,
    endpoint,
    endpointTemplate,
    backLink,
}) {
    const routeParams = useParams();
    const [payload, setPayload] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [showRaw, setShowRaw] = useState(false);
    const [page, setPage] = useState(1);

    const resolvedEndpoint = useMemo(
        () => resolveEndpoint(endpoint, endpointTemplate, routeParams),
        [endpoint, endpointTemplate, routeParams],
    );

    const load = useCallback(
        async (p = 1) => {
            if (!resolvedEndpoint) {
                setErr('Missing endpoint.');
                setLoading(false);
                return;
            }
            setErr('');
            setLoading(true);
            try {
                const { data } = await axios.get(resolvedEndpoint, {
                    headers: xhrJson,
                    params: { page: p },
                });
                setPayload(data);
                setPage(p);
            } catch (ex) {
                setPayload(null);
                setErr(ex.response?.data?.message || 'Failed to load data.');
            } finally {
                setLoading(false);
            }
        },
        [resolvedEndpoint],
    );

    useEffect(() => {
        load(1);
    }, [load]);

    const rows = useMemo(() => (payload ? extractRows(payload) : null), [payload]);
    const columns = useMemo(() => inferColumns(rows), [rows]);
    const pagination = useMemo(() => (payload ? normalizePagination(payload) : null), [payload]);
    const meta = payload?.meta;

    const objectFallback =
        !rows &&
        meta?.data &&
        typeof meta.data === 'object' &&
        !Array.isArray(meta.data) &&
        !meta.data.data &&
        meta.data;

    const topLevelObject =
        !rows &&
        !objectFallback &&
        payload?.data &&
        typeof payload.data === 'object' &&
        !Array.isArray(payload.data) &&
        !Array.isArray(payload.data.data) &&
        payload.data;

    const actions = (
        <div className="flex flex-wrap gap-2">
            {backLink ? (
                <Link
                    to={backLink.to}
                    className="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                >
                    {backLink.label || 'Back'}
                </Link>
            ) : null}
            <button
                type="button"
                onClick={() => load(page)}
                className="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Reload
            </button>
        </div>
    );

    const content = (
        <div className="mx-auto max-w-[1600px] space-y-6 p-6">
            <AccountPageHeader title={title || 'Data'} actions={actions} />
            {subtitle ? <p className="-mt-2 text-sm text-gray-500">{subtitle}</p> : null}
            {err ? <p className="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{err}</p> : null}

            <AccountCard>
                {loading ? (
                    <div className="p-6">
                        <AccountFullPageLoader text="Loading…" />
                    </div>
                ) : (
                    <div className="p-5">
                        {rows && columns.length ? (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        {columns.map((c) => (
                                            <AccountTH key={c}>{c.replace(/_/g, ' ')}</AccountTH>
                                        ))}
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-gray-100 bg-white">
                                    {rows.map((row, idx) => (
                                        <AccountTR key={row.id ?? idx}>
                                            <AccountTD className="tabular-nums text-gray-500">
                                                {(pagination?.per_page || rows.length) * ((pagination?.current_page || 1) - 1) + idx + 1}
                                            </AccountTD>
                                            {columns.map((c) => (
                                                <AccountTD key={c} className="max-w-[260px] truncate" title={cellText(row?.[c])}>
                                                    {cellText(row?.[c])}
                                                </AccountTD>
                                            ))}
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        ) : objectFallback ? (
                            <KeyValueGrid title={meta?.title || 'Values'} object={objectFallback} />
                        ) : topLevelObject ? (
                            <KeyValueGrid title="Payload" object={topLevelObject} />
                        ) : (
                            <AccountEmptyState message="Nothing to show in table or key/value form. Inspect raw JSON below." />
                        )}

                        {meta ? <MetaExtras meta={meta} /> : null}

                        {pagination && pagination.last_page > 1 ? (
                            <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                                <span className="text-sm text-gray-500">
                                    Page {pagination.current_page} of {pagination.last_page} · {pagination.total} total
                                </span>
                                <div className="flex gap-2">
                                    <button
                                        type="button"
                                        disabled={loading || pagination.current_page <= 1}
                                        onClick={() => load(pagination.current_page - 1)}
                                        className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                                    >
                                        Previous
                                    </button>
                                    <button
                                        type="button"
                                        disabled={loading || pagination.current_page >= pagination.last_page}
                                        onClick={() => load(pagination.current_page + 1)}
                                        className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        ) : null}

                        <div className="mt-6 border-t border-gray-100 pt-4">
                            <button
                                type="button"
                                onClick={() => setShowRaw((s) => !s)}
                                className="text-sm font-medium text-blue-600 hover:text-blue-800"
                            >
                                {showRaw ? 'Hide' : 'Show'} raw JSON
                            </button>
                            {showRaw ? (
                                <pre className="mt-3 max-h-[50vh] overflow-auto rounded-lg border border-gray-200 bg-gray-900 p-4 text-xs text-green-100">
                                    {JSON.stringify(payload, null, 2)}
                                </pre>
                            ) : null}
                        </div>
                    </div>
                )}
            </AccountCard>
        </div>
    );

    return <Layout>{content}</Layout>;
}
