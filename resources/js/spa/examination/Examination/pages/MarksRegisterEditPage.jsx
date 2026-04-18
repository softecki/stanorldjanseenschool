import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom';

import { Shell } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import { UiButton, UiButtonLink, UiHeadRow, UiTable, UiTableWrap, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../../../ui/UiKit';

export function MarksRegisterEditPage({ Layout }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [classId, setClassId] = useState('');
    const [sectionId, setSectionId] = useState('');
    const [examType, setExamType] = useState('');
    const [subject, setSubject] = useState('');
    const [students, setStudents] = useState([]);
    const [titles, setTitles] = useState(['written']);
    const [marks, setMarks] = useState({});
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios
            .get(`/marks-register/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const mr = r.data?.data?.marks_register;
                const examAssign = r.data?.data?.examAssign;
                const studs = r.data?.data?.students || [];
                setClassId(String(mr?.classes_id ?? ''));
                setSectionId(String(mr?.section_id ?? ''));
                setExamType(String(mr?.exam_type_id ?? ''));
                setSubject(String(mr?.subject_id ?? ''));
                setStudents(studs);
                const md = examAssign?.mark_distribution || [];
                const tt = md.length ? md.map((x) => x.title) : ['written'];
                setTitles(tt);
                const childs = mr?.marks_register_childs || [];
                const byStu = {};
                childs.forEach((c) => {
                    if (!byStu[c.student_id]) byStu[c.student_id] = {};
                    byStu[c.student_id][c.title] = String(c.mark ?? '');
                });
                studs.forEach((row) => {
                    const sid = row.student_id || row.student?.id;
                    if (!byStu[sid]) byStu[sid] = {};
                    tt.forEach((t) => {
                        if (byStu[sid][t] === undefined) byStu[sid][t] = '';
                    });
                });
                setMarks(byStu);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [id]);

    const setMarkCell = (studentId, title, val) => {
        setMarks((prev) => ({
            ...prev,
            [studentId]: { ...prev[studentId], [title]: val },
        }));
    };

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const student_ids = students.map((row) => row.student_id || row.student?.id).filter(Boolean);
            const marksPayload = {};
            student_ids.forEach((sid) => {
                marksPayload[sid] = titles.reduce((acc, t) => {
                    acc[t] = marks[sid]?.[t] ?? '';
                    return acc;
                }, {});
            });
            await axios.put(
                `/marks-register/update/${id}`,
                {
                    class: classId,
                    section: sectionId,
                    exam_type: examType,
                    subject,
                    student_ids,
                    marks: marksPayload,
                },
                { headers: xhrJson },
            );
            nav('/examination/marks-register');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout} wide>
            <UiButtonLink variant="ghost" className="mb-2 px-0 text-sm text-blue-700" to="/examination/marks-register">
                ← Marks register
            </UiButtonLink>
            <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Edit marks register'}</h1>
            {titles.length === 0 ? <p className="text-sm text-amber-700">No exam assign / titles found — check exam assignment for this class and subject.</p> : null}
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="space-y-4">
                <div className="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-4">
                    <label className="text-sm text-gray-700">
                        Class
                        <input className="mt-1 w-full rounded-lg border border-gray-200 px-2 py-1" value={classId} onChange={(e) => setClassId(e.target.value)} />
                    </label>
                    <label className="text-sm text-gray-700">
                        Section
                        <input className="mt-1 w-full rounded-lg border border-gray-200 px-2 py-1" value={sectionId} onChange={(e) => setSectionId(e.target.value)} />
                    </label>
                    <label className="text-sm text-gray-700">
                        Exam type
                        <input className="mt-1 w-full rounded-lg border border-gray-200 px-2 py-1" value={examType} onChange={(e) => setExamType(e.target.value)} />
                    </label>
                    <label className="text-sm text-gray-700">
                        Subject
                        <input className="mt-1 w-full rounded-lg border border-gray-200 px-2 py-1" value={subject} onChange={(e) => setSubject(e.target.value)} />
                    </label>
                </div>
                <UiTableWrap className="overflow-auto">
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>Student</UiTH>
                                {titles.map((t) => (
                                    <UiTH key={t}>{t}</UiTH>
                                ))}
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {students.map((row) => {
                                const st = row.student || row;
                                const sid = row.student_id || st.id;
                                const name = st.full_name || [st.first_name, st.last_name].filter(Boolean).join(' ');
                                return (
                                    <UiTR key={sid}>
                                        <UiTD className="font-medium">{name}</UiTD>
                                        {titles.map((t) => (
                                            <UiTD key={t}>
                                                <input
                                                    className="w-20 rounded-lg border border-gray-200 px-2 py-1"
                                                    value={marks[sid]?.[t] ?? ''}
                                                    onChange={(e) => setMarkCell(sid, t, e.target.value)}
                                                />
                                            </UiTD>
                                        ))}
                                    </UiTR>
                                );
                            })}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>
                <UiButton type="submit" disabled={busy}>
                    {busy ? 'Saving…' : 'Save'}
                </UiButton>
            </form>
        </Shell>
    );
}

