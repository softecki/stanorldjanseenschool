import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';
import {
    UiActionGroup,
    UiButton,
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

export function SmsTemplateListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const load = () =>
        axios
            .get('/communication/template', { headers: xhrJson })
            .then((r) => {
                setRows(paginateRows(r));
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));

    useEffect(() => {
        load();
    }, []);

    const remove = async (tid) => {
        if (!window.confirm('Delete this template?')) return;
        try {
            await axios.delete(`/communication/template/delete/${tid}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    const runDelivery = async () => {
        try {
            const { data } = await axios.get('/communication/template/delivery', { headers: xhrJson });
            alert(data?.message || 'Delivery sync completed.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delivery failed.');
        }
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'SMS / Mail templates'}</h1>
                </div>
                <div className="flex flex-wrap gap-2">
                    <UiButton type="button" variant="secondary" onClick={runDelivery}>
                        Sync delivery reports
                    </UiButton>
                    <UiButtonLink to="/communication/template/create">Create</UiButtonLink>
                </div>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>Title</UiTH>
                            <UiTH>Type</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD className="font-medium">{row.title}</UiTD>
                                    <UiTD className="uppercase">{row.type}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup editTo={`/communication/template/${row.id}/edit`} onDelete={() => remove(row.id)} />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={3} message="No templates yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}

