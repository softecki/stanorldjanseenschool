import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { AdminLayout } from '../../layout/AdminLayout';
import {
    UiActionGroup,
    UiButton,
    UiHeadRow,
    UiPageLoader,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../ui/UiKit';

export function PromoteStudentsCreatePage() {
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState({
        class: '',
        section: '',
        promote_session: '',
        promote_class: '',
        promote_section: '',
    });
    const [students, setStudents] = useState([]);
    const [results, setResults] = useState({});
    const [selected, setSelected] = useState({});
    const [rolls, setRolls] = useState({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');

    const toList = (value) => {
        if (Array.isArray(value)) return value;
        if (Array.isArray(value?.data)) return value.data;
        return [];
    };

    const loadIndex = () => {
        setLoading(true);
        axios.get('/promote-students', { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                setStudents([]);
                setResults({});
            })
            .finally(() => setLoading(false));
    };

    const loadSourceSections = (classId) => {
        if (!classId) return setMeta((m) => ({ ...m, sections: [] }));
        axios.get('/class-setup/get-sections', { headers: xhrJson, params: { id: classId } })
            .then((r) => setMeta((m) => ({ ...m, sections: toList(r.data) })))
            .catch(() => setMeta((m) => ({ ...m, sections: [] })));
    };

    const loadPromoteClasses = (sessionId) => {
        if (!sessionId) return setMeta((m) => ({ ...m, promoteClasses: [], promoteSections: [] }));
        axios.get('/promote/students/get-class', { headers: xhrJson, params: { id: sessionId } })
            .then((r) => setMeta((m) => ({ ...m, promoteClasses: toList(r.data), promoteSections: [] })))
            .catch(() => setMeta((m) => ({ ...m, promoteClasses: [], promoteSections: [] })));
    };

    const loadPromoteSections = (sessionId, classId) => {
        if (!sessionId || !classId) return setMeta((m) => ({ ...m, promoteSections: [] }));
        axios.get('/promote/students/get-sections', { headers: xhrJson, params: { session: sessionId, class: classId } })
            .then((r) => setMeta((m) => ({ ...m, promoteSections: toList(r.data) })))
            .catch(() => setMeta((m) => ({ ...m, promoteSections: [] })));
    };

    useEffect(() => {
        loadIndex();
    }, []);

    const doSearch = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErr('');
        try {
            const r = await axios.post('/promote-students/search', filters, { headers: xhrJson });
            const data = r.data?.data || {};
            setStudents(data.students || []);
            setResults(data.results || {});
            setMeta(r.data?.meta || {});
            setSelected({});
            setRolls({});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search students.');
        } finally {
            setLoading(false);
        }
    };

    const selectedCount = Object.values(selected).filter(Boolean).length;

    const submitPromote = async () => {
        const chosen = students.map((s, idx) => ({ s, idx })).filter(({ s }) => selected[s?.student?.id]);
        if (!chosen.length) {
            setErr('Select at least one student to promote.');
            return;
        }
        setSaving(true);
        setErr('');
        try {
            const fd = new FormData();
            fd.append('class', filters.class);
            fd.append('section', filters.section);
            fd.append('promote_session', filters.promote_session);
            fd.append('promote_class', filters.promote_class);
            fd.append('promote_section', filters.promote_section);

            chosen.forEach(({ s, idx }) => {
                const id = s?.student?.id;
                const pass = results?.[id] === 'Pass' ? 1 : 0;
                fd.append(`students[${idx}][]`, id);
                fd.append(`result[${idx}][]`, String(pass));
                fd.append(`roll[${idx}][]`, String(rolls[id] || ''));
            });

            await axios.post('/promote-students/store', fd, { headers: xhrJson });
            await doSearch({ preventDefault() {} });
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to promote students.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Promote Students'}</h1>
                    <p className="mt-1 text-sm text-gray-500">Search students and promote selected rows to the next session/class/section.</p>
                </div>

                <form className="mb-5 grid gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-5" onSubmit={doSearch}>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={filters.class} onChange={(e) => { const v = e.target.value; setFilters((f) => ({ ...f, class: v, section: '' })); loadSourceSections(v); }}>
                        <option value="">Current Class</option>
                        {(meta.classes || []).map((c) => <option key={c?.class?.id || c.id} value={c?.class?.id || c.id}>{c?.class?.name || c.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={filters.section} onChange={(e) => setFilters((f) => ({ ...f, section: e.target.value }))}>
                        <option value="">Current Section</option>
                        {(meta.sections || []).map((s) => <option key={s?.section?.id || s.id} value={s?.section?.id || s.id}>{s?.section?.name || s.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={filters.promote_session} onChange={(e) => { const v = e.target.value; setFilters((f) => ({ ...f, promote_session: v, promote_class: '', promote_section: '' })); loadPromoteClasses(v); }}>
                        <option value="">Promote Session</option>
                        {(meta.sessions || []).map((s) => <option key={s.id} value={s.id}>{s.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={filters.promote_class} onChange={(e) => { const v = e.target.value; setFilters((f) => ({ ...f, promote_class: v, promote_section: '' })); loadPromoteSections(filters.promote_session, v); }}>
                        <option value="">Promote Class</option>
                        {(meta.promoteClasses || []).map((c) => <option key={c?.class?.id || c.id} value={c?.class?.id || c.id}>{c?.class?.name || c.name}</option>)}
                    </select>
                    <div className="flex gap-2">
                        <select className="min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm" value={filters.promote_section} onChange={(e) => setFilters((f) => ({ ...f, promote_section: e.target.value }))}>
                            <option value="">Promote Section</option>
                            {(meta.promoteSections || []).map((s) => <option key={s?.section?.id || s.id} value={s?.section?.id || s.id}>{s?.section?.name || s.name}</option>)}
                        </select>
                        <UiButton type="submit">Search</UiButton>
                    </div>
                </form>

                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading promote students…" /> : null}
                {!loading ? (
                    <UiTableWrap>
                        <UiTable>
                            <UiTHead>
                                <UiHeadRow>
                                    <UiTH>
                                        <input
                                            type="checkbox"
                                            checked={students.length > 0 && selectedCount === students.length}
                                            onChange={(e) => {
                                                const checked = e.target.checked;
                                                const next = {};
                                                students.forEach((s) => {
                                                    next[s?.student?.id] = checked;
                                                });
                                                setSelected(next);
                                            }}
                                            aria-label="Select all"
                                        />
                                    </UiTH>
                                    <UiTH>Admission No</UiTH>
                                    <UiTH>Student Name</UiTH>
                                    <UiTH>Guardian</UiTH>
                                    <UiTH>Mobile</UiTH>
                                    <UiTH>Result</UiTH>
                                    <UiTH>Roll</UiTH>
                                    <UiTH className="text-right">Actions</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {students.map((item) => {
                                    const sid = item?.student?.id;
                                    const pass = results?.[sid] === 'Pass';
                                    return (
                                        <UiTR key={sid}>
                                            <UiTD>
                                                <input
                                                    type="checkbox"
                                                    checked={!!selected[sid]}
                                                    onChange={(e) => setSelected((prev) => ({ ...prev, [sid]: e.target.checked }))}
                                                />
                                            </UiTD>
                                            <UiTD className="font-medium">{item?.student?.admission_no || '-'}</UiTD>
                                            <UiTD>{`${item?.student?.first_name || ''} ${item?.student?.last_name || ''}`.trim() || '-'}</UiTD>
                                            <UiTD>{item?.student?.parent?.guardian_name || '-'}</UiTD>
                                            <UiTD>{item?.student?.mobile_no || item?.student?.mobile || '-'}</UiTD>
                                            <UiTD>
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${pass ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}
                                                >
                                                    {pass ? 'Passed' : 'Pending/Failed'}
                                                </span>
                                            </UiTD>
                                            <UiTD>
                                                <input
                                                    type="number"
                                                    className="w-20 rounded-lg border border-gray-200 px-2 py-1.5 text-sm"
                                                    value={rolls[sid] || ''}
                                                    onChange={(e) => setRolls((prev) => ({ ...prev, [sid]: e.target.value }))}
                                                />
                                            </UiTD>
                                            <UiTD className="text-right">
                                                <UiActionGroup viewTo={`/students/${sid}`} editTo={`/students/${sid}/edit`} hideDelete />
                                            </UiTD>
                                        </UiTR>
                                    );
                                })}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                ) : null}

                {!loading && students.length ? (
                    <div className="mt-4 flex items-center justify-between">
                        <p className="text-sm text-gray-600">{selectedCount} selected</p>
                        <UiButton
                            type="button"
                            disabled={saving}
                            variant="primary"
                            className="bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500"
                            onClick={submitPromote}
                        >
                            {saving ? 'Promoting...' : 'Promote Selected'}
                        </UiButton>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}

