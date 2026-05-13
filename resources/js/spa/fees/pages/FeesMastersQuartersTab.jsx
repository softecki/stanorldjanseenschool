import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { normalizeFeesPagedList } from '../FeesModuleShared';
import {
    UiButton,
    UiHeadRow,
    UiInlineLoader,
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

function formatMoney(v) {
    if (v == null || v === '') return '—';
    const n = Number(v);
    if (Number.isNaN(n)) return '—';
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function parseAmount(v) {
    const n = parseFloat(String(v).replace(/,/g, ''));
    return Number.isFinite(n) ? n : 0;
}

export function FeesMastersQuartersTab() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);
    const [modalRow, setModalRow] = useState(null);
    const [amounts, setAmounts] = useState(['', '', '', '']);
    const [saving, setSaving] = useState(false);
    const [modalErr, setModalErr] = useState('');

    const fetchList = useCallback((pageToLoad) => {
        setLoading(true);
        setErr('');
        return axios
            .get('/fees-master/quarters-overview', { headers: xhrJson, params: { page: pageToLoad } })
            .then((r) => {
                const { rows: list, meta: m, pagination: pg } = normalizeFeesPagedList(r.data || {});
                setRows(list);
                setMeta(m || {});
                setPagination(pg);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load quarters overview.'))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        fetchList(page);
    }, [page, fetchList]);

    const openModal = (row) => {
        setModalErr('');
        setModalRow(row);
        setAmounts([
            String(row?.quater_one ?? ''),
            String(row?.quater_two ?? ''),
            String(row?.quater_three ?? ''),
            String(row?.quater_four ?? ''),
        ]);
    };

    const closeModal = () => {
        if (saving) return;
        setModalRow(null);
    };

    const sumPreview = amounts.reduce((a, b) => a + parseAmount(b), 0);

    const saveQuarters = async (e) => {
        e.preventDefault();
        if (!modalRow?.id) return;
        setSaving(true);
        setModalErr('');
        try {
            const payload = {
                amounts: amounts.map((x) => parseAmount(x)),
            };
            await axios.put(`/fees-master/${modalRow.id}/quarters`, payload, {
                headers: { ...xhrJson, 'Content-Type': 'application/json' },
            });
            closeModal();
            await fetchList(page);
        } catch (ex) {
            const d = ex.response?.data;
            setModalErr(d?.message || (d?.errors ? JSON.stringify(d.errors) : 'Save failed.'));
        } finally {
            setSaving(false);
        }
    };

    const perPage = pagination.per_page || 10;
    const currentPage = pagination.current_page || 1;
    const lastPage = pagination.last_page || 1;
    const totalRecords = pagination.total ?? rows.length;
    const rangeFrom = rows.length === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const rangeTo = rows.length === 0 ? 0 : Math.min(currentPage * perPage, totalRecords);

    return (
        <div>
            <div className="mb-6 rounded-xl border border-indigo-100 bg-indigo-50/80 px-4 py-3 text-sm text-indigo-950 sm:px-5">
                <p className="font-semibold text-indigo-900">How quarters work</p>
                <p className="mt-1 leading-relaxed text-indigo-900/90">
                    Configure four quarter amounts per fee master. The master&apos;s total amount is set to the sum of those quarters. When students are
                    assigned this fee, each quarter column uses these amounts. If you do not configure quarters, the system keeps dividing the total fee by
                    four (equal quarters).
                </p>
            </div>

            {err ? <p className="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{err}</p> : null}

            {loading ? <UiPageLoader text="Loading fee masters…" /> : null}

            {!loading ? (
                <div className="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/[0.04]">
                    <div className="flex flex-col gap-2 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <p className="text-sm text-slate-600">
                            Showing {rangeFrom}–{rangeTo} of {totalRecords} fee masters
                        </p>
                        <p className="text-xs font-medium text-slate-400">
                            Page {currentPage} of {lastPage}
                        </p>
                    </div>
                    <UiTableWrap className="rounded-none border-0 shadow-none ring-0">
                        <UiTable className="min-w-full divide-y divide-slate-100">
                            <UiTHead>
                                <UiHeadRow className="border-b border-slate-200 bg-slate-100">
                                    <UiTH className="w-12 text-center text-xs font-semibold uppercase tracking-wider text-slate-600">#</UiTH>
                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Group</UiTH>
                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Type</UiTH>
                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Session</UiTH>
                                    <UiTH className="text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Total</UiTH>
                                    <UiTH className="text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Q1</UiTH>
                                    <UiTH className="text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Q2</UiTH>
                                    <UiTH className="text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Q3</UiTH>
                                    <UiTH className="text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Q4</UiTH>
                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Mode</UiTH>
                                    <UiTH className="text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Quarters</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.length === 0 ? (
                                    <UiTableEmptyRow colSpan={11} message="No fee masters for this session." />
                                ) : (
                                    rows.map((row, idx) => (
                                        <UiTR key={row.id} className="border-slate-100 hover:!bg-slate-50">
                                            <UiTD className="text-center text-xs text-slate-500">{(currentPage - 1) * perPage + idx + 1}</UiTD>
                                            <UiTD className="text-sm font-medium text-slate-900">{row?.group?.name || '—'}</UiTD>
                                            <UiTD className="text-sm text-slate-800">{row?.type?.name || '—'}</UiTD>
                                            <UiTD className="whitespace-nowrap text-sm text-slate-600">{row?.session?.name || '—'}</UiTD>
                                            <UiTD className="text-right text-sm tabular-nums font-medium text-slate-900">{formatMoney(row?.amount)}</UiTD>
                                            <UiTD className="text-right text-sm tabular-nums text-slate-700">{formatMoney(row?.quater_one)}</UiTD>
                                            <UiTD className="text-right text-sm tabular-nums text-slate-700">{formatMoney(row?.quater_two)}</UiTD>
                                            <UiTD className="text-right text-sm tabular-nums text-slate-700">{formatMoney(row?.quater_three)}</UiTD>
                                            <UiTD className="text-right text-sm tabular-nums text-slate-700">{formatMoney(row?.quater_four)}</UiTD>
                                            <UiTD className="text-sm">
                                                {row?.uses_custom_quarters ? (
                                                    <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-600/15">
                                                        Custom
                                                    </span>
                                                ) : (
                                                    <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-slate-500/10">
                                                        ÷4 default
                                                    </span>
                                                )}
                                            </UiTD>
                                            <UiTD className="text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => openModal(row)}
                                                    className="text-sm font-semibold text-indigo-700 hover:underline"
                                                >
                                                    Set amounts
                                                </button>
                                            </UiTD>
                                        </UiTR>
                                    ))
                                )}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                    <div className="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <UiPager
                            className="!mt-0 w-full sm:w-auto"
                            page={currentPage}
                            lastPage={lastPage}
                            onPrev={() => setPage((p) => Math.max(1, p - 1))}
                            onNext={() => setPage((p) => Math.min(lastPage, p + 1))}
                        />
                    </div>
                </div>
            ) : null}

            {modalRow ? (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="q-modal-title">
                    <button
                        type="button"
                        className="absolute inset-0 bg-slate-900/50"
                        aria-label="Close"
                        onClick={closeModal}
                    />
                    <div className="relative z-10 w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl">
                        <h2 id="q-modal-title" className="text-lg font-bold text-slate-900">
                            Quarter amounts
                        </h2>
                        <p className="mt-1 text-sm text-slate-600">
                            {modalRow?.group?.name} · {modalRow?.type?.name}
                        </p>
                        <p className="mt-2 text-xs text-slate-500">
                            Saving updates the fee master total to the sum of Q1–Q4. New student assignments will use these quarter splits (when due-date
                            rules apply).
                        </p>
                        <form onSubmit={saveQuarters} className="mt-5 space-y-4">
                            {[0, 1, 2, 3].map((i) => (
                                <label key={i} className="block">
                                    <span className="text-sm font-medium text-slate-700">Quarter {i + 1}</span>
                                    <input
                                        type="text"
                                        inputMode="decimal"
                                        className="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm tabular-nums shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                        value={amounts[i]}
                                        onChange={(e) => {
                                            const next = [...amounts];
                                            next[i] = e.target.value;
                                            setAmounts(next);
                                        }}
                                        required
                                    />
                                </label>
                            ))}
                            <p className="text-sm text-slate-700">
                                Sum: <span className="font-semibold tabular-nums">{formatMoney(sumPreview)}</span> (saved as master total)
                            </p>
                            {modalErr ? <p className="text-sm text-red-600">{modalErr}</p> : null}
                            <div className="flex justify-end gap-2 border-t border-slate-100 pt-4">
                                <button
                                    type="button"
                                    onClick={closeModal}
                                    className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                >
                                    Cancel
                                </button>
                                <UiButton type="submit" disabled={saving} className="bg-indigo-600 text-white hover:bg-indigo-700">
                                    {saving ? (
                                        <>
                                            <UiInlineLoader /> Saving…
                                        </>
                                    ) : (
                                        'Save quarters'
                                    )}
                                </UiButton>
                            </div>
                        </form>
                        <p className="mt-4 text-center text-xs text-slate-500">
                            <Link to={`/masters/${modalRow.id}/edit`} className="font-medium text-indigo-700 hover:underline">
                                Edit full fee master
                            </Link>
                        </p>
                    </div>
                </div>
            ) : null}
        </div>
    );
}
