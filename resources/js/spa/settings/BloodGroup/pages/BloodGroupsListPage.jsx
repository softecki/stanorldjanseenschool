import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BloodGroupModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import {
    UiActionGroup,
    UiButtonLink,
    UiHeadRow,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

export function BloodGroupsListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const load = () =>
        axios
            .get('/blood-groups', { headers: xhrJson })
            .then((r) => {
                const bg = r.data?.data?.bloodGroup;
                const list = Array.isArray(bg?.data) ? bg.data : Array.isArray(bg) ? bg : [];
                setRows(list);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this blood group?')) return;
        try {
            await axios.delete(`/blood-groups/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Blood groups'}</h1>
                    <p className="text-sm text-gray-500">Settings reference data.</p>
                </div>
                <UiButtonLink to="/blood-groups/create">Add blood group</UiButtonLink>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>Name</UiTH>
                            <UiTH>Status</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD className="font-medium">{row.name}</UiTD>
                                    <UiTD>{row.status ? 'Active' : 'Inactive'}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup editTo={`/blood-groups/${row.id}/edit`} onDelete={() => remove(row.id)} />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={3} message="No blood groups yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}

