import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateState } from '../CommunicationModuleShared';
import {
    IconPlus,
    UiButtonLink,
    UiHeadRow,
    UiPageLoader,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../ui/UiKit';

export function SmsMailListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [from, setFrom] = useState(null);
    const [to, setTo] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    const load = useCallback((p = 1) => {
        setLoading(true);
        setErr('');
        const q = p > 1 ? `?page=${p}` : '';
        return axios
            .get(`/communication/smsmail${q}`, { headers: xhrJson })
            .then((r) => {
                const st = paginateState(r);
                setRows(st.rows);
                setPage(st.page);
                setLastPage(st.lastPage);
                setTotal(st.total);
                setFrom(st.from);
                setTo(st.to);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load SMS log.'))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load(1);
    }, [load]);

    return (
        <Shell Layout={Layout}>
            <div className="space-y-8">
                <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 via-indigo-600 to-violet-700 px-6 py-9 text-white shadow-xl sm:px-10">
                    <div className="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/10 blur-3xl" aria-hidden />
                    <div className="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-2xl space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-sky-100/90">Communication</p>
                            <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">{meta.title || 'SMS / Mail'}</h1>
                            <p className="text-sm leading-relaxed text-indigo-100/95">
                                Delivery log from the SMS gateway (<code className="rounded bg-white/15 px-1.5 py-0.5 text-xs">sms_tracking</code>
                                ). Use <strong>Create send</strong> to dispatch SMS or mail; use <strong>Campaign</strong> for bulk campaign tools.
                            </p>
                        </div>
                        <div className="flex flex-shrink-0 flex-wrap gap-2">
                            <a
                                href="/communication/template/delivery"
                                className="inline-flex items-center justify-center gap-2 rounded-lg border border-white/30 bg-white/10 px-3.5 py-2 text-sm font-medium text-white shadow-sm backdrop-blur-sm transition hover:bg-white/20"
                            >
                                Delivery check
                            </a>
                            <UiButtonLink variant="secondary" to="/communication/smsmail/campaign" className="border-white/30 bg-white/10 text-white hover:bg-white/20">
                                Campaign
                            </UiButtonLink>
                            <UiButtonLink
                                to="/communication/smsmail/create"
                                className="border-0 bg-white text-indigo-700 shadow-lg hover:bg-indigo-50"
                                leftIcon={<IconPlus className="h-4 w-4" />}
                            >
                                Create send
                            </UiButtonLink>
                        </div>
                    </div>
                    {!loading && total > 0 ? (
                        <p className="relative mt-6 text-sm text-indigo-100/90">
                            <span className="font-semibold tabular-nums text-white">{total}</span> record{total === 1 ? '' : 's'} total
                        </p>
                    ) : null}
                </section>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading SMS log…" />
                ) : (
                    <>
                        <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-1 shadow-lg shadow-slate-200/50">
                            <UiTableWrap className="overflow-hidden rounded-xl border-0 shadow-none">
                                <UiTable className="text-sm">
                                    <UiTHead className="bg-slate-50/90">
                                        <UiHeadRow>
                                            <UiTH className="whitespace-nowrap">#</UiTH>
                                            <UiTH>To</UiTH>
                                            <UiTH className="hidden min-w-[12rem] md:table-cell">Message</UiTH>
                                            <UiTH className="hidden lg:table-cell">Status</UiTH>
                                            <UiTH className="hidden xl:table-cell">Group</UiTH>
                                            <UiTH className="hidden xl:table-cell max-w-xs">Detail</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.length ? (
                                            rows.map((row, idx) => (
                                                <UiTR key={row.id ?? idx}>
                                                    <UiTD className="whitespace-nowrap text-slate-500">{(from ?? 0) + idx}</UiTD>
                                                    <UiTD className="max-w-[10rem] font-mono text-xs text-slate-900 sm:max-w-xs">{row.to ?? '—'}</UiTD>
                                                    <UiTD className="hidden max-w-md md:table-cell">
                                                        <span className="line-clamp-3 text-slate-700" title={row.sms}>
                                                            {row.sms ?? '—'}
                                                        </span>
                                                    </UiTD>
                                                    <UiTD className="hidden lg:table-cell text-slate-700">{row.status_name ?? row.status_id ?? '—'}</UiTD>
                                                    <UiTD className="hidden xl:table-cell text-slate-600">{row.status_groupName ?? row.status_groupname ?? '—'}</UiTD>
                                                    <UiTD className="hidden max-w-sm xl:table-cell text-xs text-slate-500">
                                                        {row.status_description ?? '—'}
                                                    </UiTD>
                                                </UiTR>
                                            ))
                                        ) : (
                                            <UiTableEmptyRow
                                                colSpan={6}
                                                message="No delivery rows yet. After you send SMS from Create send, gateway responses appear here."
                                            />
                                        )}
                                    </UiTBody>
                                </UiTable>
                            </UiTableWrap>
                        </div>

                        {total > 0 && from != null && to != null ? (
                            <p className="text-center text-xs text-slate-500">
                                Showing <span className="font-medium text-slate-700">{from}</span>–
                                <span className="font-medium text-slate-700">{to}</span> of{' '}
                                <span className="font-medium text-slate-700">{total}</span>
                            </p>
                        ) : null}

                        <UiPager
                            page={page}
                            lastPage={lastPage}
                            onPrev={() => load(page - 1)}
                            onNext={() => load(page + 1)}
                            className="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3"
                        />
                    </>
                )}
            </div>
        </Shell>
    );
}
