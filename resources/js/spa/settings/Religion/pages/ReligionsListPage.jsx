import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { Shell } from '../../ReligionModuleShared';
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

export function ReligionsListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');

    const load = () =>
        axios
            .get('/religions', { headers: xhrJson })
            .then((r) => {
                setRows(r.data?.data?.data || r.data?.data || []);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load religions.'));

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this religion?')) return;
        await axios.delete(`/religions/delete/${id}`, { headers: xhrJson });
        load();
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Religions'}</h1>
                <UiButtonLink to="/settings/religions/create">Create</UiButtonLink>
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
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD>{row.name}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup
                                            editTo={`/settings/religions/${row.id}/edit`}
                                            translateTo={`/settings/religions/${row.id}/translate`}
                                            onDelete={() => remove(row.id)}
                                        />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={2} message="No religions found." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}
