import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../api/xhrJson';
import {
    IconBanknote,
    IconPlus,
    IconReceipt,
    IconX,
    UiActionGroup,
    UiButton,
    UiButtonLink,
    UiHeadRow,
    UiPageLoader,
    UiTable,
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

export function EntityListPage({ Layout, title, endpoint, baseRoute, createRoute, deleteEndpoint, columns }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [busyId, setBusyId] = useState(null);

    const load = () => {
        setLoading(true);
        setErr('');
        axios.get(endpoint, { headers: xhrJson }).then((r) => {
            setRows(r.data?.data?.data || r.data?.data || []);
            setMeta(r.data?.meta || {});
        }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.')).finally(() => setLoading(false));
    };

    useEffect(() => { load(); }, [endpoint]);

    const remove = async (id) => {
        if (!window.confirm('Delete this item?')) return;
        setBusyId(id);
        setErr('');
        try {
            await axios.delete(`${deleteEndpoint}/${id}`, { headers: xhrJson });
            setRows((prev) => prev.filter((r) => Number(r.id) !== Number(id)));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        } finally {
            setBusyId(null);
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{panelTitle(meta?.title, title)}</h1>
                        <UiButtonLink to={createRoute} variant="primary" leftIcon={<IconPlus />}>
                            Create
                        </UiButtonLink>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text={`Loading ${title.toLowerCase()}...`} /> : null}
                {!loading ? (
                    <UiTableWrap>
                        <UiTable>
                            <UiTHead>
                                <UiHeadRow>
                                    <UiTH>#</UiTH>
                                    {columns.map((c) => (
                                        <UiTH key={c.key}>{c.label}</UiTH>
                                    ))}
                                    <UiTH className="text-right">Actions</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.map((row, idx) => (
                                    <UiTR key={row.id || idx}>
                                        <UiTD className="font-medium text-gray-700">{idx + 1}</UiTD>
                                        {columns.map((c) => (
                                            <UiTD key={c.key}>{c.render ? c.render(row) : (row?.[c.key] ?? '-')}</UiTD>
                                        ))}
                                        <UiTD>
                                            <UiActionGroup
                                                viewTo={`${baseRoute}/${row.id}`}
                                                editTo={`${baseRoute}/${row.id}/edit`}
                                                onDelete={() => remove(row.id)}
                                                busy={busyId === row.id}
                                            />
                                        </UiTD>
                                    </UiTR>
                                ))}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                ) : null}
            </div>
        </Layout>
    );
}

export function EntityViewPage({ Layout, title, loadEndpoint, backTo, editBase }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    useEffect(() => {
        setLoading(true);
        axios.get(`${loadEndpoint}/edit/${id}`, { headers: xhrJson }).then((r) => {
            setData(r.data?.data || {});
        }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.')).finally(() => setLoading(false));
    }, [id, loadEndpoint]);

    return (
        <Layout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{title}</h1>
                </div>
                {loading ? <FullPageLoader text="Loading details..." /> : null}
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {!loading ? <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <dl className="grid gap-3 text-sm md:grid-cols-2">
                        {Object.entries(data || {}).map(([k, v]) => (
                            <div key={k}>
                                <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{k.replaceAll('_', ' ')}</dt>
                                <dd className="mt-1 text-gray-800">{typeof v === 'object' ? JSON.stringify(v) : String(v ?? '-')}</dd>
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
            <option value="2">Inactive</option>
        </>
    );
}

export function FeesEntityFormPage({ Layout, edit = false, titleCreate, titleEdit, loadUrl, createUrl, updateUrl, backTo, fields }) {
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
            setMeta(r.data?.meta || {});
            if (edit) setForm(r.data?.data || {});
            else setForm((f) => ({ status: '1', ...f }));
        }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.')).finally(() => setLoading(false));
    }, [edit, id, loadUrl]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            if (edit) await axios.put(`${updateUrl}/${id}`, form, { headers: xhrJson });
            else await axios.post(createUrl, form, { headers: xhrJson });
            nav(backTo);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-6xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{edit ? titleEdit : titleCreate}</h1>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading form..." /> : null}
                {!loading ? <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2">
                    {fields({ meta, form, setForm, statusChoices })}
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
    return [];
}

export function TransactionsListPage({ Layout, title, endpoint, variant = 'cash', canCancelPush = false }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');
    const [busyId, setBusyId] = useState(null);
    const [page, setPage] = useState(1);

    const load = (pageNum = 1) => {
        setLoading(true);
        setErr('');
        const params = {};
        if (variant === 'cash' && pageNum > 1) params.page = pageNum;
        axios
            .get(endpoint, { headers: xhrJson, params })
            .then((r) => {
                const normalized = normalizeFeesTransactionRows(r.data?.data);
                setRows(normalized);
                setMeta(r.data?.meta || {});
                setPage(pageNum);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load transactions.'))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        setPage(1);
        load(1);
    }, [endpoint, variant]);

    const onCancelPush = async (id) => {
        if (!window.confirm('Cancel this push transaction?')) return;
        setBusyId(id);
        try {
            await axios.post(`/fees-collect/cancel-push-transaction/${id}`, {}, { headers: xhrJson });
            load(page);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to cancel transaction.');
        } finally {
            setBusyId(null);
        }
    };

    const pg = meta?.pagination;
    const studentName = (r) => {
        const fn = r?.first_name || r?.student?.first_name || '';
        const ln = r?.last_name || r?.student?.last_name || '';
        const s = `${fn} ${ln}`.trim();
        return s || r?.student_name || '-';
    };

    const receiptId = (r) => r?.fees_collect_id || r?.id;

    return (
        <Layout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{panelTitle(meta?.title, title)}</h1>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text={`Loading ${title.toLowerCase()}...`} /> : null}
                {!loading ? (
                    <div className="space-y-4">
                        <UiTableWrap>
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH>#</UiTH>
                                        {variant === 'cash' ? (
                                            <>
                                                <UiTH>Note</UiTH>
                                                <UiTH>Student</UiTH>
                                                <UiTH>Fee type</UiTH>
                                                <UiTH>Date</UiTH>
                                                <UiTH>Amount</UiTH>
                                                <UiTH>Bank</UiTH>
                                            </>
                                        ) : null}
                                        {variant === 'online' ? (
                                            <>
                                                <UiTH>Receipt</UiTH>
                                                <UiTH>Student</UiTH>
                                                <UiTH>Settlement</UiTH>
                                                <UiTH>Date</UiTH>
                                                <UiTH>Amount</UiTH>
                                                <UiTH>Bank</UiTH>
                                            </>
                                        ) : null}
                                        {variant === 'amendments' ? (
                                            <>
                                                <UiTH>Student</UiTH>
                                                <UiTH>Amendment</UiTH>
                                                <UiTH>Promise date</UiTH>
                                                <UiTH>Paid</UiTH>
                                                <UiTH>Remained</UiTH>
                                            </>
                                        ) : null}
                                        <UiTH className="text-right">Actions</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.map((r, idx) => (
                                        <UiTR key={receiptId(r) || r?.fees_assign_children_id || idx}>
                                            <UiTD className="text-gray-700">{idx + 1 + (pg ? (pg.current_page - 1) * pg.per_page : 0)}</UiTD>
                                            {variant === 'cash' ? (
                                                <>
                                                    <UiTD>{r?.comments ?? '—'}</UiTD>
                                                    <UiTD>{studentName(r)}</UiTD>
                                                    <UiTD>{r?.fees_type_name ?? '—'}</UiTD>
                                                    <UiTD>{r?.transaction_date ?? r?.created_at ?? '—'}</UiTD>
                                                    <UiTD>{r?.transaction_amount ?? r?.amount ?? '—'}</UiTD>
                                                    <UiTD>{[r?.bank_name, r?.account_number].filter(Boolean).join(' ') || '—'}</UiTD>
                                                </>
                                            ) : null}
                                            {variant === 'online' ? (
                                                <>
                                                    <UiTD>{r?.payment_receipt ?? '—'}</UiTD>
                                                    <UiTD>{studentName(r)}</UiTD>
                                                    <UiTD>{r?.settlement_receipt ?? '—'}</UiTD>
                                                    <UiTD>{r?.transaction_date ?? '—'}</UiTD>
                                                    <UiTD>{r?.transaction_amount ?? '—'}</UiTD>
                                                    <UiTD>{[r?.bank_name, r?.account_number].filter(Boolean).join(' ') || '—'}</UiTD>
                                                </>
                                            ) : null}
                                            {variant === 'amendments' ? (
                                                <>
                                                    <UiTD>{studentName(r)}</UiTD>
                                                    <UiTD>{[r?.parent_name, r?.description].filter(Boolean).join(' — ') || '—'}</UiTD>
                                                    <UiTD>{r?.date ?? '—'}</UiTD>
                                                    <UiTD>{r?.paid_amount ?? '—'}</UiTD>
                                                    <UiTD>{r?.remained_amount ?? r?.transaction_amount ?? '—'}</UiTD>
                                                </>
                                            ) : null}
                                            <UiTD>
                                                <div className="flex items-center justify-end gap-2">
                                                    {variant === 'amendments' && r?.student_id ? (
                                                        <a
                                                            href={`/fees-collect/collect/${r.student_id}`}
                                                            className={`${uiIconBtnClass} text-emerald-600 hover:bg-emerald-50`}
                                                            title="Collect fees"
                                                            aria-label="Collect fees"
                                                        >
                                                            <IconBanknote />
                                                        </a>
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
                                    ))}
                                </UiTBody>
                            </UiTable>
                        </UiTableWrap>
                        {pg && pg.last_page > 1 ? (
                            <div className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-600 shadow-sm">
                                <span>
                                    Page {pg.current_page} of {pg.last_page} ({pg.total} total)
                                </span>
                                <div className="flex gap-2">
                                    <UiButton
                                        type="button"
                                        variant="secondary"
                                        disabled={pg.current_page <= 1 || loading}
                                        onClick={() => load(pg.current_page - 1)}
                                    >
                                        Previous
                                    </UiButton>
                                    <UiButton
                                        type="button"
                                        variant="secondary"
                                        disabled={pg.current_page >= pg.last_page || loading}
                                        onClick={() => load(pg.current_page + 1)}
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

