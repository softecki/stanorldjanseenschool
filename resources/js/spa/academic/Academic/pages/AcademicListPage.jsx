import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { FullPageLoader, Panel, firstValue, normalizeRows, optionFrom } from '../../AcademicModuleShared';
import {
    IconPlus,
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
import { xhrJson } from '../../../api/xhrJson';

export function AcademicListPage({ Layout, title, endpoint, createTo, editBase, viewBase }) {
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');
    const [busyId, setBusyId] = useState(null);
    const [loading, setLoading] = useState(true);
    useEffect(() => {
        setLoading(true);
        axios.get(endpoint, { headers: xhrJson })
            .then((r) => setRows(normalizeRows(r.data)))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'))
            .finally(() => setLoading(false));
    }, [endpoint]);
    const deleteRow = async (id) => {
        if (!window.confirm('Delete this item?')) return;
        setBusyId(id);
        setErr('');
        try {
            await axios.delete(`${endpoint}/delete/${id}`, { headers: xhrJson });
            setRows((prev) => prev.filter((r) => r.id !== id));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to delete item.');
        } finally {
            setBusyId(null);
        }
    };
    return (
        <Panel Layout={Layout} title={title}>
            <div className="mb-4 flex justify-end">
                <UiButtonLink to={createTo} variant="primary" leftIcon={<IconPlus />}>
                    Create
                </UiButtonLink>
            </div>
            {err ? <p className="mb-2 text-sm text-red-600">{err}</p> : null}
            {loading ? <FullPageLoader text="Loading academic items..." /> : null}
            {!loading ? (
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>#</UiTH>
                                <UiTH>Name</UiTH>
                                <UiTH>Status</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((row, idx) => (
                                <UiTR key={row.id || idx}>
                                    <UiTD className="font-medium text-gray-700">{idx + 1}</UiTD>
                                    <UiTD>{firstValue(row, ['name', 'title', 'type'])}</UiTD>
                                    <UiTD className="text-gray-700">
                                        {String(firstValue(row, ['status'])) === '1'
                                            ? 'Active'
                                            : firstValue(row, ['status']) === '-'
                                              ? '-'
                                              : 'Inactive'}
                                    </UiTD>
                                    <UiTD>
                                        <UiActionGroup
                                            viewTo={`${viewBase}/${row.id}`}
                                            editTo={`${editBase}/${row.id}/edit`}
                                            onDelete={() => deleteRow(row.id)}
                                            busy={busyId === row.id}
                                        />
                                    </UiTD>
                                </UiTR>
                            ))}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>
            ) : null}
        </Panel>
    );
}
