import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import {
    UiButtonLink,
    UiHeadRow,
    UiIconLinkEdit,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';

export function UsersPage() {
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');

    const load = () =>
        axios
            .get('/users', { headers: xhrJson })
            .then((r) => setRows(r.data?.data || []))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load users.'));

    useEffect(() => {
        load();
    }, []);

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-4 flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-gray-900">Users</h1>
                    <UiButtonLink to="/users/create">Create</UiButtonLink>
                </div>
                {err ? <p className="text-sm text-red-600">{err}</p> : null}
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>Name</UiTH>
                                <UiTH>Email</UiTH>
                                <UiTH>Role</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((u) => (
                                <UiTR key={u.id}>
                                    <UiTD>{u.name || `${u.first_name || ''} ${u.last_name || ''}`}</UiTD>
                                    <UiTD>{u.email}</UiTD>
                                    <UiTD>{u.role?.name || '-'}</UiTD>
                                    <UiTD className="text-right">
                                        <div className="flex justify-end">
                                            <UiIconLinkEdit to={`/users/${u.id}/edit`} />
                                        </div>
                                    </UiTD>
                                </UiTR>
                            ))}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>
            </div>
        </AdminLayout>
    );
}
