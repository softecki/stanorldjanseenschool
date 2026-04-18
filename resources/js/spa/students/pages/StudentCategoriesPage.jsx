import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { AdminLayout } from '../../layout/AdminLayout';
import {
    UiActionGroup,
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
} from '../../ui/UiKit';

export function StudentCategoriesPage() {
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    const load = () => {
        setLoading(true);
        return axios
            .get('/student/category', { headers: xhrJson })
            .then((r) => setRows(r.data?.data?.data || r.data?.data || []))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this category?')) return;
        setErr('');
        try {
            await axios.delete(`/student/category/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete category.');
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">Student Categories</h1>
                        <UiButtonLink to="/categories/create">Create</UiButtonLink>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading categories…" /> : null}
                {!loading ? (
                    <UiTableWrap>
                        <UiTable>
                            <UiTHead>
                                <UiHeadRow>
                                    <UiTH>#</UiTH>
                                    <UiTH>Category Name</UiTH>
                                    <UiTH className="text-right">Actions</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.map((c, idx) => (
                                    <UiTR key={c.id}>
                                        <UiTD className="font-medium">{idx + 1}</UiTD>
                                        <UiTD>{c.title || c.name || '-'}</UiTD>
                                        <UiTD className="text-right">
                                            <UiActionGroup editTo={`/categories/${c.id}/edit`} onDelete={() => remove(c.id)} />
                                        </UiTD>
                                    </UiTR>
                                ))}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                ) : null}
            </div>
        </AdminLayout>
    );
}
