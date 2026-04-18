import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import {
    UiButton,
    UiHeadRow,
    UiIconButtonDelete,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';

export function RolesPage() {
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');
    const [showCreate, setShowCreate] = useState(false);
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ name: '', permissions: [] });

    const load = () =>
        axios
            .get('/roles', { headers: xhrJson })
            .then((r) => setRows(r.data?.data || []))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load roles.'));

    useEffect(() => {
        load();
    }, []);

    const openCreate = async () => {
        setShowCreate(true);
        const { data } = await axios.get('/roles/create', { headers: xhrJson });
        setMeta(data?.meta || {});
    };

    const togglePerm = (id) => {
        setForm((f) => {
            const set = new Set(f.permissions || []);
            if (set.has(id)) set.delete(id);
            else set.add(id);
            return { ...f, permissions: Array.from(set) };
        });
    };

    const createRole = async (e) => {
        e.preventDefault();
        await axios.post('/roles/store', form, { headers: xhrJson });
        setShowCreate(false);
        setForm({ name: '', permissions: [] });
        load();
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-gray-900">Roles</h1>
                    <UiButton type="button" onClick={openCreate}>
                        Create role
                    </UiButton>
                </div>
                {err ? <p className="text-sm text-red-600">{err}</p> : null}
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>Name</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((r) => (
                                <UiTR key={r.id}>
                                    <UiTD>{r.name}</UiTD>
                                    <UiTD className="text-right">
                                        <div className="flex justify-end">
                                            <UiIconButtonDelete
                                                onClick={async () => {
                                                    if (window.confirm('Delete role?')) {
                                                        await axios.delete(`/roles/delete/${r.id}`, { headers: xhrJson });
                                                        load();
                                                    }
                                                }}
                                            />
                                        </div>
                                    </UiTD>
                                </UiTR>
                            ))}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>

                {showCreate ? (
                    <form onSubmit={createRole} className="mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 className="mb-2 font-semibold text-gray-900">Create role</h2>
                        <input
                            className="mb-3 w-full rounded-lg border border-gray-200 px-3 py-2"
                            placeholder="Role name"
                            value={form.name}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                        />
                        <div className="mb-3 flex flex-wrap gap-3">
                            {(meta.permissions || []).map((p) => (
                                <label key={p.id} className="text-sm text-gray-700">
                                    <input type="checkbox" className="mr-1" checked={(form.permissions || []).includes(p.id)} onChange={() => togglePerm(p.id)} />
                                    {p.name}
                                </label>
                            ))}
                        </div>
                        <UiButton type="submit">Save</UiButton>
                    </form>
                ) : null}
            </div>
        </AdminLayout>
    );
}
