import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader } from '../FeesModuleShared';
import { FeesCollectNavTabs } from '../FeesCollectNavTabs';
import { UiHeadRow, UiPager, UiTable, UiTableEmptyRow, UiTableWrap, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../../ui/UiKit';

function money(n) {
    if (n == null || n === '') return '—';
    const x = Number(n);
    if (!Number.isFinite(x)) return String(n);
    return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function normalize(payload) {
    const paged = payload?.data;
    if (paged && Array.isArray(paged.data)) {
        return {
            rows: paged.data,
            meta: payload?.meta || {},
            pagination: {
                current_page: paged.current_page ?? 1,
                last_page: paged.last_page ?? 1,
                per_page: paged.per_page ?? 20,
                total: paged.total ?? 0,
            },
        };
    }
    return { rows: [], meta: payload?.meta || {}, pagination: { current_page: 1, last_page: 1, per_page: 20, total: 0 } };
}

export function FeesCancelledCollectPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 20, total: 0 });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);

    const load = useCallback(
        (p) => {
            setLoading(true);
            setErr('');
            return axios
                .get('/fees-collect/cancelled-collect-list', {
                    headers: { ...xhrJson, 'X-SPA-List': '1' },
                    params: { page: p },
                })
                .then((r) => {
                    const { rows: list, meta: m, pagination: pg } = normalize(r.data || {});
                    setRows(list);
                    setMeta(m || {});
                    setPagination(pg);
                })
                .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load list.'))
                .finally(() => setLoading(false));
        },
        [],
    );

    useEffect(() => {
        load(page);
    }, [load, page]);

    const th = 'text-xs font-semibold uppercase tracking-wider text-slate-600';
    const trC = 'border-slate-100 hover:!bg-slate-50';
    const currentPage = pagination.current_page || 1;
    const lastPage = pagination.last_page || 1;
    const perPage = pagination.per_page || 20;
    const total = pagination.total ?? 0;
    const rangeFrom = rows.length === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const rangeTo = rows.length === 0 ? 0 : Math.min(currentPage * perPage, total);
    const upFees = meta?.links?.update_fees || '/students/update-fees';

    return (
        <Layout>
            <div className="mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5">
                <FeesCollectNavTabs updateFeesHref={upFees} />

                {err ? <div className="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{err}</div> : null}
                {loading ? <FullPageLoader text="Loading…" /> : null}
                {!loading ? (
                    <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-100 bg-slate-50 px-4 py-2 text-sm text-slate-600 sm:px-5">
                            {rows.length ? `Showing ${rangeFrom}–${rangeTo} of ${total}` : 'No cancelled lines.'} · Page {currentPage} of {lastPage}
                        </div>
                        <UiTableWrap className="rounded-none border-0 shadow-none">
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH className={th}>Student</UiTH>
                                        <UiTH className={th}>Fee type</UiTH>
                                        <UiTH className={th}>Class</UiTH>
                                        <UiTH className={th + ' text-right'}>Remained</UiTH>
                                        <UiTH className={th}>Cancelled at</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.length === 0 ? (
                                        <UiTableEmptyRow colSpan={5} message="No cancelled collect lines." />
                                    ) : (
                                        rows.map((r) => (
                                            <UiTR key={r.assign_id} className={trC}>
                                                <UiTD className="text-sm font-medium text-slate-900">
                                                    {r.student_name || '—'}
                                                </UiTD>
                                                <UiTD className="text-sm text-slate-700">{r.fees_name || '—'}</UiTD>
                                                <UiTD className="text-sm text-slate-600">{r.class_name || '—'}</UiTD>
                                                <UiTD className="text-right text-sm tabular-nums">{money(r.remained_amount)}</UiTD>
                                                <UiTD className="text-sm text-slate-600">
                                                    {r.cancelled_at
                                                        ? new Date(r.cancelled_at).toLocaleString()
                                                        : '—'}
                                                </UiTD>
                                            </UiTR>
                                        ))
                                    )}
                                </UiTBody>
                            </UiTable>
                        </UiTableWrap>
                        <div className="border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                            <UiPager
                                className="!mt-0"
                                page={currentPage}
                                lastPage={lastPage}
                                onPrev={() => setPage((p) => Math.max(1, p - 1))}
                                onNext={() => setPage((p) => Math.min(lastPage, p + 1))}
                            />
                        </div>
                    </div>
                ) : null}
            </div>
        </Layout>
    );
}
