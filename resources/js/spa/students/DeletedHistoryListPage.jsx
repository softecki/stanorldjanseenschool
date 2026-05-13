import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import {
    UiHeadRow,
    UiIconLinkView,
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
} from '../ui/UiKit';

function rowDisplayName(r) {
    if (!r) return '—';
    if (r.full_name && String(r.full_name).trim()) return String(r.full_name).trim();
    const t = `${r.first_name || ''} ${r.last_name || ''}`.trim();
    if (t) return t;
    return r.name || r.student_name || '—';
}

function formatDateTime(s) {
    if (!s) return '—';
    const d = new Date(s);
    return Number.isNaN(d.getTime()) ? String(s) : d.toLocaleString();
}

export function DeletedHistoryPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);

    const hydrateFromResponse = useCallback((payload) => {
        const paged = payload?.data;
        const list = Array.isArray(paged?.data) ? paged.data : [];
        setRows(list);
        setMeta(payload?.meta || {});
        setPagination({
            current_page: paged?.current_page ?? 1,
            last_page: paged?.last_page ?? 1,
            per_page: paged?.per_page ?? 15,
            total: paged?.total ?? list.length,
        });
    }, []);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/student-deleted-history', { headers: xhrJson, params: { page } })
            .then((r) => hydrateFromResponse(r.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load deleted history.'))
            .finally(() => setLoading(false));
    }, [page, hydrateFromResponse]);

    const perPage = pagination.per_page || 15;
    const currentPage = pagination.current_page || 1;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading deleted history…" /> : null}
                {!loading ? (
                    <>
                        <UiTableWrap>
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH className="w-12">#</UiTH>
                                        <UiTH>Student</UiTH>
                                        <UiTH>Admission no</UiTH>
                                        <UiTH>Deleted at</UiTH>
                                        <UiTH>Deleted by</UiTH>
                                        <UiTH className="text-right">Actions</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.length === 0 ? (
                                        <UiTableEmptyRow colSpan={6} message="No deleted student records found." />
                                    ) : (
                                        rows.map((r, idx) => {
                                            const deletedBy = r.deleted_by_user;
                                            const deleter =
                                                typeof deletedBy?.name === 'string' && deletedBy.name.trim() !== ''
                                                    ? deletedBy.name
                                                    : '—';
                                            return (
                                                <UiTR key={r.id}>
                                                    <UiTD className="whitespace-nowrap text-gray-500">
                                                        {(currentPage - 1) * perPage + idx + 1}
                                                    </UiTD>
                                                    <UiTD className="font-medium text-gray-900">{rowDisplayName(r)}</UiTD>
                                                    <UiTD>{r.admission_no || '—'}</UiTD>
                                                    <UiTD className="whitespace-nowrap text-gray-600">
                                                        {formatDateTime(r.deleted_at)}
                                                    </UiTD>
                                                    <UiTD>{deleter}</UiTD>
                                                    <UiTD className="text-right">
                                                        <div className="flex justify-end">
                                                            <UiIconLinkView to={`/deleted-history/${r.id}`} />
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
            </div>
        </AdminLayout>
    );
}
