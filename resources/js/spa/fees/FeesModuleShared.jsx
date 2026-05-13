import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../api/xhrJson';
import {
    IconBanknote,
    IconPlus,
    IconReceipt,
    IconView,
    IconX,
    UiActionGroup,
    UiButton,
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
    uiIconBtnClass,
} from '../ui/UiKit';

export { UiPageLoader as FullPageLoader };
export { UiActionGroup as ActionButtons };

export function panelTitle(metaTitle, fallback) {
    return metaTitle || fallback;
}

export function optionLabel(item) {
    return item?.name || item?.title || item?.class?.name || item?.group?.name || `#${item?.id ?? '-'}`;
}

/** @deprecated Prefer <UiTableWrap> from ../ui/UiKit — kept for existing fee pages. */
export function studentsTableClass() {
    return 'overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm';
}

function normalizeRowsPayload(payload) {
    if (Array.isArray(payload?.data?.data)) return payload.data.data;
    if (Array.isArray(payload?.data)) return payload.data;
    if (Array.isArray(payload)) return payload;
    return [];
}

/** Laravel paginate JSON: { data: { data: rows[], current_page, ... }, meta } — keep in fees module to avoid import cycles. */
export function normalizeFeesPagedList(payload) {
    const paged = payload?.data;
    if (paged && Array.isArray(paged.data)) {
        return {
            rows: paged.data,
            meta: payload?.meta || {},
            pagination: {
                current_page: paged.current_page ?? 1,
                last_page: paged.last_page ?? 1,
                per_page: paged.per_page ?? 10,
                total: paged.total ?? paged.data.length,
            },
        };
    }
    const rows = normalizeRowsPayload(payload);
    return {
        rows,
        meta: payload?.meta || {},
        pagination: { current_page: 1, last_page: 1, per_page: 10, total: rows.length },
    };
}

function listRangeLabel({ from, to, total, entityLabel = 'records' }) {
    if (total === 0) return `No ${entityLabel} match the current criteria.`;
    return `Showing ${from}–${to} of ${total} ${entityLabel}`;
}

export function EntityListPage({
    Layout,
    title = 'List',
    endpoint,
    baseRoute,
    createRoute,
    deleteEndpoint,
    columns = [],
    /** @type {'default' | 'corporate'} */
    variant = 'default',
    subtitle,
    createButtonLabel,
    entityLabel,
    hideEyebrow = false,
    hideTitle = false,
    ignoreMetaTitle = false,
    /** When true, render list content only (parent supplies {@link Layout}). */
    skipLayout = false,
}) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [busyId, setBusyId] = useState(null);
    const [page, setPage] = useState(1);

    const fetchList = useCallback(
        (pageToLoad, { showLoader = true } = {}) => {
            if (!endpoint) return;
            if (showLoader) setLoading(true);
            setErr('');
            return axios
                .get(endpoint, { headers: xhrJson, params: { page: pageToLoad } })
                .then((r) => {
                    const { rows: list, meta: m, pagination: pg } = normalizeFeesPagedList(r.data || {});
                    setRows(list);
                    setMeta(m || {});
                    setPagination(pg);
                })
                .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'))
                .finally(() => {
                    if (showLoader) setLoading(false);
                });
        },
        [endpoint],
    );

    useEffect(() => {
        setPage(1);
    }, [endpoint]);

    useEffect(() => {
        if (!endpoint) return;
        fetchList(page, { showLoader: true });
    }, [endpoint, page, fetchList]);

    const perPage = pagination.per_page || 10;
    const currentPage = pagination.current_page || 1;
    const lastPage = pagination.last_page || 1;
    const emptyColSpan = (columns?.length || 0) + 2;
    const isCorporate = variant === 'corporate';
    const totalRecords = pagination.total ?? rows.length;
    const rangeFrom = rows.length === 0 ? 0 : (currentPage - 1) * perPage + 1;
    const rangeTo = rows.length === 0 ? 0 : Math.min(currentPage * perPage, totalRecords);
    const listEntityLabel = entityLabel || 'records';
    const addLabel = createButtonLabel || 'Create';
    const headerTitle = ignoreMetaTitle ? title : panelTitle(meta?.title, title);
    const showEyebrow = isCorporate && !hideEyebrow;
    const showTitle = !hideTitle && Boolean(headerTitle);
    const hasIntroContent = showEyebrow || showTitle || Boolean(subtitle);
    /** Less padding when only the action row shows (e.g. /groups) — avoids large gap under app bar. */
    const corporateCompact = isCorporate && !hasIntroContent;

    const remove = async (id) => {
        if (!window.confirm('Delete this item?')) return;
        setBusyId(id);
        setErr('');
        try {
            await axios.delete(`${deleteEndpoint}/${id}`, { headers: xhrJson });
            const p = lastPage > 1 && rows.length === 1 && currentPage > 1 ? currentPage - 1 : currentPage;
            if (p !== currentPage) {
                setPage(p);
            } else {
                await fetchList(p, { showLoader: false });
            }
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        } finally {
            setBusyId(null);
        }
    };

    const thBase = isCorporate ? 'text-xs font-semibold uppercase tracking-wider text-slate-600' : '';
    const thRowClass = isCorporate ? 'border-b border-slate-200 bg-slate-100' : '';
    const trClass = isCorporate ? 'border-slate-100 hover:!bg-slate-50' : '';

    const inner = (
        <div
            className={
                isCorporate
                    ? corporateCompact
                        ? 'mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-1.5 sm:px-6 sm:py-2 lg:px-8 lg:py-2.5'
                        : 'mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-8 sm:px-6 lg:px-8 lg:py-10'
                    : 'mx-auto max-w-7xl p-6'
            }
        >
                {isCorporate ? (
                    <div
                        className={`flex flex-col border-b border-slate-200/80 lg:flex-row lg:items-end ${
                            corporateCompact ? 'mb-3 gap-2 pb-3' : 'mb-8 gap-6 pb-8'
                        } ${hasIntroContent ? 'lg:justify-between' : 'lg:justify-end'}`}
                    >
                        {hasIntroContent ? (
                            <div className="min-w-0">
                                {showEyebrow ? (
                                    <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Fees configuration</p>
                                ) : null}
                                {showTitle ? (
                                    <h1 className="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                                        {headerTitle}
                                    </h1>
                                ) : null}
                            {subtitle ? <p className="mt-3 max-w-2xl text-sm leading-relaxed text-slate-600">{subtitle}</p> : null}
                            </div>
                        ) : null}
                        <div className="flex shrink-0 flex-wrap gap-2">
                            <UiButtonLink
                                to={createRoute}
                                variant="primary"
                                leftIcon={<IconPlus />}
                                className="shadow-md shadow-slate-900/10"
                            >
                                {addLabel}
                            </UiButtonLink>
                        </div>
                    </div>
                ) : (
                    <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <h1 className="text-2xl font-semibold text-gray-800">{panelTitle(meta?.title, title)}</h1>
                            <UiButtonLink to={createRoute} variant="primary" leftIcon={<IconPlus />}>
                                {addLabel}
                            </UiButtonLink>
                        </div>
                    </div>
                )}
                {err ? (
                    <p className={`mb-3 text-sm text-red-600 ${isCorporate ? 'rounded-lg border border-red-200 bg-red-50 px-4 py-3' : ''}`}>{err}</p>
                ) : null}
                {loading ? <UiPageLoader text={`Loading ${String(title ?? 'list').toLowerCase()}…`} /> : null}
                {!loading ? (
                    <>
                        {isCorporate ? (
                            <div className="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-[0_1px_3px_rgba(15,23,42,0.06),0_4px_12px_rgba(15,23,42,0.04)] ring-1 ring-slate-900/[0.04]">
                                <div className="flex flex-col gap-2 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                                    <p className="text-sm text-slate-600">
                                        {listRangeLabel({
                                            from: rangeFrom,
                                            to: rangeTo,
                                            total: totalRecords,
                                            entityLabel: listEntityLabel,
                                        })}
                                    </p>
                                    <p className="text-xs font-medium text-slate-400">Page {currentPage} of {lastPage}</p>
                                </div>
                                <UiTableWrap className="rounded-none border-0 shadow-none ring-0">
                                    <UiTable className={isCorporate ? 'min-w-full divide-y divide-slate-100' : ''}>
                                        <UiTHead>
                                            <UiHeadRow className={thRowClass}>
                                                <UiTH className={`w-14 text-center ${thBase}`}>#</UiTH>
                                                {columns.map((c) => (
                                                    <UiTH key={c.key} className={`${thBase} ${c.thClassName || ''}`}>
                                                        {c.label}
                                                    </UiTH>
                                                ))}
                                                <UiTH className={`text-right ${thBase}`}>Actions</UiTH>
                                            </UiHeadRow>
                                        </UiTHead>
                                        <UiTBody>
                                            {rows.length === 0 ? (
                                                <UiTableEmptyRow colSpan={emptyColSpan} message="No records to display." />
                                            ) : (
                                                rows.map((row, idx) => (
                                                    <UiTR key={row.id != null ? row.id : idx} className={trClass}>
                                                        <UiTD
                                                            className={`whitespace-nowrap text-center tabular-nums ${
                                                                isCorporate ? 'text-xs text-slate-500' : 'text-gray-500'
                                                            }`}
                                                        >
                                                            {(currentPage - 1) * perPage + idx + 1}
                                                        </UiTD>
                                                        {columns.map((c) => (
                                                            <UiTD
                                                                key={c.key}
                                                                className={`align-middle ${isCorporate ? 'text-sm !text-slate-800' : ''} ${c.tdClassName || ''}`}
                                                            >
                                                                {c.render ? c.render(row) : (row?.[c.key] ?? '—')}
                                                            </UiTD>
                                                        ))}
                                                        <UiTD className="text-right align-middle">
                                                            <div className="flex justify-end">
                                                                {row.id != null ? (
                                                                    <UiActionGroup
                                                                        viewTo={`${baseRoute}/${row.id}`}
                                                                        editTo={`${baseRoute}/${row.id}/edit`}
                                                                        onDelete={() => remove(row.id)}
                                                                        busy={busyId === row.id}
                                                                    />
                                                                ) : (
                                                                    <span className="text-xs text-gray-400">—</span>
                                                                )}
                                                            </div>
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
                        ) : (
                            <>
                                <UiTableWrap>
                                    <UiTable>
                                        <UiTHead>
                                            <UiHeadRow>
                                                <UiTH className="w-12">#</UiTH>
                                                {columns.map((c) => (
                                                    <UiTH key={c.key} className={c.thClassName || ''}>
                                                        {c.label}
                                                    </UiTH>
                                                ))}
                                                <UiTH className="text-right">Actions</UiTH>
                                            </UiHeadRow>
                                        </UiTHead>
                                        <UiTBody>
                                            {rows.length === 0 ? (
                                                <UiTableEmptyRow colSpan={emptyColSpan} message="No records to display." />
                                            ) : (
                                                rows.map((row, idx) => (
                                                    <UiTR key={row.id != null ? row.id : idx}>
                                                        <UiTD className="whitespace-nowrap text-gray-500">
                                                            {(currentPage - 1) * perPage + idx + 1}
                                                        </UiTD>
                                                        {columns.map((c) => (
                                                            <UiTD key={c.key} className={c.tdClassName || ''}>
                                                                {c.render ? c.render(row) : (row?.[c.key] ?? '—')}
                                                            </UiTD>
                                                        ))}
                                                        <UiTD className="text-right">
                                                            <div className="flex justify-end">
                                                                {row.id != null ? (
                                                                    <UiActionGroup
                                                                        viewTo={`${baseRoute}/${row.id}`}
                                                                        editTo={`${baseRoute}/${row.id}/edit`}
                                                                        onDelete={() => remove(row.id)}
                                                                        busy={busyId === row.id}
                                                                    />
                                                                ) : (
                                                                    <span className="text-xs text-gray-400">—</span>
                                                                )}
                                                            </div>
                                                        </UiTD>
                                                    </UiTR>
                                                ))
                                            )}
                                        </UiTBody>
                                    </UiTable>
                                </UiTableWrap>
                                <UiPager
                                    className="mt-4"
                                    page={currentPage}
                                    lastPage={lastPage}
                                    onPrev={() => setPage((p) => Math.max(1, p - 1))}
                                    onNext={() => setPage((p) => Math.min(lastPage, p + 1))}
                                />
                            </>
                        )}
                    </>
                ) : null}
            </div>
    );

    if (skipLayout) {
        return inner;
    }

    return <Layout>{inner}</Layout>;
}

function entityViewFormatValue(v) {
    if (v === null || v === undefined) return '—';
    if (typeof v !== 'object') return String(v);
    try {
        return JSON.stringify(v);
    } catch {
        return '[Unable to display]';
    }
}

export function EntityViewPage({ Layout, title, loadEndpoint, backTo, editBase }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    useEffect(() => {
        setLoading(true);
        axios
            .get(`${loadEndpoint}/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const raw = r.data?.data;
                if (raw != null && typeof raw === 'object' && !Array.isArray(raw)) {
                    setData(raw);
                } else {
                    setData({});
                    setErr('Unexpected response from server.');
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'))
            .finally(() => setLoading(false));
    }, [id, loadEndpoint]);

    const entries = useMemo(() => {
        const d = data;
        if (d == null || typeof d !== 'object' || Array.isArray(d)) return [];
        return Object.entries(d);
    }, [data]);

    return (
        <Layout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{title}</h1>
                </div>
                {loading ? <UiPageLoader text="Loading details..." /> : null}
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {!loading ? <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <dl className="grid gap-3 text-sm md:grid-cols-2">
                        {entries.map(([k, v]) => (
                            <div key={k}>
                                <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{String(k).replaceAll('_', ' ')}</dt>
                                <dd className="mt-1 text-gray-800">{entityViewFormatValue(v)}</dd>
                            </div>
                        ))}
                    </dl>
                    <div className="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-3">
                        <UiButtonLink to={backTo} variant="secondary">
                            Back
                        </UiButtonLink>
                        <UiButtonLink to={`${editBase}/${id}/edit`} variant="primary">
                            Edit
                        </UiButtonLink>
                    </div>
                </div> : null}
            </div>
        </Layout>
    );
}

export function statusChoices() {
    return (
        <>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </>
    );
}

/** Map API edit payload to form state (strip nested relations, coerce types). */
export function FeesEntityFormPage({
    Layout,
    edit = false,
    titleCreate,
    titleEdit,
    subtitleCreate,
    subtitleEdit,
    loadUrl,
    createUrl,
    updateUrl,
    backTo,
    fields,
    mapLoadedData,
    buildSubmitPayload,
}) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setLoading(true);
        const url = edit ? `${loadUrl}/edit/${id}` : `${loadUrl}/create`;
        axios.get(url, { headers: xhrJson }).then((r) => {
            const top = r.data && typeof r.data === 'object' ? r.data : {};
            const m = top.meta && typeof top.meta === 'object' ? { ...top.meta } : {};
            if (!Array.isArray(m.student_categories) && Array.isArray(top.student_categories)) {
                m.student_categories = top.student_categories;
            }
            if (!Array.isArray(m.classes) && Array.isArray(top.classes)) {
                m.classes = top.classes;
            }
            setMeta(m);
            if (edit) {
                let raw = r.data?.data;
                if (raw == null || typeof raw !== 'object') raw = {};
                else raw = { ...raw };
                if (mapLoadedData) {
                    setForm(mapLoadedData(raw));
                } else {
                    if (Object.prototype.hasOwnProperty.call(raw, 'status') && String(raw.status) === '2') {
                        raw.status = '0';
                    }
                    setForm(raw);
                }
            } else {
                const seed = r.data?.data;
                if (mapLoadedData) {
                    const base = seed != null && typeof seed === 'object' && !Array.isArray(seed) ? { ...seed } : {};
                    setForm(mapLoadedData(base));
                } else {
                    setForm((f) => ({ status: '1', ...f }));
                }
            }
        }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.')).finally(() => setLoading(false));
    }, [edit, id, loadUrl, mapLoadedData]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            const payload = buildSubmitPayload ? buildSubmitPayload(form) : form;
            if (edit) await axios.put(`${updateUrl}/${id}`, payload, { headers: xhrJson });
            else await axios.post(createUrl, payload, { headers: xhrJson });
            nav(backTo);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };

    const headline = edit ? titleEdit : titleCreate;
    const subline = edit ? subtitleEdit : subtitleCreate;
    const hasHeadline = headline != null && String(headline).trim() !== '';
    const hasSubline = subline != null && String(subline).trim() !== '';
    const showFormHeader = hasHeadline || hasSubline;
    const shellClass = showFormHeader
        ? 'mx-auto max-w-6xl p-6'
        : 'mx-auto max-w-6xl px-6 pb-6 pt-0 sm:px-6 sm:pb-8';

    return (
        <Layout>
            <div className={shellClass}>
                {showFormHeader ? (
                    <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        {hasHeadline ? <h1 className="text-2xl font-semibold text-gray-800">{headline}</h1> : null}
                        {hasSubline ? (
                            <p className={`max-w-3xl text-sm text-gray-600 ${hasHeadline ? 'mt-2' : ''}`}>{subline}</p>
                        ) : null}
                    </div>
                ) : null}
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading form..." /> : null}
                {!loading ? <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2">
                    {fields({ meta, form, setForm, statusChoices, edit, id })}
                    <div className="md:col-span-2 flex justify-end gap-2 border-t border-gray-100 pt-3">
                        <UiButtonLink to={backTo} variant="secondary">
                            Cancel
                        </UiButtonLink>
                        <UiButton type="submit" variant="primary" disabled={saving}>
                            {saving ? 'Saving...' : edit ? 'Update' : 'Create'}
                        </UiButton>
                    </div>
                </form> : null}
            </div>
        </Layout>
    );
}


export function normalizeFeesTransactionRows(payload) {
    if (payload == null) return [];
    if (Array.isArray(payload)) return payload;
    if (typeof payload === 'object' && Array.isArray(payload.data)) return payload.data;
    if (typeof payload === 'object' && payload !== null && !Array.isArray(payload)) {
        const keys = Object.keys(payload);
        if (
            keys.length > 0
            && keys.every((k) => /^(0|[1-9]\d*)$/.test(k))
            && Object.values(payload).every((v) => v != null && typeof v === 'object' && !Array.isArray(v))
        ) {
            return keys
                .sort((a, b) => Number(a) - Number(b))
                .map((k) => payload[k]);
        }
    }
    return [];
}

/**
 * With Axios `responseType: 'blob'`, Laravel JSON errors arrive as Blob bodies.
 * Optionally cap read size so huge PDF blobs are never fully stringified.
 */
async function laravelMessageFromBlob(blob, maxBytes = null) {
    if (!blob || typeof blob.text !== 'function') return null;
    const slice = maxBytes != null && blob.size > maxBytes ? blob.slice(0, maxBytes) : blob;
    let text = '';
    try {
        text = await slice.text();
    } catch {
        return null;
    }
    const trimmed = text.trim();
    if (!trimmed.startsWith('{') && !trimmed.startsWith('[')) return null;
    try {
        const j = JSON.parse(text);
        if (typeof j?.message === 'string' && j.message.trim()) return j.message.trim();
        if (j?.errors && typeof j.errors === 'object') {
            const parts = Object.values(j.errors)
                .flat()
                .filter((x) => typeof x === 'string' && x.trim());
            if (parts.length) return parts.join(' ');
        }
        if (typeof j?.error === 'string' && j.error.trim()) return j.error.trim();
    } catch {
        /* not JSON */
    }
    return null;
}

async function blobStartsWithPdfMagic(blob) {
    if (!blob || typeof blob.slice !== 'function') return false;
    try {
        const head = await blob.slice(0, 5).text();
        return head.startsWith('%PDF');
    } catch {
        return false;
    }
}

function responseContentType(res) {
    return String(res?.headers?.['content-type'] || res?.headers?.['Content-Type'] || '').toLowerCase();
}

const TX_FILTER_EMPTY = { name: '', q: '', class: '', start_date: '', end_date: '' };

export function TransactionsListPage({
    Layout,
    title,
    endpoint,
    variant = 'cash',
    canCancelPush = false,
    subtitle = '',
    suppressFeesHeader = false,
}) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');
    const [busyId, setBusyId] = useState(null);
    const [printing, setPrinting] = useState(false);
    const [page, setPage] = useState(1);
    const [draftFilters, setDraftFilters] = useState(TX_FILTER_EMPTY);
    const [appliedFilters, setAppliedFilters] = useState(TX_FILTER_EMPTY);
    const [selectedIds, setSelectedIds] = useState(() => new Set());
    const selectAllHeaderRef = useRef(null);

    const fetchList = useCallback(
        (pageNum) => {
            setLoading(true);
            setErr('');
            const params = {};
            if (variant === 'cash') {
                params.page = pageNum;
                if (appliedFilters.name.trim()) params.name = appliedFilters.name.trim();
                if (appliedFilters.class) params.class = appliedFilters.class;
                if (appliedFilters.start_date) params.start_date = appliedFilters.start_date;
                if (appliedFilters.end_date) params.end_date = appliedFilters.end_date;
            } else if (variant === 'online') {
                if (appliedFilters.name.trim()) params.name = appliedFilters.name.trim();
            } else if (variant === 'amendments') {
                if (appliedFilters.name.trim()) params.name = appliedFilters.name.trim();
                if (appliedFilters.q.trim()) params.q = appliedFilters.q.trim();
            } else if (pageNum > 1) {
                params.page = pageNum;
            }
            return axios
                .get(endpoint, { headers: xhrJson, params })
                .then((r) => {
                    const normalized = normalizeFeesTransactionRows(r.data?.data);
                    setRows(normalized);
                    setMeta(r.data?.meta || {});
                })
                .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load transactions.'))
                .finally(() => setLoading(false));
        },
        [endpoint, variant, appliedFilters],
    );

    useEffect(() => {
        setPage(1);
        setSelectedIds(new Set());
    }, [endpoint, variant]);

    useEffect(() => {
        fetchList(page);
    }, [fetchList, page]);

    const onSubmitFilters = (e) => {
        e.preventDefault();
        setAppliedFilters({ ...draftFilters });
        setPage(1);
    };

    const onClearFilters = () => {
        setDraftFilters({ ...TX_FILTER_EMPTY });
        setAppliedFilters({ ...TX_FILTER_EMPTY });
        setPage(1);
    };

    const onCancelPush = async (id) => {
        if (!window.confirm('Cancel this push transaction?')) return;
        setBusyId(id);
        try {
            await axios.post(`/fees-collect/cancel-push-transaction/${id}`, {}, { headers: xhrJson });
            await fetchList(page);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to cancel transaction.');
        } finally {
            setBusyId(null);
        }
    };

    const pg = meta?.pagination;
    const classOptions = meta?.class_options || [];
    const studentName = (r) => {
        const fn = r?.first_name || r?.student?.first_name || '';
        const ln = r?.last_name || r?.student?.last_name || '';
        const s = `${fn} ${ln}`.trim();
        return s || r?.student_name || '-';
    };

    const receiptId = (r) => r?.fees_collect_id || r?.id;
    const detailsId = (r) => r?.fees_assign_children_id || r?.id;
    const txEntityLabel = variant === 'online' ? 'online transactions' : variant === 'amendments' ? 'amendments' : 'transactions';
    const totalRows = pg?.total ?? rows.length;
    const fromRow = rows.length === 0 ? 0 : pg ? (pg.current_page - 1) * pg.per_page + 1 : 1;
    const toRow = rows.length === 0 ? 0 : pg ? Math.min(pg.current_page * pg.per_page, pg.total) : rows.length;

    const pageReceiptKeys = useMemo(
        () => rows.map((r) => receiptId(r)).filter((id) => id != null && id !== '').map((id) => String(id)),
        [rows],
    );

    const allPageSelected = pageReceiptKeys.length > 0 && pageReceiptKeys.every((id) => selectedIds.has(id));
    const somePageSelected = pageReceiptKeys.some((id) => selectedIds.has(id)) && !allPageSelected;

    useEffect(() => {
        const el = selectAllHeaderRef.current;
        if (el && 'indeterminate' in el) el.indeterminate = somePageSelected;
    }, [somePageSelected]);

    const toggleReceipt = (rid) => {
        const key = String(rid);
        setSelectedIds((prev) => {
            const next = new Set(prev);
            if (next.has(key)) next.delete(key);
            else next.add(key);
            return next;
        });
    };

    const toggleSelectAllPage = () => {
        setSelectedIds((prev) => {
            const next = new Set(prev);
            if (allPageSelected) {
                pageReceiptKeys.forEach((id) => next.delete(id));
            } else {
                pageReceiptKeys.forEach((id) => next.add(id));
            }
            return next;
        });
    };

    const printManyReceipts = async () => {
        const ids = Array.from(selectedIds);
        if (ids.length === 0) {
            setErr('Select at least one row to print receipts.');
            return;
        }
        setErr('');
        setPrinting(true);
        try {
            const fd = new FormData();
            ids.forEach((id) => fd.append('fees_assign_ids[]', id));
            const res = await axios.post('/fees-collect/print-receipts', fd, {
                responseType: 'blob',
                headers: { ...xhrJson },
                validateStatus: () => true,
            });
            const status = res.status;
            const ctype = responseContentType(res);
            const body = res.data;

            if (status < 200 || status >= 300) {
                const msg = (await laravelMessageFromBlob(body)) || `Could not generate PDF (HTTP ${status}).`;
                setErr(msg);
                return;
            }

            if (ctype.includes('application/json') || ctype.includes('+json')) {
                const msg = (await laravelMessageFromBlob(body)) || 'Unexpected JSON response while printing receipts.';
                setErr(msg);
                return;
            }

            const blob = body instanceof Blob ? body : new Blob([body], { type: ctype || 'application/pdf' });
            const isPdf = ctype.includes('pdf') || ctype.includes('octet-stream') || (await blobStartsWithPdfMagic(blob));
            if (!isPdf) {
                const sniffed = await laravelMessageFromBlob(blob, 12_000);
                setErr(
                    sniffed ||
                        'The server response was not a PDF. Check your permissions or try again after refreshing the page.',
                );
                return;
            }

            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `fees_transaction_receipts_${new Date().toISOString().slice(0, 10)}.pdf`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
            setSelectedIds(new Set());
        } catch (ex) {
            const data = ex?.response?.data;
            let msg =
                typeof ex?.response?.data?.message === 'string' ? ex.response.data.message : '';
            if (!msg && data instanceof Blob) {
                msg = (await laravelMessageFromBlob(data)) || '';
            }
            setErr(msg || ex.message || 'Could not generate combined receipt PDF. Check your network connection and try again.');
        } finally {
            setPrinting(false);
        }
    };

    const loaderLabel =
        suppressFeesHeader && (variant === 'cash' || variant === 'online' || variant === 'amendments')
            ? txEntityLabel
            : String(panelTitle(meta?.title, title) ?? 'list').toLowerCase();

    const cashColSpan = 10;

    const shellWrap = suppressFeesHeader
        ? 'mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-3 sm:px-6 sm:py-4 lg:px-8 lg:py-5'
        : 'mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-8 sm:px-6 lg:px-8 lg:py-10';

    return (
        <Layout>
            <div className={shellWrap}>
                {!suppressFeesHeader ? (
                    <div className="mb-6 border-b border-slate-200/80 pb-6">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Fees</p>
                        <h1 className="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            {panelTitle(meta?.title, title)}
                        </h1>
                        {subtitle ? <p className="mt-2 max-w-3xl text-sm text-slate-600">{subtitle}</p> : null}
                    </div>
                ) : null}
                {variant === 'cash' ? (
                    <form
                        onSubmit={onSubmitFilters}
                        className="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
                    >
                        <div className="min-w-[200px] flex-1">
                            <label className="mb-1 block text-xs font-medium text-slate-600">Student name</label>
                            <input
                                type="search"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="First name, last name, or full name"
                                value={draftFilters.name}
                                onChange={(e) => setDraftFilters((f) => ({ ...f, name: e.target.value }))}
                            />
                        </div>
                        <div className="min-w-[160px]">
                            <label className="mb-1 block text-xs font-medium text-slate-600">Class</label>
                            <select
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                value={draftFilters.class}
                                onChange={(e) => setDraftFilters((f) => ({ ...f, class: e.target.value }))}
                            >
                                <option value="">All classes</option>
                                {classOptions.map((o) => (
                                    <option key={o.value} value={o.value}>
                                        {o.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="min-w-[140px]">
                            <label className="mb-1 block text-xs font-medium text-slate-600">From date</label>
                            <input
                                type="date"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                value={draftFilters.start_date}
                                onChange={(e) => setDraftFilters((f) => ({ ...f, start_date: e.target.value }))}
                            />
                        </div>
                        <div className="min-w-[140px]">
                            <label className="mb-1 block text-xs font-medium text-slate-600">To date</label>
                            <input
                                type="date"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                value={draftFilters.end_date}
                                onChange={(e) => setDraftFilters((f) => ({ ...f, end_date: e.target.value }))}
                            />
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <UiButton type="submit" variant="primary" disabled={loading}>
                                Search
                            </UiButton>
                            <UiButton type="button" variant="secondary" disabled={loading} onClick={onClearFilters}>
                                Clear
                            </UiButton>
                        </div>
                    </form>
                ) : null}
                {variant === 'online' ? (
                    <form
                        onSubmit={onSubmitFilters}
                        className="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
                    >
                        <div className="min-w-[220px] flex-1">
                            <label className="mb-1 block text-xs font-medium text-slate-600">Student name</label>
                            <input
                                type="search"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="First name, last name, or full name"
                                value={draftFilters.name}
                                onChange={(e) => setDraftFilters((f) => ({ ...f, name: e.target.value }))}
                            />
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <UiButton type="submit" variant="primary" disabled={loading}>
                                Search
                            </UiButton>
                            <UiButton type="button" variant="secondary" disabled={loading} onClick={onClearFilters}>
                                Clear
                            </UiButton>
                        </div>
                    </form>
                ) : null}
                {variant === 'amendments' ? (
                    <form
                        onSubmit={onSubmitFilters}
                        className="mb-4 flex flex-col gap-3 rounded-xl border border-slate-200/90 bg-white p-4 shadow-sm sm:flex-row sm:flex-wrap sm:items-end"
                    >
                        <div className="min-w-[200px] flex-1">
                            <label className="mb-1 block text-xs font-medium text-slate-600">Student name</label>
                            <input
                                type="search"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="First name, last name, or full name"
                                value={draftFilters.name}
                                onChange={(e) => setDraftFilters((f) => ({ ...f, name: e.target.value }))}
                            />
                        </div>
                        <div className="min-w-[200px] flex-1">
                            <label className="mb-1 block text-xs font-medium text-slate-600">Amendment</label>
                            <input
                                type="search"
                                className="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                placeholder="Description or contact / parent name"
                                value={draftFilters.q}
                                onChange={(e) => setDraftFilters((f) => ({ ...f, q: e.target.value }))}
                            />
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <UiButton type="submit" variant="primary" disabled={loading}>
                                Search
                            </UiButton>
                            <UiButton type="button" variant="secondary" disabled={loading} onClick={onClearFilters}>
                                Clear
                            </UiButton>
                        </div>
                    </form>
                ) : null}
                {variant === 'cash' && selectedIds.size > 0 ? (
                    <div className="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3">
                        <p className="text-sm font-medium text-indigo-900">
                            {selectedIds.size} receipt{selectedIds.size === 1 ? '' : 's'} selected
                        </p>
                        <UiButton type="button" variant="primary" disabled={printing} onClick={printManyReceipts}>
                            {printing ? 'Generating PDF…' : 'Print selected receipts (PDF)'}
                        </UiButton>
                    </div>
                ) : null}
                {err ? <p className="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{err}</p> : null}
                {loading ? <UiPageLoader text={`Loading ${loaderLabel}…`} /> : null}
                {!loading ? (
                    <div className="space-y-4">
                        <div className="overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-[0_1px_3px_rgba(15,23,42,0.06),0_4px_12px_rgba(15,23,42,0.04)] ring-1 ring-slate-900/[0.04]">
                            <div className="flex flex-col gap-2 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                                <p className="text-sm text-slate-600">
                                    {rows.length === 0 ? `No ${txEntityLabel} found.` : `Showing ${fromRow}-${toRow} of ${totalRows} ${txEntityLabel}`}
                                </p>
                                {pg ? (
                                    <p className="text-xs font-medium text-slate-400">
                                        Page {pg.current_page} of {pg.last_page}
                                    </p>
                                ) : null}
                            </div>
                            <UiTableWrap className="rounded-none border-0 shadow-none ring-0">
                                <UiTable className="min-w-full divide-y divide-slate-100">
                                    <UiTHead>
                                        <UiHeadRow className="border-b border-slate-200 bg-slate-100">
                                            {variant === 'cash' ? (
                                                <UiTH className="w-10 text-xs font-semibold uppercase tracking-wider text-slate-600">
                                                    <input
                                                        ref={selectAllHeaderRef}
                                                        type="checkbox"
                                                        className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                        checked={allPageSelected}
                                                        onChange={toggleSelectAllPage}
                                                        aria-label="Select all on this page"
                                                    />
                                                </UiTH>
                                            ) : null}
                                            <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">#</UiTH>
                                            {variant === 'cash' ? (
                                                <>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Note</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Student</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Fee type</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Date</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Amount</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Bank</UiTH>
                                                </>
                                            ) : null}
                                            {variant === 'online' ? (
                                                <>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Student</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Settlement</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Date</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Amount</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Bank</UiTH>
                                                </>
                                            ) : null}
                                            {variant === 'amendments' ? (
                                                <>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Student</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Amendment</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Promise date</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Paid</UiTH>
                                                    <UiTH className="text-xs font-semibold uppercase tracking-wider text-slate-600">Remained</UiTH>
                                                </>
                                            ) : null}
                                            <UiTH className="text-right text-xs font-semibold uppercase tracking-wider text-slate-600">Actions</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.length === 0 ? (
                                            <UiTableEmptyRow
                                                colSpan={variant === 'amendments' ? 7 : variant === 'cash' ? cashColSpan : 7}
                                                message="No records to display."
                                            />
                                        ) : (
                                            rows.map((r, idx) => {
                                                const rk = receiptId(r);
                                                const rkStr = rk != null && rk !== '' ? String(rk) : '';
                                                const rowKey = rkStr || r?.fees_assign_children_id || idx;
                                                return (
                                                    <UiTR key={rowKey} className="border-slate-100 hover:!bg-slate-50">
                                                        {variant === 'cash' && rkStr !== '' ? (
                                                            <UiTD className="align-middle">
                                                                <input
                                                                    type="checkbox"
                                                                    className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                                    checked={selectedIds.has(rkStr)}
                                                                    onChange={() => toggleReceipt(rkStr)}
                                                                    aria-label={`Select receipt ${rkStr}`}
                                                                />
                                                            </UiTD>
                                                        ) : variant === 'cash' ? (
                                                            <UiTD className="align-middle">
                                                                <span className="text-slate-300">—</span>
                                                            </UiTD>
                                                        ) : null}
                                                        <UiTD className="text-slate-600 tabular-nums">
                                                            {idx + 1 + (pg ? (pg.current_page - 1) * pg.per_page : 0)}
                                                        </UiTD>
                                                        {variant === 'cash' ? (
                                                            <>
                                                                <UiTD className="text-slate-700">{r?.comments ?? '—'}</UiTD>
                                                                <UiTD className="font-medium text-slate-900">{studentName(r)}</UiTD>
                                                                <UiTD className="text-slate-700">{r?.fees_type_name ?? '—'}</UiTD>
                                                                <UiTD className="whitespace-nowrap text-slate-600">
                                                                    {r?.transaction_date ?? r?.created_at ?? '—'}
                                                                </UiTD>
                                                                <UiTD className="whitespace-nowrap text-right tabular-nums font-semibold text-slate-900">
                                                                    {r?.transaction_amount ?? r?.amount ?? '—'}
                                                                </UiTD>
                                                                <UiTD className="text-slate-700">
                                                                    {[r?.bank_name, r?.account_number].filter(Boolean).join(' ') || '—'}
                                                                </UiTD>
                                                            </>
                                                        ) : null}
                                                        {variant === 'online' ? (
                                                            <>
                                                                <UiTD className="font-medium text-slate-900">{studentName(r)}</UiTD>
                                                                <UiTD className="text-slate-700">{r?.settlement_receipt ?? '—'}</UiTD>
                                                                <UiTD className="whitespace-nowrap text-slate-600">{r?.transaction_date ?? '—'}</UiTD>
                                                                <UiTD className="whitespace-nowrap text-right tabular-nums font-semibold text-slate-900">
                                                                    {r?.transaction_amount ?? '—'}
                                                                </UiTD>
                                                                <UiTD className="text-slate-700">
                                                                    {[r?.bank_name, r?.account_number].filter(Boolean).join(' ') || '—'}
                                                                </UiTD>
                                                            </>
                                                        ) : null}
                                                        {variant === 'amendments' ? (
                                                            <>
                                                                <UiTD className="font-medium text-slate-900">{studentName(r)}</UiTD>
                                                                <UiTD className="text-slate-700">{[r?.parent_name, r?.description].filter(Boolean).join(' — ') || '—'}</UiTD>
                                                                <UiTD className="whitespace-nowrap text-slate-600">{r?.date ?? '—'}</UiTD>
                                                                <UiTD className="whitespace-nowrap text-right tabular-nums text-emerald-700">{r?.paid_amount ?? '—'}</UiTD>
                                                                <UiTD className="whitespace-nowrap text-right tabular-nums font-semibold text-amber-800">{r?.remained_amount ?? r?.transaction_amount ?? '—'}</UiTD>
                                                            </>
                                                        ) : null}
                                                        <UiTD>
                                                            <div className="flex items-center justify-end gap-2">
                                                                {variant === 'cash' && r?.student_id ? (
                                                                    <Link
                                                                        to={`/collections/collect/${r.student_id}`}
                                                                        className={`${uiIconBtnClass} text-emerald-600 hover:bg-emerald-50`}
                                                                        title="Collect fees"
                                                                        aria-label="Collect fees"
                                                                    >
                                                                        <IconBanknote />
                                                                    </Link>
                                                                ) : null}
                                                                {variant === 'cash' && r?.fees_assign_children_id ? (
                                                                    <Link
                                                                        to={`/collections/${r.fees_assign_children_id}`}
                                                                        className={`${uiIconBtnClass} text-slate-700 hover:bg-slate-50`}
                                                                        title="View details"
                                                                        aria-label="View details"
                                                                    >
                                                                        <IconView />
                                                                    </Link>
                                                                ) : null}
                                                                {variant === 'online' && r?.student_id ? (
                                                                    <Link
                                                                        to={`/collections/collect/${r.student_id}`}
                                                                        className={`${uiIconBtnClass} text-emerald-600 hover:bg-emerald-50`}
                                                                        title="Collect fees"
                                                                        aria-label="Collect fees"
                                                                    >
                                                                        <IconBanknote />
                                                                    </Link>
                                                                ) : null}
                                                                {variant === 'online' && r?.fees_assign_children_id ? (
                                                                    <Link
                                                                        to={`/collections/${r.fees_assign_children_id}`}
                                                                        className={`${uiIconBtnClass} text-slate-700 hover:bg-slate-50`}
                                                                        title="View details"
                                                                        aria-label="View details"
                                                                    >
                                                                        <IconView />
                                                                    </Link>
                                                                ) : null}
                                                                {variant === 'amendments' && detailsId(r) ? (
                                                                    <Link
                                                                        to={`/collections/${detailsId(r)}`}
                                                                        className="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                                                                        title="View details"
                                                                        aria-label="View details"
                                                                    >
                                                                        <IconView className="h-4 w-4" />
                                                                        View
                                                                    </Link>
                                                                ) : null}
                                                                {variant === 'cash' && receiptId(r) ? (
                                                                    <a
                                                                        href={`/fees-collect/printTransactionReceipt/${receiptId(r)}`}
                                                                        className={`${uiIconBtnClass} text-blue-600 hover:bg-blue-50`}
                                                                        title="Print receipt"
                                                                        aria-label="Print receipt"
                                                                    >
                                                                        <IconReceipt />
                                                                    </a>
                                                                ) : null}
                                                                {canCancelPush && receiptId(r) ? (
                                                                    <button
                                                                        type="button"
                                                                        disabled={busyId === receiptId(r)}
                                                                        onClick={() => onCancelPush(receiptId(r))}
                                                                        className={`${uiIconBtnClass} text-rose-600 hover:bg-rose-50 disabled:opacity-60`}
                                                                        title="Cancel push"
                                                                        aria-label="Cancel push"
                                                                    >
                                                                        <IconX />
                                                                    </button>
                                                                ) : null}
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
                        {pg && pg.last_page > 1 ? (
                            <div className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
                                <span>
                                    Page {pg.current_page} of {pg.last_page} ({pg.total} total)
                                </span>
                                <div className="flex gap-2">
                                    <UiButton
                                        type="button"
                                        variant="secondary"
                                        disabled={pg.current_page <= 1 || loading}
                                        onClick={() => setPage((p) => Math.max(1, p - 1))}
                                    >
                                        Previous
                                    </UiButton>
                                    <UiButton
                                        type="button"
                                        variant="secondary"
                                        disabled={pg.current_page >= pg.last_page || loading}
                                        onClick={() => setPage((p) => p + 1)}
                                    >
                                        Next
                                    </UiButton>
                                </div>
                            </div>
                        ) : null}
                    </div>
                ) : null}
            </div>
        </Layout>
    );
}

