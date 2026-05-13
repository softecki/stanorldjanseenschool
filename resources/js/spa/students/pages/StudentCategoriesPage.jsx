import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { AdminLayout } from '../../layout/AdminLayout';
import {
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

function categoryLabel(c) {
    return c.name || c.title || '—';
}

function hydrateRowsFromPayload(r) {
    const paged = r?.data;
    const list = Array.isArray(paged?.data) ? paged.data : Array.isArray(r?.data) ? r.data : [];
    return { list, paged };
}

export function StudentCategoriesPage() {
    const [rows, setRows] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [page, setPage] = useState(1);
    const [refreshToken, setRefreshToken] = useState(0);

    const hydrate = useCallback((payload) => {
        const { list, paged } = hydrateRowsFromPayload(payload);
        setRows(list);
        setMeta(payload?.meta || {});
        setPagination({
            current_page: paged?.current_page ?? 1,
            last_page: paged?.last_page ?? 1,
            per_page: paged?.per_page ?? 10,
            total: paged?.total ?? list.length,
        });
    }, []);

    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get('/student/category', { headers: xhrJson, params: { page } })
            .then((r) => hydrate(r.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load categories.'))
            .finally(() => setLoading(false));
    }, [page, refreshToken, hydrate]);

    const remove = async (id) => {
        if (!window.confirm('Delete this category?')) return;
        setErr('');
        try {
            await axios.delete(`/student/category/delete/${id}`, { headers: xhrJson });
            setRefreshToken((n) => n + 1);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete category.');
        }
    };

    const perPage = pagination.per_page || 10;
    const currentPage = pagination.current_page || 1;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Student categories'}</h1>
                        <UiButtonLink to="/categories/create">Create</UiButtonLink>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading categories…" /> : null}
                {!loading ? (
                    <>
                        <UiTableWrap>
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH className="w-12">#</UiTH>
                                        <UiTH>Category name</UiTH>
                                        <UiTH>Shortcode</UiTH>
                                        <UiTH>Status</UiTH>
                                        <UiTH className="text-right">Actions</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.length === 0 ? (
                                        <UiTableEmptyRow colSpan={5} message="No categories found." />
                                    ) : (
                                        rows.map((c, idx) => {
                                            const active = String(c.status) === '1' || c.status === 1;
                                            return (
                                                <UiTR key={c.id}>
                                                    <UiTD className="whitespace-nowrap text-gray-500">{(currentPage - 1) * perPage + idx + 1}</UiTD>
                                                    <UiTD className="font-medium text-gray-900">{categoryLabel(c)}</UiTD>
                                                    <UiTD className="text-gray-600">{c.shortcode || '—'}</UiTD>
                                                    <UiTD>
                                                        <span
                                                            className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${
                                                                active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'
                                                            }`}
                                                        >
                                                            {active ? 'Active' : 'Inactive'}
                                                        </span>
                                                    </UiTD>
                                                    <UiTD className="text-right">
                                                        <UiActionGroup
                                                            viewTo={`/categories/${c.id}`}
                                                            editTo={`/categories/${c.id}/edit`}
                                                            onDelete={() => remove(c.id)}
                                                        />
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
