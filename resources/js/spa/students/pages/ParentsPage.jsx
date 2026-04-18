import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { AdminLayout } from '../../layout/AdminLayout';
import {
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
} from '../../ui/UiKit';

export function ParentsPage() {
    const [rows, setRows] = useState([]);
    const [query, setQuery] = useState('');
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);

    const load = (keyword = '') => {
        setLoading(true);
        const req = keyword
            ? axios.post('/parent/search', { keyword }, { headers: xhrJson })
            : axios.get('/parent', { headers: xhrJson });
        req.then((r) => {
            setRows(r.data?.data?.data || r.data?.data || []);
            setErr('');
        }).catch((ex) => {
            setErr(ex.response?.data?.message || 'Failed to load parents.');
        }).finally(() => setLoading(false));
    };

    useEffect(() => { load(); }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this parent?')) return;
        try {
            await axios.delete(`/parent/delete/${id}`, { headers: xhrJson });
            load(query);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete parent.');
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">Parents</h1>
                        <div className="flex flex-wrap gap-2">
                            <input
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                placeholder="Search parent"
                                className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                            />
                            <UiButton type="button" variant="secondary" onClick={() => load(query)}>
                                Search
                            </UiButton>
                            <UiButtonLink to="/parents/create">Create</UiButtonLink>
                        </div>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading parents…" /> : null}
                {!loading ? (
                    <UiTableWrap>
                        <UiTable>
                            <UiTHead>
                                <UiHeadRow>
                                    <UiTH>Name</UiTH>
                                    <UiTH>Phone</UiTH>
                                    <UiTH>Email</UiTH>
                                    <UiTH className="text-right">Actions</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.map((p) => (
                                    <UiTR key={p.id}>
                                        <UiTD>{p.name || `${p.first_name || ''} ${p.last_name || ''}`.trim() || '-'}</UiTD>
                                        <UiTD>{p.phone || p.mobile || p.mobile_no || '-'}</UiTD>
                                        <UiTD>{p.email || '-'}</UiTD>
                                        <UiTD className="text-right">
                                            <UiActionGroup editTo={`/parents/${p.id}/edit`} onDelete={() => remove(p.id)} />
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

