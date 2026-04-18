import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import {
    UiHeadRow,
    UiIconLinkView,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';

export function DeletedHistoryPage() {
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');

    useEffect(() => {
        axios
            .get('/student-deleted-history', { headers: xhrJson })
            .then((r) => {
                const d = r.data?.data || {};
                setRows(d.data || []);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load deleted history.'));
    }, []);

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">Deleted Student History</h1>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>#</UiTH>
                                <UiTH>Student</UiTH>
                                <UiTH>Admission No</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((r, idx) => (
                                <UiTR key={r.id}>
                                    <UiTD className="font-medium">{idx + 1}</UiTD>
                                    <UiTD>{r.student_name || r.name || '-'}</UiTD>
                                    <UiTD>{r.admission_no || '-'}</UiTD>
                                    <UiTD className="text-right">
                                        <div className="flex justify-end">
                                            <UiIconLinkView to={`/deleted-history/${r.id}`} />
                                        </div>
                                    </UiTD>
                                </UiTR>
                            ))}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>
            </div>
        </AdminLayout>
    );
}
