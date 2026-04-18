import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell, readPaginate } from '../../ExaminationModuleShared';
import {
    IconPlus,
    UiButtonLink,
    UiHeadRow,
    UiPager,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
    UiIconLinkEdit,
    UiIconButtonDelete,
} from '../../../ui/UiKit';
import { xhrJson } from '../../../api/xhrJson';

export function MarksGradesListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [page, setPage] = useState(1);
    const [last, setLast] = useState(1);
    const [err, setErr] = useState('');
    const [busyId, setBusyId] = useState(null);

    const load = (p = 1) =>
        axios
            .get('/marks-grade', { headers: xhrJson, params: { page: p } })
            .then((r) => {
                const { rows: list, page: cur, last: lst } = readPaginate(r);
                setRows(list);
                setPage(cur);
                setLast(lst);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));

    useEffect(() => {
        load(1);
    }, []);

    const remove = async (id) => {
        if (!window.confirm('Delete this marks grade?')) return;
        setBusyId(id);
        try {
            await axios.delete(`/marks-grade/delete/${id}`, { headers: xhrJson });
            load(page);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        } finally {
            setBusyId(null);
        }
    };

    return (
        <Shell Layout={Layout} wide>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Marks grades'}</h1>
                <div className="flex flex-wrap gap-2">
                    <UiButtonLink to="/examination" variant="secondary">
                        Back
                    </UiButtonLink>
                    <UiButtonLink to="/examination/marks-grades/create" variant="primary" leftIcon={<IconPlus />}>
                        Add
                    </UiButtonLink>
                </div>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>Name</UiTH>
                            <UiTH>Point</UiTH>
                            <UiTH>%</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.map((row) => (
                            <UiTR key={row.id}>
                                <UiTD className="font-medium">{row.name}</UiTD>
                                <UiTD>{row.point}</UiTD>
                                <UiTD>
                                    {row.percent_from} – {row.percent_upto}
                                </UiTD>
                                <UiTD className="text-right">
                                    <div className="flex items-center justify-end gap-2">
                                        <UiIconLinkEdit to={`/examination/marks-grades/${row.id}/edit`} />
                                        <UiIconButtonDelete
                                            onClick={() => remove(row.id)}
                                            busy={busyId === row.id}
                                            label="Delete"
                                        />
                                    </div>
                                </UiTD>
                            </UiTR>
                        ))}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
            {!rows.length ? <p className="px-4 py-8 text-center text-sm text-gray-500">No records.</p> : null}
            <UiPager page={page} lastPage={last} onPrev={() => load(page - 1)} onNext={() => load(page + 1)} />
        </Shell>
    );
}
