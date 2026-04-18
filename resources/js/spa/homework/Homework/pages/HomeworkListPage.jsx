import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../../api/xhrJson';
import {
    IconClipboardCheck,
    UiButton,
    UiButtonLink,
    UiHeadRow,
    UiIconButton,
    UiIconButtonDelete,
    UiIconLinkEdit,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

export function HomeworkListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState({ class: '', section: '', subject: '' });
    const [students, setStudents] = useState([]);
    const [homeworkId, setHomeworkId] = useState(null);
    const [marks, setMarks] = useState({});
    const [err, setErr] = useState('');

    const load = async () => {
        try {
            const r = await axios.get('/homework', { headers: xhrJson });
            setRows(r.data?.data?.data || r.data?.data || []);
            setMeta(r.data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load.');
        }
    };

    useEffect(() => {
        load();
    }, []);

    const search = async (e) => {
        e.preventDefault();
        try {
            const r = await axios.post('/homework/search', filters, { headers: xhrJson });
            setRows(r.data?.data?.data || r.data?.data || []);
            setMeta((m) => ({ ...m, ...(r.data?.meta || {}) }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Search failed.');
        }
    };

    const remove = async (id) => {
        if (!window.confirm('Delete this homework?')) return;
        try {
            await axios.delete(`/homework/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    const openEvaluation = async (id) => {
        setHomeworkId(id);
        try {
            const r = await axios.post('/homework/students', { homework_id: id }, { headers: xhrJson });
            const list = r.data?.students || [];
            setStudents(list);
            const pre = {};
            list.forEach((s) => {
                const hw = s.homework_student || s.homeworkStudent;
                pre[s.student_id] = hw?.marks ?? '';
            });
            setMarks(pre);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load students.');
        }
    };

    const submitEvaluation = async () => {
        const ids = students.map((s) => s.student_id);
        const vals = ids.map((id) => marks[id] ?? '');
        try {
            await axios.post(
                '/homework/evaluation/submit',
                { homework_id: homeworkId, students: ids, marks: vals },
                { headers: xhrJson },
            );
            setHomeworkId(null);
            setStudents([]);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Evaluation failed.');
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-4 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Homework'}</h1>
                    <UiButtonLink to="/homework/create">Create</UiButtonLink>
                </div>
                <form onSubmit={search} className="grid gap-2 rounded-xl border border-gray-200 bg-white p-4 md:grid-cols-4">
                    <select
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        value={filters.class}
                        onChange={(e) => setFilters({ ...filters, class: e.target.value })}
                    >
                        <option value="">Class</option>
                        {(meta.classes || []).map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                    <input
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        placeholder="Section id"
                        value={filters.section}
                        onChange={(e) => setFilters({ ...filters, section: e.target.value })}
                    />
                    <input
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        placeholder="Subject id"
                        value={filters.subject}
                        onChange={(e) => setFilters({ ...filters, subject: e.target.value })}
                    />
                    <UiButton type="submit" variant="secondary" className="bg-gray-800 text-white hover:bg-gray-900">
                        Search
                    </UiButton>
                </form>
                {err ? <p className="text-sm text-red-600">{err}</p> : null}
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>Class/Section</UiTH>
                                <UiTH>Subject</UiTH>
                                <UiTH>Date</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((r) => (
                                <UiTR key={r.id}>
                                    <UiTD>
                                        {r.classes_id}/{r.section_id}
                                    </UiTD>
                                    <UiTD>{r.subject_id}</UiTD>
                                    <UiTD>{r.date}</UiTD>
                                    <UiTD className="text-right">
                                        <div className="flex items-center justify-end gap-2">
                                            <UiIconButton
                                                label="Evaluate"
                                                className="text-emerald-700 hover:bg-emerald-50"
                                                onClick={() => openEvaluation(r.id)}
                                            >
                                                <IconClipboardCheck />
                                            </UiIconButton>
                                            <UiIconLinkEdit to={`/homework/${r.id}/edit`} />
                                            <UiIconButtonDelete onClick={() => remove(r.id)} />
                                        </div>
                                    </UiTD>
                                </UiTR>
                            ))}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>

                {homeworkId ? (
                    <div className="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h2 className="mb-3 text-lg font-semibold text-gray-900">Evaluation</h2>
                        <div className="space-y-2">
                            {students.map((s) => {
                                const st = s.student || {};
                                const name =
                                    st.full_name || `${st.first_name || ''} ${st.last_name || ''}`.trim() || `Student ${s.student_id}`;
                                return (
                                    <div key={s.student_id} className="flex items-center justify-between border-b border-gray-100 py-2">
                                        <span className="text-gray-800">{name}</span>
                                        <input
                                            className="w-24 rounded-lg border border-gray-200 px-2 py-1 text-sm"
                                            value={marks[s.student_id] ?? ''}
                                            onChange={(e) => setMarks({ ...marks, [s.student_id]: e.target.value })}
                                        />
                                    </div>
                                );
                            })}
                        </div>
                        <div className="mt-3 flex gap-2">
                            <UiButton type="button" onClick={submitEvaluation}>
                                Submit
                            </UiButton>
                            <UiButton type="button" variant="secondary" onClick={() => setHomeworkId(null)}>
                                Close
                            </UiButton>
                        </div>
                    </div>
                ) : null}
            </div>
        </Layout>
    );
}
