import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, readPaginate } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function ExamAssignEditPage({ Layout }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [classId, setClassId] = useState('');
    const [sectionId, setSectionId] = useState('');
    const [examTypeId, setExamTypeId] = useState('');
    const [subjectId, setSubjectId] = useState('');
    const [titles, setTitles] = useState(['Written']);
    const [marks, setMarks] = useState(['0']);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios
            .get(`/exam-assign/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const row = r.data?.data?.exam_assign;
                if (!row) {
                    setErr(r.data?.message || 'Cannot edit this assign.');
                    return;
                }
                setClassId(String(row.classes_id));
                setSectionId(String(row.section_id));
                setExamTypeId(String(row.exam_type_id));
                setSubjectId(String(row.subject_id));
                const md = row.mark_distribution || [];
                if (md.length) {
                    setTitles(md.map((m) => m.title));
                    setMarks(md.map((m) => String(m.mark)));
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            await axios.put(
                `/exam-assign/update/${id}`,
                {
                    class: classId,
                    sections: sectionId,
                    exam_types: examTypeId,
                    subjects: subjectId,
                    marks_distribution: {
                        [subjectId]: { titles, marks },
                    },
                },
                { headers: xhrJson },
            );
            nav('/examination/exam-assign');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    const classes = meta.classes || [];
    const sections = meta.sections || [];
    const subjects = meta.subjects || [];
    const examTypes = meta.exam_types || [];

    return (
        <Shell Layout={Layout} wide>
            <Link to="/examination/exam-assign" className="text-sm text-blue-600 hover:text-blue-800">
                ← Exam assign
            </Link>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Edit exam assign'}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-2xl gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className="text-sm font-medium text-slate-700">
                    Class
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={classId}
                        onChange={(e) => setClassId(e.target.value)}
                        required
                    >
                        <option value="">—</option>
                        {classes.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Section
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={sectionId}
                        onChange={(e) => setSectionId(e.target.value)}
                        required
                    >
                        <option value="">—</option>
                        {sections.map((s) => (
                            <option key={s.id} value={s.id}>
                                {s.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Exam type
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={examTypeId}
                        onChange={(e) => setExamTypeId(e.target.value)}
                        required
                    >
                        <option value="">—</option>
                        {examTypes.map((et) => (
                            <option key={et.id} value={et.id}>
                                {et.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Subject
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={subjectId}
                        onChange={(e) => setSubjectId(e.target.value)}
                        required
                    >
                        <option value="">—</option>
                        {subjects.map((sc) => {
                            const sub = sc.subject || sc;
                            const sid = sc.subject_id || sub.id;
                            return (
                                <option key={sid} value={sid}>
                                    {sub.name}
                                </option>
                            );
                        })}
                    </select>
                </label>
                <div>
                    <p className="text-sm font-medium text-slate-700">Mark distribution</p>
                    {titles.map((t, idx) => (
                        <div key={idx} className="mt-2 flex gap-2">
                            <input
                                className="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm"
                                value={t}
                                onChange={(e) => {
                                    const nt = [...titles];
                                    nt[idx] = e.target.value;
                                    setTitles(nt);
                                }}
                            />
                            <input
                                className="w-24 rounded-md border border-slate-300 px-3 py-2 text-sm"
                                value={marks[idx] || ''}
                                onChange={(e) => {
                                    const nm = [...marks];
                                    nm[idx] = e.target.value;
                                    setMarks(nm);
                                }}
                            />
                        </div>
                    ))}
                </div>
                <button type="submit" disabled={busy} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                    {busy ? 'Saving…' : 'Update'}
                </button>
            </form>
        </Shell>
    );
}

/* ——— Marks register ——— */

