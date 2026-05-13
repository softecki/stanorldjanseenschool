import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BloodGroupModuleShared';
import { paginateState } from '../../../communication/CommunicationModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import {
    UiActionGroup,
    UiButtonLink,
    UiHeadRow,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

function formatErr(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (d.errors && typeof d.errors === 'object') {
        return Object.entries(d.errors)
            .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(' ') : v}`)
            .join(' · ');
    }
    return 'Request failed.';
}

export function BloodGroupsListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [deletingId, setDeletingId] = useState(null);

    const load = useCallback((p = 1) => {
        setErr('');
        return axios
            .get('/blood-groups', { headers: xhrJson, params: { page: p } })
            .then((r) => {
                const st = paginateState(r);
                setRows(st.rows);
                setPage(st.page);
                setLastPage(st.lastPage);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(formatErr(ex)));
    }, []);

    useEffect(() => {
        load(1);
    }, [load]);

    const remove = async (id) => {
        if (!window.confirm('Delete this blood group?')) return;
        setErr('');
        setDeletingId(id);
        try {
            await axios.delete(`/blood-groups/delete/${id}`, { headers: xhrJson });
            await load(page);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setDeletingId(null);
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
                                        <UiActionGroup editTo={`/blood-groups/${row.id}/edit`} onDelete={() => remove(row.id)} busy={deletingId === row.id} />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={3} message="No blood groups yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
            <UiPager page={page} lastPage={lastPage} onPrev={() => load(page - 1)} onNext={() => load(page + 1)} />
        </Shell>
    );
}
