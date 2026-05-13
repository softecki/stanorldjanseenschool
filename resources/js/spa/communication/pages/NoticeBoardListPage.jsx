import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateState } from '../CommunicationModuleShared';
import {
    IconPlus,
    UiActionGroup,
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

function textPreview(html, max = 96) {
    const t = String(html || '')
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    if (!t) return '—';
    return t.length > max ? `${t.slice(0, max)}…` : t;
}

function formatDateTime(value) {
    if (!value) return '—';
    try {
        const d = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return String(value);
        return d.toLocaleString(undefined, {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    } catch {
        return String(value);
    }
}

function formatDateOnly(value) {
    if (!value) return '—';
    try {
        const d = new Date(String(value));
        if (Number.isNaN(d.getTime())) return String(value);
        return d.toLocaleDateString(undefined, { dateStyle: 'medium' });
    } catch {
        return String(value);
    }
}

export function NoticeBoardListPage({ Layout }) {
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
            .get(`/communication/notice-board${q}`, { headers: xhrJson })
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
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load notices.'))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load(1);
    }, [load]);

    const remove = async (id) => {
        if (!window.confirm('Delete this notice? This cannot be undone.')) return;
        try {
            await axios.delete(`/communication/notice-board/delete/${id}`, { headers: xhrJson });
            await load(page);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    const activeCount = rows.filter((r) => Number(r.status) === 1).length;

    return (
        <Shell Layout={Layout}>
            <div className="space-y-8">
                <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 px-6 py-10 text-white shadow-xl sm:px-10">
                    <div className="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl" aria-hidden />
                    <div className="pointer-events-none absolute -bottom-24 -left-12 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl" aria-hidden />
                    <div className="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-xl space-y-3">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-100/90">Communication</p>
                            <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">{meta.title || 'Notice board'}</h1>
                            <p className="text-sm leading-relaxed text-indigo-100/95">
                                Publish school-wide announcements, control who can see them in the app, and optionally show them on your public website.
                            </p>
                        </div>
                        <UiButtonLink
                            to="/communication/notice-board/create"
                            className="shrink-0 border-0 bg-white text-indigo-700 shadow-lg hover:bg-indigo-50"
                            leftIcon={<IconPlus className="h-4 w-4" />}
                        >
                            New notice
                        </UiButtonLink>
                    </div>
                    {!loading && total > 0 ? (
                        <dl className="relative mt-8 grid max-w-lg grid-cols-2 gap-3 sm:grid-cols-3">
                            <div className="rounded-xl bg-white/10 px-4 py-3 backdrop-blur-sm">
                                <dt className="text-xs font-medium uppercase tracking-wide text-indigo-100/80">Total</dt>
                                <dd className="mt-1 text-2xl font-semibold tabular-nums">{total}</dd>
                            </div>
                            <div className="rounded-xl bg-white/10 px-4 py-3 backdrop-blur-sm">
                                <dt className="text-xs font-medium uppercase tracking-wide text-indigo-100/80">On this page</dt>
                                <dd className="mt-1 text-2xl font-semibold tabular-nums">{rows.length}</dd>
                            </div>
                            <div className="col-span-2 rounded-xl bg-white/10 px-4 py-3 backdrop-blur-sm sm:col-span-1">
                                <dt className="text-xs font-medium uppercase tracking-wide text-indigo-100/80">Active here</dt>
                                <dd className="mt-1 text-2xl font-semibold tabular-nums">{activeCount}</dd>
                            </div>
                        </dl>
                    ) : null}
                </section>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading notices…" />
                ) : (
                    <>
                        <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-1 shadow-lg shadow-slate-200/50 backdrop-blur-sm">
                            <UiTableWrap className="overflow-hidden rounded-xl border-0 shadow-none">
                                <UiTable>
                                    <UiTHead className="bg-slate-50/90">
                                        <UiHeadRow>
                                            <UiTH>Notice</UiTH>
                                            <UiTH className="hidden md:table-cell">Schedule</UiTH>
                                            <UiTH className="hidden lg:table-cell">Visibility</UiTH>
                                            <UiTH className="text-right">Actions</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.length ? (
                                            rows.map((row) => (
                                                <UiTR key={row.id} className="align-top">
                                                    <UiTD className="max-w-md">
                                                        <div className="flex flex-col gap-1.5">
                                                            <span className="font-semibold text-slate-900">{row.title}</span>
                                                            <p className="text-xs leading-relaxed text-slate-500">{textPreview(row.description)}</p>
                                                            <div className="flex flex-wrap gap-2 pt-1">
                                                                <span
                                                                    className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${
                                                                        Number(row.status) === 1
                                                                            ? 'bg-emerald-100 text-emerald-800'
                                                                            : 'bg-slate-100 text-slate-600'
                                                                    }`}
                                                                >
                                                                    {Number(row.status) === 1 ? 'Active' : 'Inactive'}
                                                                </span>
                                                                {row.attachment ? (
                                                                    <span className="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                                                        Attachment
                                                                    </span>
                                                                ) : null}
                                                            </div>
                                                        </div>
                                                    </UiTD>
                                                    <UiTD className="hidden md:table-cell whitespace-nowrap text-sm text-slate-600">
                                                        <div className="flex flex-col gap-1">
                                                            <span title="Publish date">{formatDateTime(row.publish_date)}</span>
                                                            <span className="text-xs text-slate-400" title="Event / notice date">
                                                                Event: {formatDateOnly(row.date)}
                                                            </span>
                                                        </div>
                                                    </UiTD>
                                                    <UiTD className="hidden lg:table-cell">
                                                        <span
                                                            className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                                                Number(row.is_visible_web) === 1
                                                                    ? 'bg-sky-100 text-sky-800'
                                                                    : 'bg-slate-100 text-slate-600'
                                                            }`}
                                                        >
                                                            {Number(row.is_visible_web) === 1 ? 'On website' : 'App only'}
                                                        </span>
                                                    </UiTD>
                                                    <UiTD className="text-right">
                                                        <UiActionGroup
                                                            editTo={`/communication/notice-board/${row.id}/edit`}
                                                            translateTo={`/communication/notice-board/${row.id}/translate`}
                                                            onDelete={() => remove(row.id)}
                                                        />
                                                    </UiTD>
                                                </UiTR>
                                            ))
                                        ) : (
                                            <UiTableEmptyRow colSpan={4} message="No notices yet. Create your first announcement for parents and students." />
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
