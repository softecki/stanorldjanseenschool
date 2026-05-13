import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { NavLink } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import {
    UiActionGroup,
    UiButtonLink,
    UiHeadRow,
    UiPageLoader,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

/** DB / JSON often sends status as string "1"; avoid === 1 mismatch in the Status column */
function coerceStudentListStatus(raw) {
    const n = Number(raw);
    if (Number.isFinite(n)) {
        return n === 1 ? 1 : 0;
    }
    return raw == 1 ? 1 : 0;
}

const STATUS_ACTIVE = 1;

function sectionOptionsFrom(meta) {
    const sections = meta?.sections || [];
    return sections
        .map((item) => ({
            id: item?.section_id ?? item?.section?.id ?? item?.id ?? '',
            name: item?.section?.name ?? item?.name ?? '-',
        }))
        .filter((x) => x.id !== '' && x.id != null);
}

function classOptionsFrom(meta) {
    const classes = meta?.classes || [];
    return classes
        .map((item) => ({
            id: item?.classes_id ?? item?.class?.id ?? item?.id ?? '',
            name: item?.class?.name ?? item?.name ?? '-',
        }))
        .filter((x) => x.id !== '' && x.id != null);
}

function rowFromLegacy(row) {
    const student = row?.student || {};
    return {
        id: student.id ?? row?.id,
        fullName: `${student.first_name || ''} ${student.last_name || ''}`.trim() || '-',
        className: row?.class?.name || '-',
        sectionName: row?.section?.name || '-',
        mobile: student.mobile_no || student.mobile || '-',
        status: coerceStudentListStatus(student.status),
    };
}

export function StudentsPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 10, total: 0 });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);
    const [classFilter, setClassFilter] = useState('');
    const [sectionFilter, setSectionFilter] = useState('');
    const [nameInput, setNameInput] = useState('');
    const [debouncedName, setDebouncedName] = useState('');
    const [page, setPage] = useState(1);
    const [refreshToken, setRefreshToken] = useState(0);
    const prevDebouncedName = useRef(null);

    const classOptions = useMemo(() => classOptionsFrom(meta), [meta]);
    const sectionOptions = useMemo(() => sectionOptionsFrom(meta), [meta]);

    const hydrateFromResponse = useCallback((payload) => {
        const paged = payload?.data;
        const list = Array.isArray(paged?.data) ? paged.data : [];
        setRows(list.map(rowFromLegacy));
        setMeta(payload?.meta || {});
        setPagination({
            current_page: paged?.current_page ?? 1,
            last_page: paged?.last_page ?? 1,
            per_page: paged?.per_page ?? 10,
            total: paged?.total ?? list.length,
        });
    }, []);

    useEffect(() => {
        const id = setTimeout(() => setDebouncedName(nameInput.trim()), 320);
        return () => clearTimeout(id);
    }, [nameInput]);

    useEffect(() => {
        if (prevDebouncedName.current !== null && prevDebouncedName.current !== debouncedName) {
            setPage(1);
        }
        prevDebouncedName.current = debouncedName;
    }, [debouncedName]);

    useEffect(() => {
        setLoading(true);
        setErr('');
        const filtered = Boolean(classFilter || sectionFilter || debouncedName);
        const body = {
            class: classFilter || '',
            section: sectionFilter || '',
            name: debouncedName || '',
            page,
        };
        const req = filtered
            ? axios.post('/student/search', body, { headers: xhrJson })
            : axios.get('/student', { headers: xhrJson, params: { page } });
        req.then((r) => hydrateFromResponse(r.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load students.'))
            .finally(() => setLoading(false));
    }, [page, classFilter, sectionFilter, debouncedName, refreshToken, hydrateFromResponse]);

    const onClassChange = (e) => {
        const v = e.target.value;
        setClassFilter(v);
        setSectionFilter('');
        setPage(1);
    };

    const onSectionChange = (e) => {
        setSectionFilter(e.target.value);
        setPage(1);
    };

    const perPage = pagination.per_page || 10;
    const currentPage = pagination.current_page || 1;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-4 grid w-full grid-cols-2 gap-2 rounded-xl border border-gray-200 bg-white p-2">
                    <NavLink
                        to="/students"
                        end
                        className={({ isActive }) =>
                            `w-full rounded-lg px-4 py-2 text-center text-sm font-semibold transition ${
                                isActive
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'border border-gray-200 bg-gray-50 text-gray-700 hover:bg-white'
                            }`
                        }
                    >
                        Students
                    </NavLink>
                    <NavLink
                        to="/parents"
                        className={({ isActive }) =>
                            `w-full rounded-lg px-4 py-2 text-center text-sm font-semibold transition ${
                                isActive
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'border border-gray-200 bg-gray-50 text-gray-700 hover:bg-white'
                            }`
                        }
                    >
                        Guardian
                    </NavLink>
                </div>
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-wrap items-center gap-3 xl:flex-nowrap">
                        <select
                            className="min-w-[180px] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm xl:max-w-[220px] xl:flex-none"
                            value={classFilter}
                            onChange={onClassChange}
                        >
                            <option value="">All classes</option>
                            {classOptions.map((c) => (
                                <option key={String(c.id)} value={String(c.id)}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                        <select
                            className="min-w-[180px] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm disabled:bg-gray-50 disabled:text-gray-400 xl:max-w-[220px] xl:flex-none"
                            value={sectionFilter}
                            onChange={onSectionChange}
                            disabled={!classFilter}
                        >
                            <option value="">{classFilter ? 'All sections' : 'Select a class first'}</option>
                            {sectionOptions.map((s) => (
                                <option key={String(s.id)} value={String(s.id)}>
                                    {s.name}
                                </option>
                            ))}
                        </select>
                        <input
                            className="min-w-[240px] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm"
                            placeholder="Search by student name"
                            value={nameInput}
                            onChange={(e) => setNameInput(e.target.value)}
                        />
                        <div className="ml-auto flex shrink-0 flex-wrap gap-2">
                            <UiButtonLink to="/students/create">Create</UiButtonLink>
                            <UiButtonLink variant="secondary" to="/students/upload">
                                Upload
                            </UiButtonLink>
                        </div>
                    </div>
                </div>

                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading students…" /> : null}
                {!loading ? (
                    <>
                        <UiTableWrap>
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH className="w-12">#</UiTH>
                                        <UiTH>Student Name</UiTH>
                                        <UiTH>Class</UiTH>
                                        <UiTH>Section</UiTH>
                                        <UiTH>Mobile</UiTH>
                                        <UiTH>Status</UiTH>
                                        <UiTH className="text-right">Actions</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.length === 0 ? (
                                        <UiTableEmptyRow colSpan={7} message="No students match the current filters." />
                                    ) : (
                                        rows.map((s, idx) => (
                                            <UiTR key={s.id}>
                                                <UiTD className="whitespace-nowrap text-gray-500">
                                                    {(currentPage - 1) * perPage + idx + 1}
                                                </UiTD>
                                                <UiTD className="font-medium text-gray-900">{s.fullName}</UiTD>
                                                <UiTD>{s.className}</UiTD>
                                                <UiTD>{s.sectionName}</UiTD>
                                                <UiTD>{s.mobile}</UiTD>
                                                <UiTD>
                                                    <span
                                                        className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                                                            s.status === STATUS_ACTIVE
                                                                ? 'bg-emerald-50 text-emerald-700'
                                                                : 'bg-rose-50 text-rose-700'
                                                        }`}
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
                                                                setRefreshToken((t) => t + 1);
                                                            } catch (ex) {
                                                                setErr(ex.response?.data?.message || 'Failed to delete student.');
                                                            }
                                                        }}
                                                    />
                                                </UiTD>
                                            </UiTR>
                                        ))
                                    )}
                                </UiTBody>
                            </UiTable>
                        </UiTableWrap>
                        <UiPager
                            className="mt-4"
                            page={currentPage}
                            lastPage={pagination.last_page || 1}
                            onPrev={() => setPage((p) => Math.max(1, p - 1))}
                            onNext={() => setPage((p) => Math.min(pagination.last_page || 1, p + 1))}
                        />
                    </>
                ) : null}
            </div>
        </AdminLayout>
    );
}
