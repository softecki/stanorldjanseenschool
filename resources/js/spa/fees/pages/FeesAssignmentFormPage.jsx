import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    ActionButtons,
    EntityListPage,
    EntityViewPage,
    FeesEntityFormPage,
    FullPageLoader,
    TransactionsListPage,
    normalizeFeesTransactionRows,
    optionLabel,
    panelTitle,
    statusChoices,
    studentsTableClass,
} from '../FeesModuleShared';
import { UiHeadRow, UiTable, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../../ui/UiKit';

export function FeesAssignmentFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const editStudentsSyncedRef = useRef(false);
    const [meta, setMeta] = useState({});
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
        editStudentsSyncedRef.current = false;
    }, [edit, id]);

    useEffect(() => {
        setLoading(true);
        const url = edit ? `/fees-assign/edit/${id}` : '/fees-assign/create';
        axios.get(url, { headers: xhrJson }).then((r) => {
            const m = r.data?.meta || {};
            setMeta(m);
            if (edit) {
                const d = r.data?.data || {};
                const assignedMasters = (m?.assigned_fes_masters || []).map((x) => Number(x));
                setForm({
                    fees_group: String(d?.fees_group_id ?? ''),
                    class: String(d?.classes_id ?? ''),
                    category: '',
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
        }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.')).finally(() => setLoading(false));
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
                const assigned = (r.data?.meta?.assigned_student_ids || []).map(Number);
                if (edit && id && !editStudentsSyncedRef.current) {
                    setForm((f) => ({ ...f, student_ids: assigned }));
                    editStudentsSyncedRef.current = true;
                }
            })
            .catch(() => setStudentRows([]))
            .finally(() => setStudentsLoading(false));
    }, [form.class, form.category, edit, id]);

    const selectedStudentCount = useMemo(() => (form.student_ids || []).length, [form.student_ids]);

    const toggleStudent = (studentTableId, checked) => {
        const sid = Number(studentTableId);
        setForm((f) => ({
            ...f,
            student_ids: checked
                ? [...new Set([...(f.student_ids || []), sid])]
                : (f.student_ids || []).filter((x) => Number(x) !== sid),
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
        studentRows.length > 0 &&
        studentRows.every((row) => (form.student_ids || []).map(Number).includes(Number(row.student_id)));

    const toggleFeesMaster = (masterId, checked) => {
        const mid = Number(masterId);
        setForm((f) => ({
            ...f,
            fees_master_ids: checked
                ? [...new Set([...(f.fees_master_ids || []), mid])]
                : (f.fees_master_ids || []).filter((x) => Number(x) !== mid),
        }));
    };

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        const feesMasterIds = (form.fees_master_ids || []).map(Number).filter((n) => !Number.isNaN(n));
        const studentIds = (form.student_ids || []).map(Number).filter((n) => !Number.isNaN(n));
        if (feesMasterIds.length === 0) {
            setErr('Please select at least one fees type.');
            return;
        }
        if (studentIds.length === 0) {
            setErr('Please select at least one student.');
            return;
        }
        setSaving(true);
        try {
            const payload = {
                fees_group: form.fees_group,
                class: form.class,
                fees_master_ids: feesMasterIds,
                student_ids: studentIds,
            };
            if (edit) await axios.put(`/fees-assign/update/${id}`, payload, { headers: xhrJson });
            else await axios.post('/fees-assign/store', payload, { headers: xhrJson });
            nav('/fees/assignments');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{edit ? 'Edit Fees Assignment' : 'Create Fees Assignment'}</h1>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading assignment form..." /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-3">
                            <select
                                className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                                value={form.fees_group}
                                onChange={(e) =>
                                    setForm((f) => ({
                                        ...f,
                                        fees_group: e.target.value,
                                        fees_master_ids: [],
                                    }))
                                }
                                required
                            >
                                <option value="">Select Fees Group</option>
                                {(meta.fees_groups || []).map((g) => (
                                    <option key={g?.group?.id || g.id} value={g?.group?.id || g.id}>
                                        {g?.group?.name || g.name}
                                    </option>
                                ))}
                            </select>
                            <select
                                className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
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
                                <option value="">Select Class</option>
                                {(meta.classes || []).map((c) => (
                                    <option key={c?.class?.id || c.classes_id || c.id} value={c?.class?.id || c.classes_id || c.id}>
                                        {c?.class?.name || c.name}
                                    </option>
                                ))}
                            </select>
                            <select
                                className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
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
                        </div>
                        <div className="grid gap-4 lg:grid-cols-2">
                            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p className="mb-2 text-sm font-semibold text-gray-700">Fees types</p>
                                {mastersLoading ? <p className="text-sm text-gray-500">Loading fees types…</p> : null}
                                {!mastersLoading && masters.length === 0 ? (
                                    <p className="text-sm text-gray-500">Select a fees group to load types.</p>
                                ) : null}
                                {!mastersLoading && masters.length > 0 ? (
                                    <div className={studentsTableClass()}>
                                        <UiTable>
                                            <UiTHead>
                                                <UiHeadRow>
                                                    <UiTH className="w-10"> </UiTH>
                                                    <UiTH>Name</UiTH>
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
                                                                    type="checkbox"
                                                                    checked={checked}
                                                                    onChange={(e) => toggleFeesMaster(m.id, e.target.checked)}
                                                                />
                                                            </UiTD>
                                                            <UiTD>{m?.type?.name || optionLabel(m)}</UiTD>
                                                            <UiTD className="text-right">{m?.amount ?? '-'}</UiTD>
                                                        </UiTR>
                                                    );
                                                })}
                                            </UiTBody>
                                        </UiTable>
                                    </div>
                                ) : null}
                            </div>
                            <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                                <p className="mb-2 text-sm font-semibold text-gray-700">
                                    Students ({selectedStudentCount} selected)
                                </p>
                                {studentsLoading ? <p className="text-sm text-gray-500">Loading students…</p> : null}
                                {!studentsLoading && !form.class ? (
                                    <p className="text-sm text-gray-500">Select a class to load students.</p>
                                ) : null}
                                {!studentsLoading && form.class && studentRows.length === 0 ? (
                                    <p className="text-sm text-gray-500">No students found for this class.</p>
                                ) : null}
                                {!studentsLoading && studentRows.length > 0 ? (
                                    <div className={`${studentsTableClass()} max-h-[28rem]`}>
                                        <UiTable>
                                            <UiTHead className="sticky top-0 z-10 bg-gray-50">
                                                <UiHeadRow>
                                                    <UiTH>
                                                        <input
                                                            type="checkbox"
                                                            checked={visibleAllSelected}
                                                            onChange={(e) => toggleAllVisibleStudents(e.target.checked)}
                                                            title="Select all visible"
                                                        />
                                                    </UiTH>
                                                    <UiTH>Admission</UiTH>
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
                                                    const name = st
                                                        ? `${st.first_name || ''} ${st.last_name || ''}`.trim()
                                                        : `Student #${sid}`;
                                                    return (
                                                        <UiTR key={`${row.id}-${sid}`}>
                                                            <UiTD>
                                                                <input
                                                                    type="checkbox"
                                                                    checked={checked}
                                                                    onChange={(e) => toggleStudent(sid, e.target.checked)}
                                                                />
                                                            </UiTD>
                                                            <UiTD>{st?.admission_no ?? '-'}</UiTD>
                                                            <UiTD>{name}</UiTD>
                                                            <UiTD>{row?.class?.name ?? '-'}</UiTD>
                                                            <UiTD>{st?.parent?.guardian_name ?? '-'}</UiTD>
                                                            <UiTD>{st?.mobile ?? '-'}</UiTD>
                                                        </UiTR>
                                                    );
                                                })}
                                            </UiTBody>
                                        </UiTable>
                                    </div>
                                ) : null}
                            </div>
                        </div>
                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-3">
                            <Link
                                to="/fees/assignments"
                                className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                disabled={saving}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60"
                            >
                                {saving ? 'Saving...' : edit ? 'Update' : 'Create'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </div>
        </Layout>
    );
}

