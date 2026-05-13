import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, studentsTableClass } from '../FeesModuleShared';
import { FeesCollectNavTabs } from '../FeesCollectNavTabs';
import { UiButton, UiHeadRow, UiPager, UiTable, UiTableEmptyRow, UiTableWrap, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../../ui/UiKit';
import { IconBanknote, IconView } from '../../ui/UiKit';

function money(n) {
    if (n == null || n === '') return '—';
    const x = Number(n);
    if (!Number.isFinite(x)) return String(n);
    return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function normalizeWorkbench(payload) {
    const paged = payload?.data;
    if (paged && Array.isArray(paged.data)) {
        return {
            rows: paged.data,
            meta: payload?.meta || {},
            pagination: {
                current_page: paged.current_page ?? 1,
                last_page: paged.last_page ?? 1,
                per_page: paged.per_page ?? 10,
                total: paged.total ?? 0,
            },
        };
    }
    return {
        rows: [],
        meta: payload?.meta || {},
        pagination: { current_page: 1, last_page: 1, per_page: 10, total: 0 },
    };
}

export function FeesCollectionsPage({ Layout }) {
    const navigate = useNavigate();
    const [meta, setMeta] = useState({ title: 'Collect fees' });
    const [rows, setRows] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [nameQuery, setNameQuery] = useState('');
    const [classId, setClassId] = useState('');
    const [sectionId, setSectionId] = useState('');
    const [studentId, setStudentId] = useState('');
    const [page, setPage] = useState(1);

    const [sectionOptions, setSectionOptions] = useState([]);

    const load = useCallback(
        (pageToLoad) => {
            setLoading(true);
            setErr('');
            const params = { page: pageToLoad };
            if (classId) params.class = classId;
            if (sectionId) params.section = sectionId;
            if (studentId) params.student = studentId;
            if (nameQuery && nameQuery.trim() !== '') params.name = nameQuery.trim();

            return axios
                .get('/fees-collect/collect-workbench', { headers: xhrJson, params })
                .then((r) => {
                    const { rows: list, meta: m, pagination: pg } = normalizeWorkbench(r.data || {});
                    setRows(list);
                    setMeta(m || {});
                    setPagination(pg);
                    if (m?.section_options) setSectionOptions(m.section_options);
                })
                .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'))
                .finally(() => setLoading(false));
        },
        [classId, sectionId, studentId, nameQuery],
    );

    useEffect(() => {
        setPage(1);
    }, [classId, sectionId, studentId, nameQuery]);

    useEffect(() => {
        load(page);
    }, [load, page]);

    useEffect(() => {
        setSectionId('');
    }, [classId]);

    const onSearchSubmit = (e) => {
        e.preventDefault();
        setPage(1);
        load(1);
    };

    const openReceipt = (studentIdev) => {
        window.open(`/fees-collect/printReceipt/${studentIdev}`, '_blank', 'noopener,noreferrer');
    };

    const openCollect = (studentIdev) => {
        navigate(`/collections/collect/${studentIdev}`);
    };

    const classOptions = meta.class_options || [];
    const upFees = meta.links?.update_fees || '/students/update-fees';

    function collectStatusBadge(r) {
        const rem = Number(r?.remained_amount ?? 0);
        const paid = Number(r?.paid_amount ?? 0);
        const pill =
            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide ring-1';
        if (paid > 0 && rem > 0) {
            return (
                <span className={`${pill} bg-amber-50 text-amber-900 ring-amber-600/15`}>
                    <span className="h-2 w-2 rounded-full bg-amber-500 shadow-sm" aria-hidden />
                    Partial
                </span>
            );
        }
        if (rem > 0) {
            return (
                <span className={`${pill} bg-rose-50 text-rose-900 ring-rose-600/15`}>
                    <span className="h-2 w-2 rounded-full bg-rose-500 shadow-sm" aria-hidden />
                    Unpaid
                </span>
            );
        }
        return (
            <span className={`${pill} bg-emerald-50 text-emerald-900 ring-emerald-600/15`}>
                <span className="h-2 w-2 rounded-full bg-emerald-500 shadow-sm" aria-hidden />
                Paid
            </span>
        );
    }

    const th = 'text-xs font-semibold uppercase tracking-wider text-slate-600';
    const trC = 'border-slate-100 hover:!bg-slate-50';
    const perPage = pagination.per_page || 10;
    const currentPage = pagination.current_page || 1;
    const lastPage = pagination.last_page || 1;
    const totalRecords = pagination.total ?? 0;
    const rangeFrom = rows.length === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const rangeTo = rows.length === 0 ? 0 : Math.min(currentPage * perPage, totalRecords);

    return (
        <Layout>
            <div className="mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5">
                <FeesCollectNavTabs updateFeesHref={upFees} />

                <form
                    onSubmit={onSearchSubmit}
                    className="mb-4 overflow-hidden rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm sm:p-5"
                >
                    <h2 className="mb-3 text-sm font-semibold text-slate-900">Filters (legacy)</h2>
                    <div className="flex flex-wrap items-end gap-3">
                        <div className="min-w-[160px]">
                            <label className="mb-1.5 block text-xs font-medium text-slate-600">Class</label>
                            <select
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                value={classId}
                                onChange={(e) => {
                                    setClassId(e.target.value);
                                    setPage(1);
                                }}
                            >
                                <option value="">{`— ${'All classes'} —`}</option>
                                {classOptions.map((o) => (
                                    <option key={o.value} value={o.value}>
                                        {o.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="min-w-[140px]">
                            <label className="mb-1.5 block text-xs font-medium text-slate-600">Section</label>
                            <select
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                value={sectionId}
                                onChange={(e) => {
                                    setSectionId(e.target.value);
                                    setPage(1);
                                }}
                                disabled={!classId}
                            >
                                <option value="">All sections</option>
                                {sectionOptions.map((o) => (
                                    <option key={o.value} value={o.value}>
                                        {o.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="min-w-[140px]">
                            <label className="mb-1.5 block text-xs font-medium text-slate-600">Student id</label>
                            <input
                                type="text"
                                inputMode="numeric"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="Optional"
                                value={studentId}
                                onChange={(e) => {
                                    setStudentId(e.target.value);
                                    setPage(1);
                                }}
                            />
                        </div>
                        <div className="min-w-[200px] flex-1">
                            <label className="mb-1.5 block text-xs font-medium text-slate-600">Name</label>
                            <input
                                type="search"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="Search first/last name"
                                value={nameQuery}
                                onChange={(e) => setNameQuery(e.target.value)}
                            />
                        </div>
                        <UiButton type="submit" variant="primary">
                            Search
                        </UiButton>
                    </div>
                </form>

                {err ? (
                    <div className="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {loading ? <FullPageLoader text="Loading…" /> : null}

                {!loading ? (
                    <div className="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-sm">
                        <div className="flex flex-col gap-2 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                            <p className="text-sm text-slate-600">
                                {rows.length === 0
                                    ? 'No lines match the current filters.'
                                    : `Showing ${rangeFrom}–${rangeTo} of ${totalRecords} lines`}
                            </p>
                            <p className="text-xs font-medium text-slate-400">
                                Page {currentPage} of {lastPage}
                            </p>
                        </div>
                        <div className={studentsTableClass() + ' !rounded-none !border-0 !shadow-none'}>
                            <UiTableWrap className="rounded-none border-0 shadow-none ring-0">
                                <UiTable className="min-w-full divide-y divide-slate-100">
                                    <UiTHead>
                                        <UiHeadRow className="border-b border-slate-200 bg-slate-100">
                                            <UiTH className={th}>Student</UiTH>
                                            <UiTH className={th}>Fee type</UiTH>
                                            <UiTH className={th}>Class</UiTH>
                                            <UiTH className={th + ' text-right'}>Fee</UiTH>
                                            <UiTH className={th + ' text-right'}>Paid</UiTH>
                                            <UiTH className={th + ' text-right'}>Remained</UiTH>
                                            <UiTH className={th}>Status</UiTH>
                                            <UiTH className={th + ' text-right w-[1%]'}>Actions</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.length === 0 ? (
                                            <UiTableEmptyRow
                                                colSpan={8}
                                                message="No fee lines to display. Pick another filter or add assignments."
                                            />
                                        ) : (
                                            rows.map((r) => {
                                                return (
                                                    <UiTR
                                                        key={r.assign_id}
                                                        className={trC + ' cursor-pointer'}
                                                        title="Row click: open receipt (legacy)"
                                                        onClick={() => openReceipt(r.student_id)}
                                                    >
                                                        <UiTD className="!text-slate-900 text-sm font-medium">
                                                            {r.student_name || '—'}
                                                        </UiTD>
                                                        <UiTD className="text-sm text-slate-700">{r.fees_name || '—'}</UiTD>
                                                        <UiTD className="text-sm text-slate-600">{r.class_name || '—'}</UiTD>
                                                        <UiTD className="text-right text-sm tabular-nums text-slate-800">
                                                            {money(r.fees_amount)}
                                                        </UiTD>
                                                        <UiTD className="text-right text-sm tabular-nums text-slate-800">
                                                            {money(r.paid_amount)}
                                                        </UiTD>
                                                        <UiTD className="text-right text-sm tabular-nums text-slate-800">
                                                            {money(r.remained_amount)}
                                                        </UiTD>
                                                        <UiTD className="text-sm">{collectStatusBadge(r)}</UiTD>
                                                        <UiTD className="text-right" onClick={(e) => e.stopPropagation()}>
                                                            <div className="inline-flex items-center justify-end gap-1">
                                                                <button
                                                                    type="button"
                                                                    className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
                                                                    title="Collect fees (full form)"
                                                                    onClick={(e) => {
                                                                        e.stopPropagation();
                                                                        openCollect(r.student_id);
                                                                    }}
                                                                >
                                                                    <IconBanknote className="h-4 w-4 text-sky-600" aria-hidden />
                                                                </button>
                                                                <Link
                                                                    to={`/collections/${r.assign_id}`}
                                                                    className="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
                                                                    title="View assignment line details"
                                                                    aria-label="View assignment line details"
                                                                >
                                                                    <IconView className="h-4 w-4 text-indigo-600" />
                                                                </Link>
                                                            </div>
                                                        </UiTD>
                                                    </UiTR>
                                                );
                                            })
                                        )}
                                    </UiTBody>
                                </UiTable>
                            </UiTableWrap>
                        </div>
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

            </div>
        </Layout>
    );
}
