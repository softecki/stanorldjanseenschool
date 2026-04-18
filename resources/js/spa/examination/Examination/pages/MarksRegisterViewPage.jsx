import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useParams } from 'react-router-dom';

import { Shell } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import { UiButtonLink, UiHeadRow, UiTable, UiTableWrap, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../../../ui/UiKit';

export function MarksRegisterViewPage({ Layout }) {
    const { id } = useParams();
    const [payload, setPayload] = useState(null);
    const [err, setErr] = useState('');

    useEffect(() => {
        axios
            .get('/marks-register/show', { headers: xhrJson, params: { id } })
            .then((r) => setPayload({ inner: r.data?.data || null, meta: r.data?.meta || {} }))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [id]);

    const data = payload?.inner;
    const students = data?.students || [];
    const mr = data?.marks_register;

    return (
        <Shell Layout={Layout} wide>
            <UiButtonLink variant="ghost" className="mb-2 px-0 text-sm text-blue-700" to="/examination/marks-register">
                ← Marks register
            </UiButtonLink>
            <h1 className="text-2xl font-semibold text-gray-900">{payload?.meta?.title || 'Marks register detail'}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            {mr ? (
                <p className="text-sm text-gray-600">
                    Register #{mr.id} — class {mr.classes_id}, section {mr.section_id}, exam {mr.exam_type_id}, subject {mr.subject_id}
                </p>
            ) : null}
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>Student</UiTH>
                            <UiTH>Roll</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {students.map((row) => {
                            const st = row.student || row;
                            const name = st.full_name || [st.first_name, st.last_name].filter(Boolean).join(' ');
                            return (
                                <UiTR key={row.id || st.id}>
                                    <UiTD>{name || st.id}</UiTD>
                                    <UiTD>{st.roll_no || '-'}</UiTD>
                                </UiTR>
                            );
                        })}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}

