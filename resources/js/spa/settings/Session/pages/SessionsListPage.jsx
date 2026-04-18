import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { Shell } from '../../SessionModuleShared';
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

export function SessionsListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});

    const load = () =>
        axios.get('/sessions', { headers: xhrJson }).then((r) => {
            setRows(r.data?.data?.data || r.data?.data || []);
            setMeta(r.data?.meta || {});
        });

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this session?')) return;
        await axios.delete(`/sessions/delete/${id}`, { headers: xhrJson });
        load();
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Sessions'}</h1>
                <UiButtonLink to="/settings/sessions/create">Create</UiButtonLink>
            </div>
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>Title</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD>{row.title || row.name}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup
                                            editTo={`/settings/sessions/${row.id}/edit`}
                                            translateTo={`/settings/sessions/${row.id}/translate`}
                                            onDelete={() => remove(row.id)}
                                        />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={2} message="No sessions found." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}
