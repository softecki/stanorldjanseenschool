import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { Shell, readPaginate } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import {
    UiActionGroup,
    UiButton,
    UiButtonLink,
    UiHeadRow,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

export function ExamAssignListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [page, setPage] = useState(1);
    const [last, setLast] = useState(1);
    const [err, setErr] = useState('');
    const [filters, setFilters] = useState({ class: '', section: '', exam_type: '', subject: '' });

    const loadInitial = () =>
        axios.get('/exam-assign', { headers: xhrJson, params: { page: 1 } }).then((r) => {
            const { rows: list, page: cur, last: lst } = readPaginate(r);
            setRows(list);
            setPage(cur);
            setLast(lst);
            setMeta(r.data?.meta ?? {});
        });

    useEffect(() => {
        loadInitial().catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, []);

    const search = async (e, p = 1) => {
        e?.preventDefault();
        setErr('');
        try {
            const r = await axios.post('/exam-assign/search', { ...filters, page: p }, { headers: xhrJson });
            const { rows: list, page: cur, last: lst } = readPaginate(r);
            setRows(list);
            setPage(cur);
            setLast(lst);
            setMeta((m) => ({ ...m, ...(r.data?.meta || {}) }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Search failed.');
        }
    };

    const remove = async (id) => {
        if (!window.confirm('Delete this exam assign?')) return;
        try {
            await axios.delete(`/exam-assign/delete/${id}`, { headers: xhrJson });
            await loadInitial();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    const subjectArr = meta.subjectArr || {};
    const sectionArr = meta.sectionArr || {};
    const examArr = meta.examArr || {};

    return (
        <Shell Layout={Layout} wide>
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Exam assign'}</h1>
                <div className="flex flex-wrap gap-2">
                    <UiButtonLink variant="secondary" to="/examination">
                        Back
                    </UiButtonLink>
                    <UiButtonLink to="/examination/exam-assign/create">Create</UiButtonLink>
                </div>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={search} className="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-5">
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
                    placeholder="Exam type id"
                    value={filters.exam_type}
                    onChange={(e) => setFilters({ ...filters, exam_type: e.target.value })}
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
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>ID</UiTH>
                            <UiTH>Class / Section</UiTH>
                            <UiTH>Exam / Subject</UiTH>
                            <UiTH>Total</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD>{row.id}</UiTD>
                                    <UiTD>
                                        {row.classes_id} / {sectionArr[row.section_id] || row.section_id}
                                    </UiTD>
                                    <UiTD>
                                        {examArr[row.exam_type_id] || row.exam_type_id} / {subjectArr[row.subject_id] || row.subject_id}
                                    </UiTD>
                                    <UiTD>{row.total_mark}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup editTo={`/examination/exam-assign/${row.id}/edit`} onDelete={() => remove(row.id)} />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={5} message="No rows." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
            {last > 1 ? (
                <UiPager page={page} lastPage={last} onPrev={(e) => search(e, page - 1)} onNext={(e) => search(e, page + 1)} />
            ) : null}
        </Shell>
    );
}

