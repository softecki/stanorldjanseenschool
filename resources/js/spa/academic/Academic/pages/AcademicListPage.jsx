import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { useSearchParams } from 'react-router-dom';
import { FullPageLoader, Panel, firstValue, normalizePagedList } from '../../AcademicModuleShared';
import {
    IconPlus,
    UiActionGroup,
    UiButtonLink,
    UiHeadRow,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';
import { xhrJson } from '../../../api/xhrJson';

/** Use on `/classes` to mirror the `classes` table (id, name, status, orders, timestamps). */
export const CLASSES_LIST_COLUMNS = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Name', type: 'name' },
    { key: 'status', label: 'Status', type: 'status' },
    { key: 'orders', label: 'Orders' },
    { key: 'created_at', label: 'Created' },
    { key: 'updated_at', label: 'Updated' },
];

function formatDateTime(v) {
    if (v == null || v === '') return '—';
    const d = new Date(v);
    return Number.isNaN(d.getTime()) ? String(v) : d.toLocaleString();
}

function isActiveStatus(s) {
    return String(s) === '1' || s === 1;
}

function statusLabelAndClass(s) {
    if (s === undefined || s === null || s === '' || s === '-') return { label: '—', cls: 'text-gray-500' };
    if (isActiveStatus(s)) return { label: 'Active', cls: 'bg-emerald-50 text-emerald-700' };
    if (String(s) === '0' || s === 0) return { label: 'Inactive', cls: 'bg-gray-100 text-gray-800' };
    return { label: 'Inactive', cls: 'bg-amber-50 text-amber-800' };
}

/**
 * @param {Array<{ key: string, label: string, type?: 'name' | 'status' }>} [listColumns] — if set, shows these data columns (plus # and actions).
 */
export function AcademicListPage({
    Layout,
    title,
    endpoint,
    createTo,
    editBase,
    viewBase,
    listColumns: listColumnsProp,
    tabViews,
}) {
    const [searchParams, setSearchParams] = useSearchParams();
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');
    const [busyId, setBusyId] = useState(null);
    const [loading, setLoading] = useState(true);
    const [pageTitle, setPageTitle] = useState(title);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
    const [page, setPage] = useState(1);

    const tabs = useMemo(() => (Array.isArray(tabViews) && tabViews.length > 0 ? tabViews : null), [tabViews]);
    const activeTab = useMemo(() => {
        if (!tabs) return null;
        const selected = searchParams.get('tab');
        return tabs.find((t) => t.id === selected) || tabs[0];
    }, [tabs, searchParams]);

    const resolvedTitle = activeTab?.title ?? title;
    const resolvedEndpoint = activeTab?.endpoint ?? endpoint;
    const resolvedCreateTo = activeTab?.createTo ?? createTo;
    const resolvedEditBase = activeTab?.editBase ?? editBase;
    const resolvedViewBase = activeTab?.viewBase ?? viewBase;
    const resolvedListColumns = activeTab?.listColumns ?? listColumnsProp;

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(resolvedEndpoint, { headers: xhrJson, params: { page } })
            .then((r) => {
                const { rows: list, meta, pagination: pg } = normalizePagedList(r.data);
                setRows(list);
                setPageTitle(tabs ? resolvedTitle : (meta.title || resolvedTitle));
                setPagination(pg);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'))
            .finally(() => setLoading(false));
    }, [resolvedEndpoint, page, resolvedTitle]);

    const deleteRow = async (id) => {
        if (!window.confirm('Delete this item?')) return;
        setBusyId(id);
        setErr('');
        try {
            await axios.delete(`${resolvedEndpoint}/delete/${id}`, { headers: xhrJson });
            setRows((prev) => prev.filter((r) => r.id !== id));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete item.');
        } finally {
            setBusyId(null);
        }
    };

    const perPage = pagination.per_page || 15;
    const currentPage = pagination.current_page || 1;
    const listColumns = resolvedListColumns;
    const emptyColSpan = listColumns ? 2 + listColumns.length : 4;

    const renderDataCell = (row, col) => {
        if (col.type === 'name') {
            return <>{firstValue(row, ['name', 'class_tran', 'section_tran', 'title', 'type'])}</>;
        }
        if (col.type === 'status') {
            const { label, cls } = statusLabelAndClass(row[col.key]);
            return (
                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${cls}`}>{label}</span>
            );
        }
        if (col.key === 'created_at' || col.key === 'updated_at') {
            return formatDateTime(row[col.key]);
        }
        const v = row[col.key];
        if (v == null || v === '') return '—';
        return String(v);
    };

    return (
        <Panel Layout={Layout} title={pageTitle}>
            {tabs ? (
                <div className="mb-4 grid w-full grid-cols-1 gap-2 rounded-xl border border-gray-200 bg-white p-2 sm:grid-cols-3">
                    {tabs.map((tab) => {
                        const isActive = tab.id === activeTab?.id;
                        return (
                            <button
                                key={tab.id}
                                type="button"
                                className={`w-full rounded-lg px-4 py-2 text-sm font-semibold transition ${
                                    isActive
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'border border-gray-200 bg-gray-50 text-gray-700 hover:bg-white'
                                }`}
                                onClick={() => {
                                    setPage(1);
                                    setSearchParams({ tab: tab.id });
                                }}
                            >
                                {tab.label}
                            </button>
                        );
                    })}
                </div>
            ) : null}
            <div className="mb-4 flex justify-end">
                <UiButtonLink to={resolvedCreateTo} variant="primary" leftIcon={<IconPlus />}>
                    Create
                </UiButtonLink>
            </div>
            {err ? <p className="mb-2 text-sm text-red-600">{err}</p> : null}
            {loading ? <FullPageLoader text="Loading…" /> : null}
            {!loading ? (
                <>
                    <UiTableWrap>
                        <UiTable>
                            <UiTHead>
                                <UiHeadRow>
                                    <UiTH className="w-12">#</UiTH>
                                    {listColumns
                                        ? listColumns.map((col) => <UiTH key={col.key}>{col.label}</UiTH>)
                                        : null}
                                    {!listColumns ? (
                                        <>
                                            <UiTH>Name</UiTH>
                                            <UiTH>Status</UiTH>
                                        </>
                                    ) : null}
                                    <UiTH className="text-right">Actions</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.length === 0 ? (
                                    <UiTableEmptyRow colSpan={emptyColSpan} message="No records to display." />
                                ) : listColumns ? (
                                    rows.map((row, idx) => (
                                        <UiTR key={row.id != null ? row.id : idx}>
                                            <UiTD className="whitespace-nowrap text-gray-500">
                                                {(currentPage - 1) * perPage + idx + 1}
                                            </UiTD>
                                            {listColumns.map((col) => (
                                                <UiTD
                                                    key={col.key}
                                                    className={
                                                        col.type === 'name' ? 'font-medium text-gray-900' : 'whitespace-nowrap'
                                                    }
                                                >
                                                    {renderDataCell(row, col)}
                                                </UiTD>
                                            ))}
                                            <UiTD className="text-right">
                                                <div className="flex justify-end">
                                                    <UiActionGroup
                                                        viewTo={`${resolvedViewBase}/${row.id}`}
                                                        editTo={`${resolvedEditBase}/${row.id}/edit`}
                                                        onDelete={() => deleteRow(row.id)}
                                                        busy={busyId === row.id}
                                                    />
                                                </div>
                                            </UiTD>
                                        </UiTR>
                                    ))
                                ) : (
                                    rows.map((row, idx) => {
                                        const { label, cls } = statusLabelAndClass(
                                            firstValue(row, ['status']),
                                        );
                                        const name = firstValue(row, [
                                            'name',
                                            'class_tran',
                                            'section_tran',
                                            'title',
                                            'type',
                                        ]);
                                        return (
                                            <UiTR key={row.id != null ? row.id : idx}>
                                                <UiTD className="whitespace-nowrap text-gray-500">
                                                    {(currentPage - 1) * perPage + idx + 1}
                                                </UiTD>
                                                <UiTD className="font-medium text-gray-900">{name}</UiTD>
                                                <UiTD>
                                                    <span
                                                        className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${cls}`}
                                                    >
                                                        {label}
                                                    </span>
                                                </UiTD>
                                                <UiTD className="text-right">
                                                    <div className="flex justify-end">
                                                        <UiActionGroup
                                                            viewTo={`${resolvedViewBase}/${row.id}`}
                                                            editTo={`${resolvedEditBase}/${row.id}/edit`}
                                                            onDelete={() => deleteRow(row.id)}
                                                            busy={busyId === row.id}
                                                        />
                                                    </div>
                                                </UiTD>
                                            </UiTR>
                                        );
                                    })
                                )}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                    <UiPager
                        className="mt-4"
                        page={currentPage}
                        lastPage={pagination.last_page || 1}
                        onPrev={() => setPage((p) => Math.max(1, p - 1))}
                        onNext={() => setPage((p) => Math.min(pagination.last_page || 1, p + 1))}
                    />
                </>
            ) : null}
        </Panel>
    );
}
