import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, readPaginate } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function ExamAssignCreatePage({ Layout }) {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [sections, setSections] = useState([]);
    const [classId, setClassId] = useState('');
    const [sectionIds, setSectionIds] = useState([]);
    const [subjectOptions, setSubjectOptions] = useState([]);
    const [subjectMsg, setSubjectMsg] = useState('');
    const [examTypeIds, setExamTypeIds] = useState([]);
    const [subjectIds, setSubjectIds] = useState([]);
    const [dist, setDist] = useState({});
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios.get('/exam-assign/create', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
    }, []);

    useEffect(() => {
        if (!classId) {
            setSections([]);
            setSectionIds([]);
            return;
        }
        axios.get('/exam-assign/get-sections', { headers: xhrJson, params: { id: classId } }).then((r) => {
            setSections(Array.isArray(r.data) ? r.data : r.data?.data || []);
        });
    }, [classId]);

    useEffect(() => {
        if (!classId || sectionIds.length !== 1) {
            setSubjectOptions([]);
            setSubjectIds([]);
            setDist({});
            return;
        }
        const sid = sectionIds[0];
        axios
            .get('/exam-assign/get-subjects', {
                headers: xhrJson,
                params: {
                    classes_id: classId,
                    section_id: sid,
                    sections: sectionIds,
                    form_type: 'create',
                },
            })
            .then((r) => {
                const d = r.data || {};
                setSubjectMsg(d.message || '');
                const subs = (d.subjects || []).map((x) => ({
                    id: x.subject_id || x.subject?.id,
                    name: x.subject?.name || `Subject ${x.subject_id}`,
                }));
                setSubjectOptions(subs.filter((s) => s.id));
                if (!d.section_status || !d.loop_status) {
                    setSubjectIds([]);
                }
            });
    }, [classId, sectionIds]);

    const toggleSection = (id) => {
        setSectionIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
    };

    const setDistributionRow = (subId, idx, field, val) => {
        setDist((prev) => {
            const cur = prev[subId] || { titles: ['Written'], marks: ['0'] };
            const titles = [...(cur.titles || [])];
            const marks = [...(cur.marks || [])];
            if (field === 'title') titles[idx] = val;
            else marks[idx] = val;
            return { ...prev, [subId]: { titles, marks } };
        });
    };

    const addDistRow = (subId) => {
        setDist((prev) => {
            const cur = prev[subId] || { titles: ['Written'], marks: ['0'] };
            return { ...prev, [subId]: { titles: [...cur.titles, ''], marks: [...cur.marks, '0'] } };
        });
    };

    useEffect(() => {
        setDist((prev) => {
            const next = { ...prev };
            subjectIds.forEach((sid) => {
                if (!next[sid]) next[sid] = { titles: ['Written'], marks: ['10'] };
            });
            return next;
        });
    }, [subjectIds]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const marks_distribution = {};
            subjectIds.forEach((sid) => {
                marks_distribution[sid] = dist[sid] || { titles: ['Written'], marks: ['0'] };
            });
            await axios.post(
                '/exam-assign/store',
                {
                    class: classId,
                    sections: sectionIds,
                    exam_types: examTypeIds,
                    subjects: subjectIds,
                    marks_distribution,
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

    const classRows = meta.classes || [];

    return (
        <Shell Layout={Layout} wide>
            <Link to="/examination/exam-assign" className="text-sm text-blue-600 hover:text-blue-800">
                ← Exam assign
            </Link>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Create exam assign'}</h1>
            <p className="text-sm text-amber-800">
                Select exactly one section for this workflow so subjects load correctly. You can assign multiple exam types and subjects; mark distribution is per subject.
            </p>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            {subjectMsg ? <p className="text-sm text-amber-700">{subjectMsg}</p> : null}
            <form onSubmit={submit} className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className="block text-sm font-medium text-slate-700">
                    Class
                    <select
                        className="mt-1 w-full max-w-md rounded-md border border-slate-300 px-3 py-2"
                        value={classId}
                        onChange={(e) => {
                            setClassId(e.target.value);
                            setSectionIds([]);
                        }}
                        required
                    >
                        <option value="">Select</option>
                        {classRows.map((item) => {
                            const c = item.class || item;
                            const cid = c.id || item.classes_id;
                            return (
                                <option key={cid} value={cid}>
                                    {c.name || item.name}
                                </option>
                            );
                        })}
                    </select>
                </label>
                <div>
                    <p className="text-sm font-medium text-slate-700">Sections (select one)</p>
                    <div className="mt-2 flex flex-wrap gap-3">
                        {sections.map((s) => (
                            <label key={s.id} className="flex items-center gap-2 text-sm">
                                <input type="checkbox" checked={sectionIds.includes(s.id)} onChange={() => toggleSection(s.id)} />
                                {s.name}
                            </label>
                        ))}
                    </div>
                </div>
                <label className="block text-sm font-medium text-slate-700">
                    Exam types (multi)
                    <select
                        multiple
                        className="mt-1 w-full max-w-md rounded-md border border-slate-300 px-3 py-2"
                        value={examTypeIds.map(String)}
                        onChange={(e) =>
                            setExamTypeIds(Array.from(e.target.selectedOptions).map((o) => o.value))
                        }
                        required
                        size={Math.min(8, (meta.exam_types || []).length || 4)}
                    >
                        {(meta.exam_types || []).map((et) => (
                            <option key={et.id} value={et.id}>
                                {et.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="block text-sm font-medium text-slate-700">
                    Subjects (multi)
                    <select
                        multiple
                        className="mt-1 w-full max-w-md rounded-md border border-slate-300 px-3 py-2"
                        value={subjectIds.map(String)}
                        onChange={(e) => setSubjectIds(Array.from(e.target.selectedOptions).map((o) => Number(o.value)))}
                        required
                        size={Math.min(8, subjectOptions.length || 4)}
                    >
                        {subjectOptions.map((s) => (
                            <option key={s.id} value={s.id}>
                                {s.name}
                            </option>
                        ))}
                    </select>
                </label>
                {subjectIds.map((subId) => {
                    const rows = dist[subId]?.titles?.length ? dist[subId] : { titles: ['Written'], marks: ['10'] };
                    return (
                        <div key={subId} className="rounded-lg border border-slate-100 p-4">
                            <p className="mb-2 text-sm font-semibold text-slate-800">
                                Marks — {subjectOptions.find((s) => s.id === subId)?.name || subId}
                            </p>
                            {(rows.titles || []).map((t, idx) => (
                                <div key={idx} className="mb-2 flex flex-wrap gap-2">
                                    <input
                                        className="rounded-md border border-slate-300 px-3 py-2 text-sm"
                                        placeholder="Title"
                                        value={t}
                                        onChange={(e) => setDistributionRow(subId, idx, 'title', e.target.value)}
                                    />
                                    <input
                                        className="w-24 rounded-md border border-slate-300 px-3 py-2 text-sm"
                                        placeholder="Mark"
                                        value={rows.marks?.[idx] ?? ''}
                                        onChange={(e) => setDistributionRow(subId, idx, 'mark', e.target.value)}
                                    />
                                </div>
                            ))}
                            <button type="button" className="text-sm text-blue-600 hover:text-blue-800" onClick={() => addDistRow(subId)}>
                                + Add distribution row
                            </button>
                        </div>
                    );
                })}
                <button
                    type="submit"
                    disabled={busy || sectionIds.length !== 1}
                    className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                >
                    {busy ? 'Saving…' : 'Save'}
                </button>
            </form>
        </Shell>
    );
}

