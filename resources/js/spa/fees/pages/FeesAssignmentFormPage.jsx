import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { optionLabel, studentsTableClass, FullPageLoader } from '../FeesModuleShared';
import { UiButton, UiButtonLink, UiHeadRow, UiTable, UiTBody, UiTD, UiTH, UiTHead, UiTR, UiTableWrap } from '../../ui/UiKit';

const label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-600';
const input = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
const card = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5';

function classOptionValue(c) {
    if (c == null) return '';
    if (c.classes_id != null) return String(c.classes_id);
    if (c.class?.id != null) return String(c.class.id);
    if (c.id != null) return String(c.id);
    return '';
}

function classOptionLabel(c) {
    if (c == null) return '';
    return c.class?.name || c.class_name || c.name || `Class #${classOptionValue(c) || '?'}`;
}

function groupOptionValue(g) {
    if (g == null) return '';
    if (g.group?.id != null) return String(g.group.id);
    if (g.id != null) return String(g.id);
    return '';
}

function groupOptionLabel(g) {
    if (g == null) return '';
    return g.group?.name || g.name || `Group #${groupOptionValue(g) || '?'}`;
}

export function FeesAssignmentFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const editBlocksInitializedRef = useRef(false);
    const [meta, setMeta] = useState({});
    const [studentsByMaster, setStudentsByMaster] = useState({});
    const [form, setForm] = useState({
        fees_group: '',
        class: '',
        category: '',
        fees_master_ids: [],
        student_ids: [],
    });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [mastersLoading, setMastersLoading] = useState(false);
    const [studentsLoading, setStudentsLoading] = useState(false);
    const [studentRows, setStudentRows] = useState([]);
    const [masters, setMasters] = useState([]);

    useEffect(() => {
        editBlocksInitializedRef.current = false;
        setStudentsByMaster({});
    }, [edit, id]);

    useEffect(() => {
        setLoading(true);
        const url = edit ? `/fees-assign/edit/${id}` : '/fees-assign/create';
        axios
            .get(url, { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta(m);
                if (edit) {
                    const d = r.data?.data || {};
                    const assignedMasters = (m?.assigned_fes_masters || [])
                        .map((x) => Number(x))
                        .filter((n) => !Number.isNaN(n));
                    setForm({
                        fees_group: String(d?.fees_group_id ?? ''),
                        class: String(d?.classes_id ?? ''),
                        category: d?.category_id != null ? String(d.category_id) : '',
                        fees_master_ids: assignedMasters,
                        student_ids: [],
                    });
                } else {
                    setForm({
                        fees_group: '',
                        class: '',
                        category: '',
                        fees_master_ids: [],
                        student_ids: [],
                    });
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
    }, [edit, id]);

    useEffect(() => {
        if (!form.fees_group) {
            setMasters([]);
            return;
        }
        setMastersLoading(true);
        axios
            .get('/fees-assign/get-all-type', { headers: xhrJson, params: { id: form.fees_group } })
            .then((r) => setMasters(r.data?.data || []))
            .catch(() => setMasters([]))
            .finally(() => setMastersLoading(false));
    }, [form.fees_group]);

    useEffect(() => {
        if (!form.class) {
            setStudentRows([]);
            return;
        }
        setStudentsLoading(true);
        const params = { class: form.class };
        if (form.category) params.category = form.category;
        if (edit && id) params.fees_assign_id = id;
        axios
            .get('/fees-assign/get-fees-assign-students', { headers: xhrJson, params })
            .then((r) => {
                const rows = r.data?.data || [];
                setStudentRows(rows);
            })
            .catch(() => setStudentRows([]))
            .finally(() => setStudentsLoading(false));
    }, [form.class, form.category, edit, id]);

    useEffect(() => {
        if (!edit || masters.length === 0 || editBlocksInitializedRef.current) {
            return;
        }
        const fromApi = meta.assigned_students_by_master || {};
        const next = {};
        masters.forEach((m) => {
            const k = String(m.id);
            const arr = Array.isArray(fromApi[k]) ? fromApi[k] : Array.isArray(fromApi[m.id]) ? fromApi[m.id] : [];
            next[k] = Array.from(new Set(arr.map(Number).filter((n) => n > 0)));
        });
        setStudentsByMaster(next);
        editBlocksInitializedRef.current = true;
    }, [edit, masters, meta]);

    const masterBucket = useCallback((masterId) => studentsByMaster[String(masterId)] || [], [studentsByMaster]);

    const toggleStudentForMasterExclusive = useCallback((masterId, studentTableId, checked) => {
        const mid = String(masterId);
        const sid = Number(studentTableId);
        setStudentsByMaster((prev) => {
            const next = { ...prev };
            if (checked) {
                Object.keys(next).forEach((k) => {
                    next[k] = (next[k] || []).filter((id) => Number(id) !== sid);
                });
                next[mid] = [...new Set([...(next[mid] || []), sid])];
            } else {
                next[mid] = (next[mid] || []).filter((id) => Number(id) !== sid);
            }
            return next;
        });
    }, []);

    const toggleBlockVisibleStudents = useCallback(
        (masterId, checked) => {
            const visibleIds = studentRows.map((row) => Number(row.student_id)).filter(Number.isFinite);
            setStudentsByMaster((prev) => {
                let next = { ...prev };
                if (checked) {
                    visibleIds.forEach((sid) => {
                        Object.keys(next).forEach((k) => {
                            next[k] = (next[k] || []).filter((id) => Number(id) !== sid);
                        });
                    });
                    const mid = String(masterId);
                    const curMid = next[mid] || [];
                    next[mid] = [...new Set([...curMid, ...visibleIds])];
                } else {
                    const mid = String(masterId);
                    next[mid] = (next[mid] || []).filter((sid) => !visibleIds.includes(Number(sid)));
                }
                return next;
            });
        },
        [studentRows],
    );

    const blockVisibleAllSelected = useCallback(
        (masterId) => {
            if (studentRows.length === 0) return false;
            const ids = masterBucket(masterId).map(Number);
            return studentRows.every((row) => ids.includes(Number(row.student_id)));
        },
        [studentRows, masterBucket],
    );

    const editAssignedStudentTotal = useMemo(
        () => new Set(Object.values(studentsByMaster || {}).flatMap((xs) => (Array.isArray(xs) ? xs : []).map(Number))).size,
        [studentsByMaster],
    );

    const selectedStudentCount = useMemo(() => (form.student_ids || []).length, [form.student_ids]);

    const toggleStudent = (studentTableId, checked) => {
        const sid = Number(studentTableId);
        setForm((f) => ({
            ...f,
            student_ids: checked ? [...new Set([...(f.student_ids || []), sid])] : (f.student_ids || []).filter((x) => Number(x) !== sid),
        }));
    };

    const toggleAllVisibleStudents = (checked) => {
        const visibleIds = studentRows.map((row) => Number(row.student_id));
        setForm((f) => {
            const cur = (f.student_ids || []).map(Number);
            if (checked) {
                return { ...f, student_ids: [...new Set([...cur, ...visibleIds])] };
            }
            return { ...f, student_ids: cur.filter((sid) => !visibleIds.includes(sid)) };
        });
    };

    const visibleAllSelected =
        studentRows.length > 0 && studentRows.every((row) => (form.student_ids || []).map(Number).includes(Number(row.student_id)));

    const setFeesMasterSingle = (masterId) => {
        const mid = Number(masterId);
        setForm((f) => ({
            ...f,
            fees_master_ids: Number.isFinite(mid) && mid > 0 ? [mid] : [],
        }));
    };

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        const assignmentLines = edit
            ? Object.entries(studentsByMaster)
                  .map(([mid, xs]) => ({
                      fees_master_id: Number(mid),
                      student_ids: (Array.isArray(xs) ? xs : []).map(Number).filter((n) => Number.isFinite(n) && n > 0),
                  }))
                  .filter((line) => line.student_ids.length > 0 && Number.isFinite(line.fees_master_id) && line.fees_master_id > 0)
            : [];

        const feesMasterIds = edit
            ? assignmentLines.map((l) => l.fees_master_id)
            : (form.fees_master_ids || []).map(Number).filter((n) => !Number.isNaN(n));
        const studentIds = edit
            ? [...new Set(assignmentLines.flatMap((l) => l.student_ids))]
            : (form.student_ids || []).map(Number).filter((n) => !Number.isNaN(n));

        const seenStudent = new Set();
        if (edit) {
            if (assignmentLines.length === 0) {
                setErr('Assign at least one student to a fee type block.');
                return;
            }
            for (let i = 0; i < assignmentLines.length; i += 1) {
                for (let j = 0; j < assignmentLines[i].student_ids.length; j += 1) {
                    const sid = assignmentLines[i].student_ids[j];
                    if (seenStudent.has(sid)) {
                        setErr('Each student can only appear under one fee type (same assignment). Remove duplicates.');
                        return;
                    }
                    seenStudent.add(sid);
                }
            }
        }

        if (!edit && feesMasterIds.length !== 1) {
            setErr(feesMasterIds.length === 0 ? 'Select one fee master (fees type).' : 'Only one fee master may be selected at a time.');
            return;
        }

        if (studentIds.length === 0) {
            setErr('Select at least one student.');
            return;
        }
        setSaving(true);
        try {
            const payload = {
                fees_group: form.fees_group,
                class: form.class,
                student_category: form.category ?? '',
                gender: '',
                fees_master_ids: edit ? feesMasterIds : feesMasterIds.slice(0, 1),
                student_ids: studentIds,
                ...(edit ? { assignment_lines: assignmentLines } : {}),
            };
            if (edit) await axios.put(`/fees-assign/update/${id}`, payload, { headers: xhrJson });
            else await axios.post('/fees-assign/store', payload, { headers: xhrJson });
            nav('/assignments');
        } catch (ex) {
            setErr(ex.response?.data?.message || (typeof ex.response?.data === 'string' ? ex.response.data : 'Save failed.'));
        } finally {
            setSaving(false);
        }
    };

    const groupList = meta.fees_groups || [];
    const classList = meta.classes || [];

    return (
        <Layout>
            <div className="mx-auto max-w-7xl bg-[#f6f7f9] px-4 py-1.5 sm:px-6 sm:py-2 lg:px-8 lg:py-2.5">
                {edit ? (
                    <div className="mb-4 border-b border-slate-200/80 pb-3">
                        <h1 className="text-xl font-bold tracking-tight text-slate-900">Edit fees assignment</h1>
                    </div>
                ) : null}

                {err ? (
                    <div className="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {loading ? <FullPageLoader text="Loading assignment form…" /> : null}

                {!loading ? (
                    <form onSubmit={submit} className="space-y-6">
                        <div className={card}>
                            <h2 className="mb-4 text-sm font-semibold text-slate-900">1. Scope (required)</h2>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div>
                                    <label className={label} htmlFor="fa-group">
                                        Fees group <span className="text-rose-600">*</span>
                                    </label>
                                    <select
                                        id="fa-group"
                                        className={`${input} ${edit ? 'cursor-not-allowed bg-slate-50 text-slate-700' : ''}`}
                                        value={form.fees_group}
                                        disabled={edit}
                                        title={edit ? 'Fees group cannot be changed while editing — create a new assignment for a different group.' : undefined}
                                        onChange={(e) => {
                                            editBlocksInitializedRef.current = false;
                                            setStudentsByMaster({});
                                            setForm((f) => ({ ...f, fees_group: e.target.value, fees_master_ids: [] }));
                                        }}
                                        required
                                    >
                                        <option value="">Select fees group</option>
                                        {groupList.map((g) => (
                                            <option key={groupOptionValue(g) || g.id} value={groupOptionValue(g)}>
                                                {groupOptionLabel(g)}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className={label} htmlFor="fa-class">
                                        Class <span className="text-rose-600">*</span>
                                    </label>
                                    <select
                                        id="fa-class"
                                        className={input}
                                        value={form.class}
                                        onChange={(e) =>
                                            setForm((f) => ({
                                                ...f,
                                                class: e.target.value,
                                                student_ids: edit ? f.student_ids : [],
                                            }))
                                        }
                                        required
                                    >
                                        <option value="">Select class</option>
                                        {classList.map((c) => {
                                            const val = classOptionValue(c);
                                            if (!val) return null;
                                            return (
                                                <option key={val} value={val}>
                                                    {classOptionLabel(c)}
                                                </option>
                                            );
                                        })}
                                    </select>
                                </div>
                                <div>
                                    <label className={label} htmlFor="fa-cat">
                                        Student category
                                    </label>
                                    <select
                                        id="fa-cat"
                                        className={input}
                                        value={form.category}
                                        onChange={(e) => setForm((f) => ({ ...f, category: e.target.value }))}
                                    >
                                        <option value="">All categories</option>
                                        {(meta.categories || []).map((cat) => (
                                            <option key={cat.id} value={cat.id}>
                                                {cat.name}
                                            </option>
                                        ))}
                                    </select>
                                    <p className="mt-1 text-xs text-slate-500">Saved on the assignment; also filters the student list.</p>
                                </div>
                            </div>
                        </div>

                        {edit ? (
                            <div className="space-y-6">
                                <div className="rounded-xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 text-xs leading-relaxed text-indigo-950">
                                    <p>
                                        <span className="font-semibold">One fee type per student</span> — in this assignment, each student may only belong to one
                                        fee type for the selected fees group. Checking them under a fee type removes them from all other blocks.
                                    </p>
                                    <p className="mt-2 font-medium text-indigo-900">Students assigned in blocks: {editAssignedStudentTotal}</p>
                                </div>
                                <div>
                                    <h2 className="mb-1 text-sm font-semibold text-slate-900">2. Fee types & students</h2>
                                    <p className="text-xs text-slate-600">Pick the class above, then choose students inside each fee type block.</p>
                                </div>
                                {mastersLoading ? <p className="text-sm text-slate-500">Loading fee types…</p> : null}
                                {!mastersLoading && !form.fees_group ? <p className="text-sm text-slate-500">Select a fees group first.</p> : null}
                                {!mastersLoading && form.fees_group && masters.length === 0 ? (
                                    <p className="text-sm text-amber-700">No active fee masters in this group for the current session.</p>
                                ) : null}
                                {!mastersLoading &&
                                    masters.map((m) => {
                                        const bids = masterBucket(m.id).map(Number);
                                        return (
                                            <div key={m.id} className={card}>
                                                <div className="mb-3 border-b border-slate-100 pb-3">
                                                    <div className="flex flex-wrap items-start justify-between gap-2">
                                                        <div>
                                                            <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Fee type</p>
                                                            <h3 className="text-base font-semibold text-slate-900">{m?.type?.name || optionLabel(m)}</h3>
                                                            <p className="mt-1 font-mono text-xs text-slate-500">
                                                                Master #{m.id}
                                                                <span className="mx-2 text-slate-300">·</span>
                                                                Amount{' '}
                                                                <span className="tabular-nums text-slate-700">{m?.amount ?? '—'}</span>
                                                            </p>
                                                        </div>
                                                        <span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{bids.length} student(s)</span>
                                                    </div>
                                                </div>
                                                {!form.class ? <p className="text-sm text-slate-500">Select a class to load students.</p> : null}
                                                {form.class && studentsLoading ? <p className="text-sm text-slate-500">Loading students…</p> : null}
                                                {form.class && !studentsLoading && studentRows.length === 0 ? (
                                                    <p className="text-sm text-slate-500">No students in this class (for the current filters).</p>
                                                ) : null}
                                                {form.class && !studentsLoading && studentRows.length > 0 ? (
                                                    <div className={`${studentsTableClass()} max-h-72 overflow-y-auto`}>
                                                        <UiTable>
                                                            <UiTHead>
                                                                <UiHeadRow>
                                                                    <UiTH>
                                                                        <input
                                                                            type="checkbox"
                                                                            className="h-4 w-4 rounded"
                                                                            checked={blockVisibleAllSelected(m.id)}
                                                                            onChange={(e) => toggleBlockVisibleStudents(m.id, e.target.checked)}
                                                                            title="Select visible for this fee type"
                                                                        />
                                                                    </UiTH>
                                                                    <UiTH>Adm. no.</UiTH>
                                                                    <UiTH>Name</UiTH>
                                                                    <UiTH>Class</UiTH>
                                                                    <UiTH>Guardian</UiTH>
                                                                    <UiTH>Mobile</UiTH>
                                                                </UiHeadRow>
                                                            </UiTHead>
                                                            <UiTBody>
                                                                {studentRows.map((row) => {
                                                                    const sid = Number(row.student_id);
                                                                    const st = row.student;
                                                                    const checked = bids.includes(sid);
                                                                    const name = st ? `${st.first_name || ''} ${st.last_name || ''}`.trim() : `Student #${sid}`;
                                                                    return (
                                                                        <UiTR key={`${m.id}-${row.id}-${sid}`}>
                                                                            <UiTD>
                                                                                <input
                                                                                    type="checkbox"
                                                                                    className="h-4 w-4 rounded"
                                                                                    checked={checked}
                                                                                    onChange={(e) => toggleStudentForMasterExclusive(m.id, sid, e.target.checked)}
                                                                                />
                                                                            </UiTD>
                                                                            <UiTD className="text-sm">{st?.admission_no ?? '—'}</UiTD>
                                                                            <UiTD className="text-sm font-medium text-slate-900">{name}</UiTD>
                                                                            <UiTD className="text-sm">{row?.class?.name ?? '—'}</UiTD>
                                                                            <UiTD className="text-sm text-slate-600">{st?.parent?.guardian_name ?? '—'}</UiTD>
                                                                            <UiTD className="text-sm">{st?.mobile ?? '—'}</UiTD>
                                                                        </UiTR>
                                                                    );
                                                                })}
                                                            </UiTBody>
                                                        </UiTable>
                                                    </div>
                                                ) : null}
                                            </div>
                                        );
                                    })}
                            </div>
                        ) : (
                            <div className="grid gap-6 lg:grid-cols-2">
                                <div className={card}>
                                    <div className="mb-3 flex items-center justify-between gap-2">
                                        <h2 className="text-sm font-semibold text-slate-900">2. Fee masters (types)</h2>
                                        <span className="text-xs font-medium text-slate-500">Choose one row</span>
                                    </div>
                                    {mastersLoading ? <p className="text-sm text-slate-500">Loading…</p> : null}
                                    {!mastersLoading && !form.fees_group ? <p className="text-sm text-slate-500">Select a fees group first.</p> : null}
                                    {!mastersLoading && form.fees_group && masters.length === 0 ? (
                                        <p className="text-sm text-amber-700">No active fee masters in this group for the current session.</p>
                                    ) : null}
                                    {!mastersLoading && masters.length > 0 ? (
                                        <UiTableWrap>
                                            <UiTable>
                                                <UiTHead>
                                                    <UiHeadRow>
                                                        <UiTH className="w-10" />
                                                        <UiTH>Type</UiTH>
                                                        <UiTH className="text-right">Amount</UiTH>
                                                    </UiHeadRow>
                                                </UiTHead>
                                                <UiTBody>
                                                    {masters.map((m) => {
                                                        const checked = (form.fees_master_ids || []).map(Number).includes(Number(m.id));
                                                        return (
                                                            <UiTR key={m.id}>
                                                                <UiTD>
                                                                    <input
                                                                        type="radio"
                                                                        name="fa-fee-master"
                                                                        className="h-4 w-4 border-slate-300"
                                                                        checked={checked}
                                                                        onChange={() => setFeesMasterSingle(m.id)}
                                                                        aria-label={`Select fee master ${optionLabel(m)}`}
                                                                    />
                                                                </UiTD>
                                                                <UiTD>{m?.type?.name || optionLabel(m)}</UiTD>
                                                                <UiTD className="text-right tabular-nums">{m?.amount ?? '—'}</UiTD>
                                                            </UiTR>
                                                        );
                                                    })}
                                                </UiTBody>
                                            </UiTable>
                                        </UiTableWrap>
                                    ) : null}
                                </div>

                                <div className={card}>
                                    <div className="mb-3 flex items-center justify-between gap-2">
                                        <h2 className="text-sm font-semibold text-slate-900">3. Students</h2>
                                        <span className="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                            {selectedStudentCount} selected
                                        </span>
                                    </div>
                                    {studentsLoading ? <p className="text-sm text-slate-500">Loading students…</p> : null}
                                    {!studentsLoading && !form.class ? <p className="text-sm text-slate-500">Select a class to load students.</p> : null}
                                    {!studentsLoading && form.class && studentRows.length === 0 ? (
                                        <p className="text-sm text-slate-500">No students in this class (for the current filters).</p>
                                    ) : null}
                                    {!studentsLoading && studentRows.length > 0 ? (
                                        <div className={`${studentsTableClass()} max-h-[28rem] overflow-y-auto`}>
                                            <UiTable>
                                                <UiTHead>
                                                    <UiHeadRow>
                                                        <UiTH>
                                                            <input
                                                                type="checkbox"
                                                                className="h-4 w-4 rounded"
                                                                checked={visibleAllSelected}
                                                                onChange={(e) => toggleAllVisibleStudents(e.target.checked)}
                                                                title="Select visible"
                                                            />
                                                        </UiTH>
                                                        <UiTH>Adm. no.</UiTH>
                                                        <UiTH>Name</UiTH>
                                                        <UiTH>Class</UiTH>
                                                        <UiTH>Guardian</UiTH>
                                                        <UiTH>Mobile</UiTH>
                                                    </UiHeadRow>
                                                </UiTHead>
                                                <UiTBody>
                                                    {studentRows.map((row) => {
                                                        const sid = Number(row.student_id);
                                                        const st = row.student;
                                                        const checked = (form.student_ids || []).map(Number).includes(sid);
                                                        const name = st ? `${st.first_name || ''} ${st.last_name || ''}`.trim() : `Student #${sid}`;
                                                        return (
                                                            <UiTR key={`${row.id}-${sid}`}>
                                                                <UiTD>
                                                                    <input
                                                                        type="checkbox"
                                                                        className="h-4 w-4 rounded"
                                                                        checked={checked}
                                                                        onChange={(e) => toggleStudent(sid, e.target.checked)}
                                                                    />
                                                                </UiTD>
                                                                <UiTD className="text-sm">{st?.admission_no ?? '—'}</UiTD>
                                                                <UiTD className="text-sm font-medium text-slate-900">{name}</UiTD>
                                                                <UiTD className="text-sm">{row?.class?.name ?? '—'}</UiTD>
                                                                <UiTD className="text-sm text-slate-600">{st?.parent?.guardian_name ?? '—'}</UiTD>
                                                                <UiTD className="text-sm">{st?.mobile ?? '—'}</UiTD>
                                                            </UiTR>
                                                        );
                                                    })}
                                                </UiTBody>
                                            </UiTable>
                                        </div>
                                    ) : null}
                                </div>
                            </div>
                        )}

                        <div className="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200 bg-white/80 py-4">
                            <UiButtonLink to="/assignments" variant="secondary">
                                Cancel
                            </UiButtonLink>
                            <UiButton type="submit" variant="primary" disabled={saving}>
                                {saving ? 'Saving…' : edit ? 'Update assignment' : 'Create assignment'}
                            </UiButton>
                        </div>
                    </form>
                ) : null}
            </div>
        </Layout>
    );
}
