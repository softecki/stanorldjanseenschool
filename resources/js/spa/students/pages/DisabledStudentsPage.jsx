import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { AdminLayout } from '../../layout/AdminLayout';
import {
    UiActionGroup,
    UiButton,
    UiHeadRow,
    UiPageLoader,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../ui/UiKit';

export function DisabledStudentsPage() {
    const [meta, setMeta] = useState({});
    const [rows, setRows] = useState([]);
    const [filters, setFilters] = useState({ class: '', section: '' });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);

    const toList = (value) => {
        if (Array.isArray(value)) return value;
        if (Array.isArray(value?.data)) return value.data;
        return [];
    };

    const loadIndex = () => {
        axios.get('/disabled-students', { headers: xhrJson }).then((r) => {
            setMeta(r.data?.meta || {});
            setRows([]);
        });
    };

    const loadSections = (classId) => {
        if (!classId) return setMeta((m) => ({ ...m, sections: [] }));
        axios.get('/class-setup/get-sections', { headers: xhrJson, params: { id: classId } })
            .then((r) => setMeta((m) => ({ ...m, sections: toList(r.data) })))
            .catch(() => setMeta((m) => ({ ...m, sections: [] })));
    };

    useEffect(() => {
        loadIndex();
    }, []);

    const doSearch = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErr('');
        try {
            const r = await axios.post('/disabled-students/search', filters, { headers: xhrJson });
            setRows(r.data?.data || []);
            setMeta(r.data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search disabled students.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Disabled Students'}</h1>
                </div>

                <form className="mb-5 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-3" onSubmit={doSearch}>
                    <select
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        value={filters.class}
                        onChange={(e) => {
                            const v = e.target.value;
                            setFilters((f) => ({ ...f, class: v, section: '' }));
                            loadSections(v);
                        }}
                    >
                        <option value="">Select Class</option>
                        {(meta.classes || []).map((c) => <option key={c?.class?.id || c.id} value={c?.class?.id || c.id}>{c?.class?.name || c.name}</option>)}
                    </select>
                    <select
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        value={filters.section}
                        onChange={(e) => setFilters((f) => ({ ...f, section: e.target.value }))}
                    >
                        <option value="">Select Section</option>
                        {(meta.sections || []).map((s) => <option key={s?.section?.id || s.id} value={s?.section?.id || s.id}>{s?.section?.name || s.name}</option>)}
                    </select>
                    <div className="flex justify-end">
                        <UiButton type="submit">Search</UiButton>
                    </div>
                </form>

                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading disabled students…" /> : null}
                {!loading ? (
                    <UiTableWrap>
                        <UiTable>
                            <UiTHead>
                                <UiHeadRow>
                                    <UiTH>Admission No</UiTH>
                                    <UiTH>Student Name</UiTH>
                                    <UiTH>Class (Section)</UiTH>
                                    <UiTH>Guardian</UiTH>
                                    <UiTH>DOB</UiTH>
                                    <UiTH>Gender</UiTH>
                                    <UiTH>Mobile</UiTH>
                                    <UiTH>Status</UiTH>
                                    <UiTH className="text-right">Actions</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.map((item) => {
                                    const sid = item?.student?.id;
                                    const isActive = Number(item?.student?.status) === 1;
                                    return (
                                        <UiTR key={sid || `${item?.classes_id}-${item?.section_id}`}>
                                            <UiTD className="font-medium">{item?.student?.admission_no || '-'}</UiTD>
                                            <UiTD>{`${item?.student?.first_name || ''} ${item?.student?.last_name || ''}`.trim() || '-'}</UiTD>
                                            <UiTD>{`${item?.class?.name || '-'} (${item?.section?.name || '-'})`}</UiTD>
                                            <UiTD>{item?.student?.parent?.guardian_name || '-'}</UiTD>
                                            <UiTD>{item?.student?.dob || '-'}</UiTD>
                                            <UiTD>{item?.student?.gender?.name || '-'}</UiTD>
                                            <UiTD>{item?.student?.mobile_no || item?.student?.mobile || '-'}</UiTD>
                                            <UiTD>
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}
                                                >
                                                    {isActive ? 'Active' : 'Inactive'}
                                                </span>
                                            </UiTD>
                                            <UiTD className="text-right">
                                                <UiActionGroup viewTo={`/students/${sid}`} editTo={`/students/${sid}/edit`} hideDelete />
                                            </UiTD>
                                        </UiTR>
                                    );
                                })}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                ) : null}
            </div>
        </AdminLayout>
    );
}

