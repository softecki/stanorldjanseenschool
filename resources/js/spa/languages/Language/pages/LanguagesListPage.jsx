import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../../api/xhrJson';
import {
    UiButtonLink,
    UiHeadRow,
    UiIconButtonDelete,
    UiIconLinkEdit,
    UiIconLinkTerms,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

export function LanguagesListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');

    const load = async () => {
        try {
            const r = await axios.get('/languages', { headers: xhrJson });
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
        if (!window.confirm('Delete this language?')) return;
        try {
            await axios.delete(`/languages/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-6xl space-y-4 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Languages'}</h1>
                    <UiButtonLink to="/languages/create">Create</UiButtonLink>
                </div>
                {err ? <p className="text-sm text-red-600">{err}</p> : null}
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>Name</UiTH>
                                <UiTH>Code</UiTH>
                                <UiTH>Icon</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((r) => (
                                <UiTR key={r.id}>
                                    <UiTD>{r.name}</UiTD>
                                    <UiTD>{r.code}</UiTD>
                                    <UiTD>
                                        <i className={r.icon_class} />
                                    </UiTD>
                                    <UiTD className="text-right">
                                        <div className="flex items-center justify-end gap-2">
                                            <UiIconLinkEdit to={`/languages/${r.id}/edit`} />
                                            <UiIconLinkTerms to={`/languages/${r.id}/terms`} />
                                            {r.code !== 'en' ? <UiIconButtonDelete onClick={() => remove(r.id)} /> : null}
                                        </div>
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
