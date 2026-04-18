import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { AdminLayout } from '../layout/AdminLayout';
import {
    UiActionGroup,
    UiButtonLink,
    UiHeadRow,
    UiPageLoader,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

const STATUS_ACTIVE = 1;

function sectionOptionsFrom(meta) {
    const sections = meta?.sections || [];
    return sections.map((item) => ({
        id: item?.section?.id ?? item?.id ?? '',
        name: item?.section?.name ?? item?.name ?? '-',
    })).filter((x) => x.id);
}

function classOptionsFrom(meta) {
    const classes = meta?.classes || [];
    return classes.map((item) => ({
        id: item?.class?.id ?? item?.id ?? '',
        name: item?.class?.name ?? item?.name ?? '-',
    })).filter((x) => x.id);
}

function rowFromLegacy(row) {
    const student = row?.student || {};
    return {
        id: student.id ?? row?.id,
        admissionNo: student.admission_no || '-',
        fullName: `${student.first_name || ''} ${student.last_name || ''}`.trim() || '-',
        className: row?.class?.name || '-',
        sectionName: row?.section?.name || '-',
        mobile: student.mobile_no || student.mobile || '-',
        status: student.status,
    };
}

export function StudentsPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);
    const [filters, setFilters] = useState({ class: '', section: '', name: '' });

    const classOptions = useMemo(() => classOptionsFrom(meta), [meta]);
    const sectionOptions = useMemo(() => sectionOptionsFrom(meta), [meta]);

    const hydrateFromResponse = (payload) => {
        const paged = payload?.data || {};
        const list = paged?.data || [];
        setRows(list.map(rowFromLegacy));
        setMeta(payload?.meta || {});
    };

    const loadIndex = () => {
        setLoading(true);
        setErr('');
        axios.get('/student', { headers: xhrJson })
            .then((r) => hydrateFromResponse(r.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load students.'))
            .finally(() => setLoading(false));
    };

    const search = (nextFilters) => {
        setLoading(true);
        setErr('');
        axios.post('/student/search', nextFilters, { headers: xhrJson })
            .then((r) => hydrateFromResponse(r.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to search students.'))
            .finally(() => setLoading(false));
    };

    useEffect(() => { loadIndex(); }, []);

    useEffect(() => {
        const id = setTimeout(() => {
            if (!filters.class && !filters.section && !filters.name) {
                loadIndex();
            } else {
                search(filters);
            }
        }, 350);
        return () => clearTimeout(id);
    }, [filters.class, filters.section, filters.name]);

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Students'}</h1>
                        <div className="flex flex-wrap gap-2">
                            <UiButtonLink to="/students/create">Create</UiButtonLink>
                            <UiButtonLink variant="secondary" to="/students/upload">
                                Upload
                            </UiButtonLink>
                        </div>
                    </div>
                </div>

                <div className="mb-5 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-3">
                    <select
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        value={filters.class}
                        onChange={(e) => setFilters((f) => ({ ...f, class: e.target.value, section: '' }))}
                    >
                        <option value="">Select Class</option>
                        {classOptions.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                    <select
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        value={filters.section}
                        onChange={(e) => setFilters((f) => ({ ...f, section: e.target.value }))}
                    >
                        <option value="">Select Section</option>
                        {sectionOptions.map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                    </select>
                    <input
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        placeholder="Search by student name"
                        value={filters.name}
                        onChange={(e) => setFilters((f) => ({ ...f, name: e.target.value }))}
                    />
                </div>

                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading students…" /> : null}
                {!loading ? (
                    <UiTableWrap>
                        <UiTable>
                            <UiTHead>
                                <UiHeadRow>
                                    <UiTH>Admission No</UiTH>
                                    <UiTH>Student Name</UiTH>
                                    <UiTH>Class</UiTH>
                                    <UiTH>Section</UiTH>
                                    <UiTH>Mobile</UiTH>
                                    <UiTH>Status</UiTH>
                                    <UiTH className="text-right">Actions</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.map((s) => (
                                    <UiTR key={s.id}>
                                        <UiTD className="font-medium">{s.admissionNo}</UiTD>
                                        <UiTD>{s.fullName}</UiTD>
                                        <UiTD>{s.className}</UiTD>
                                        <UiTD>{s.sectionName}</UiTD>
                                        <UiTD>{s.mobile}</UiTD>
                                        <UiTD>
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${s.status === STATUS_ACTIVE ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}
                                            >
                                                {s.status === STATUS_ACTIVE ? 'Active' : 'Inactive'}
                                            </span>
                                        </UiTD>
                                        <UiTD className="text-right">
                                            <UiActionGroup
                                                viewTo={`/students/${s.id}`}
                                                editTo={`/students/${s.id}/edit`}
                                                onDelete={async () => {
                                                    if (!window.confirm('Delete this student?')) return;
                                                    try {
                                                        await axios.delete(`/student/delete/${s.id}`, { headers: xhrJson });
                                                        loadIndex();
                                                    } catch (ex) {
                                                        setErr(ex.response?.data?.message || 'Failed to delete student.');
                                                    }
                                                }}
                                            />
                                        </UiTD>
                                    </UiTR>
                                ))}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                ) : null}
            </div>
        </AdminLayout>
    );
}

