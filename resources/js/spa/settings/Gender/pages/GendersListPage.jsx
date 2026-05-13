import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Shell, paginateRows } from '../../GenderModuleShared';
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

export function GendersListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [paginator, setPaginator] = useState(null);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [deletingId, setDeletingId] = useState(null);

    const load = useCallback((page = 1) => {
        setErr('');
        return axios
            .get('/genders', { headers: xhrJson, params: { page } })
            .then((r) => {
                const { rows: list, paginator: pg } = paginateRows(r);
                setRows(list);
                setPaginator(pg);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(formatErr(ex)));
    }, []);

    useEffect(() => {
        load(1);
    }, [load]);

    const remove = async (id) => {
        if (!window.confirm('Delete this gender?')) return;
        setErr('');
        setDeletingId(id);
        try {
            await axios.delete(`/genders/delete/${id}`, { headers: xhrJson });
            await load(paginator?.current_page || 1);
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
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Genders'}</h1>
                    <p className="text-sm text-gray-500">Settings reference data.</p>
                </div>
                <UiButtonLink to="/settings/genders/create">Add gender</UiButtonLink>
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
                                        <UiActionGroup
                                            editTo={`/settings/genders/${row.id}/edit`}
                                            translateTo={`/settings/genders/${row.id}/translate`}
                                            onDelete={() => remove(row.id)}
                                            busy={deletingId === row.id}
                                        />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={3} message="No genders yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
            {paginator && paginator.last_page > 1 ? (
                <UiPager
                    page={paginator.current_page}
                    lastPage={paginator.last_page}
                    onPrev={() => load(paginator.current_page - 1)}
                    onNext={() => load(paginator.current_page + 1)}
                />
            ) : null}
        </Shell>
    );
}

