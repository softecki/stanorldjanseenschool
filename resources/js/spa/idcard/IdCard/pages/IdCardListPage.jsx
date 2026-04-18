import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../../api/xhrJson';
import {
    UiActionGroup,
    UiButtonLink,
    UiHeadRow,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

export function IdCardListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');

    const load = async () => {
        try {
            const r = await axios.get('/idcard', { headers: xhrJson });
            setRows(r.data?.data?.data || r.data?.data || []);
            setMeta(r.data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load.');
        }
    };

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this template?')) return;
        try {
            await axios.delete(`/idcard/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-6xl space-y-4 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'ID cards'}</h1>
                    <div className="flex gap-2">
                        <UiButtonLink variant="secondary" to="/idcard/generate">
                            Generate
                        </UiButtonLink>
                        <UiButtonLink to="/idcard/create">Create</UiButtonLink>
                    </div>
                </div>
                {err ? <p className="text-sm text-red-600">{err}</p> : null}
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>Title</UiTH>
                                <UiTH>Expired</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((r) => (
                                <UiTR key={r.id}>
                                    <UiTD>{r.title}</UiTD>
                                    <UiTD>{r.expired_date || '-'}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup editTo={`/idcard/${r.id}/edit`} onDelete={() => remove(r.id)} />
                                    </UiTD>
                                </UiTR>
                            ))}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>
            </div>
        </Layout>
    );
}
