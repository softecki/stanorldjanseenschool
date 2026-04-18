import React, { useEffect, useState } from 'react';
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

export function GendersListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [paginator, setPaginator] = useState(null);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');

    const load = (page = 1) =>
        axios
            .get('/genders', { headers: xhrJson, params: { page } })
            .then((r) => {
                const { rows: list, paginator: pg } = paginateRows(r);
                setRows(list);
                setPaginator(pg);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));

    useEffect(() => {
        load();
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this gender?')) return;
        try {
            await axios.delete(`/genders/delete/${id}`, { headers: xhrJson });
            load(paginator?.current_page || 1);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
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

