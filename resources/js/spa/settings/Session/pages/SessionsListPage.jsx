import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { Shell } from '../../SessionModuleShared';
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

function fmtDate(v) {
    if (v == null || v === '') return '—';
    const d = new Date(v);
    return Number.isNaN(d.getTime()) ? String(v) : d.toLocaleDateString();
}

export function SessionsListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [deletingId, setDeletingId] = useState(null);

    const load = useCallback((p = 1) => {
        setErr('');
        return axios
            .get('/sessions', { headers: xhrJson, params: { page: p } })
            .then((r) => {
                const st = paginateState(r);
                setRows(st.rows);
                setPage(st.page);
                setLastPage(st.lastPage);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(formatErr(ex)));
    }, []);

    useEffect(() => {
        load(1);
    }, [load]);

    const remove = async (id) => {
        if (!window.confirm('Delete this session?')) return;
        setErr('');
        setDeletingId(id);
        try {
            await axios.delete(`/sessions/delete/${id}`, { headers: xhrJson });
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
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Sessions'}</h1>
                    <p className="text-sm text-gray-500">Academic years / terms.</p>
                </div>
                <UiButtonLink to="/settings/sessions/create">Add session</UiButtonLink>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>Name</UiTH>
                            <UiTH>Start</UiTH>
                            <UiTH>End</UiTH>
                            <UiTH>Status</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD className="font-medium">{row.title || row.name}</UiTD>
                                    <UiTD>{fmtDate(row.start_date)}</UiTD>
                                    <UiTD>{fmtDate(row.end_date)}</UiTD>
                                    <UiTD>{row.status ? 'Active' : 'Inactive'}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup
                                            editTo={`/settings/sessions/${row.id}/edit`}
                                            translateTo={`/settings/sessions/${row.id}/translate`}
                                            onDelete={() => remove(row.id)}
                                            busy={deletingId === row.id}
                                        />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={5} message="No sessions yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
            <UiPager page={page} lastPage={lastPage} onPrev={() => load(page - 1)} onNext={() => load(page + 1)} />
        </Shell>
    );
}
