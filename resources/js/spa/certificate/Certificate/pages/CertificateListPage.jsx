import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell, paginateRows } from '../../CertificateModuleShared';
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

export function CertificateListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const load = () =>
        axios
            .get('/certificate', { headers: xhrJson })
            .then((r) => {
                setRows(paginateRows(r));
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load certificates.'));

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this certificate template?')) return;
        try {
            await axios.delete(`/certificate/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Certificates'}</h1>
                    <p className="text-sm text-gray-500">Design templates and generate for students.</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <UiButtonLink variant="secondary" to="/certificate/generate">
                        Generate
                    </UiButtonLink>
                    <UiButtonLink to="/certificate/create">Create template</UiButtonLink>
                </div>
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
                                        <UiActionGroup editTo={`/certificate/${row.id}/edit`} onDelete={() => remove(row.id)} />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={2} message="No certificate templates yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}

