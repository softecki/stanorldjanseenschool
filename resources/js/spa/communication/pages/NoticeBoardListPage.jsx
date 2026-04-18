import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';
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
} from '../../ui/UiKit';

export function NoticeBoardListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const load = () =>
        axios
            .get('/communication/notice-board', { headers: xhrJson })
            .then((r) => {
                setRows(paginateRows(r));
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this notice?')) return;
        try {
            await axios.delete(`/communication/notice-board/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Notice board'}</h1>
                </div>
                <UiButtonLink to="/communication/notice-board/create">Create</UiButtonLink>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
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
                                    <UiTD className="font-medium">{row.title}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup
                                            editTo={`/communication/notice-board/${row.id}/edit`}
                                            translateTo={`/communication/notice-board/${row.id}/translate`}
                                            onDelete={() => remove(row.id)}
                                        />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={2} message="No notices yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}

