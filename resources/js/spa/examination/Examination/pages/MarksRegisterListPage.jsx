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

export function MarksRegisterListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [page, setPage] = useState(1);
    const [last, setLast] = useState(1);
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const [filters, setFilters] = useState({ class: '', section: '', exam_type: '', subject: '' });
    const [sections, setSections] = useState([]);

    const applyPaginator = (r) => {
        const { rows: list, page: cur, last: lst } = readPaginate(r);
        setRows(list);
        setPage(cur);
        setLast(lst);
        setMeta((m) => ({ ...m, ...(r.data?.meta || {}) }));
    };

    useEffect(() => {
        axios
            .get('/marks-register', { headers: xhrJson, params: { page: 1 } })
            .then(applyPaginator)
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, []);

    useEffect(() => {
        if (!filters.class) {
            setSections([]);
            return;
        }
        axios.get('/exam-assign/get-sections', { headers: xhrJson, params: { id: filters.class } }).then((r) => {
            setSections(Array.isArray(r.data) ? r.data : r.data?.data || []);
        });
    }, [filters.class]);

    const search = async (e, p = 1) => {
        e?.preventDefault();
        setErr('');
        try {
            const r = await axios.post('/marks-register/search', { ...filters, page: p }, { headers: xhrJson });
            applyPaginator(r);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Search failed.');
        }
    };

    const runTerminal = async () => {
        setMsg('');
        try {
            const r = await axios.get('/marks-register/terminal', { headers: xhrJson });
            setMsg(r.data?.message || 'Done.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Terminal action failed.');
        }
    };

    const remove = async (id) => {
        if (!window.confirm('Delete this marks register?')) return;
        try {
            await axios.delete(`/marks-register/delete/${id}`, { headers: xhrJson });
            await axios.get('/marks-register', { headers: xhrJson, params: { page } }).then(applyPaginator);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    return (
        <Shell Layout={Layout} wide>
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Marks register'}</h1>
                <div className="flex flex-wrap gap-2">
                    <UiButtonLink variant="secondary" to="/examination">
                        Back
                    </UiButtonLink>
                    <UiButton type="button" variant="secondary" className="border-amber-500 text-amber-900 hover:bg-amber-50" onClick={runTerminal}>
                        Compute terminal results
                    </UiButton>
                    <UiButtonLink to="/examination/marks-register/create">Create</UiButtonLink>
                </div>
            </div>
            {msg ? <p className="text-sm text-emerald-700">{msg}</p> : null}
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={search} className="grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-6">
                <select
                    className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                    value={filters.class}
                    onChange={(e) => setFilters({ ...filters, class: e.target.value, section: '' })}
                >
                    <option value="">Class</option>
                    {(meta.classes || []).map((c) => (
                        <option key={c.id} value={c.id}>
                            {c.name}
                        </option>
                    ))}
                </select>
                <select
                    className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                    value={filters.section}
                    onChange={(e) => setFilters({ ...filters, section: e.target.value })}
                >
                    <option value="">Section</option>
                    {sections.map((s) => (
                        <option key={s.id} value={s.id}>
                            {s.name}
                        </option>
                    ))}
                </select>
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
                            <UiTH>Class</UiTH>
                            <UiTH>Section</UiTH>
                            <UiTH>Exam</UiTH>
                            <UiTH>Subject</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD>{row.id}</UiTD>
                                    <UiTD>{row.classes_id}</UiTD>
                                    <UiTD>{row.section_id}</UiTD>
                                    <UiTD>{row.exam_type_id}</UiTD>
                                    <UiTD>{row.subject_id}</UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup
                                            viewTo={`/examination/marks-register/${row.id}/view`}
                                            editTo={`/examination/marks-register/${row.id}/edit`}
                                            onDelete={() => remove(row.id)}
                                        />
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={6} message="No rows." />
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

