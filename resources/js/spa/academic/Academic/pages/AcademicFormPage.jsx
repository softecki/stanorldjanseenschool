import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { FullPageLoader, Panel, firstValue, normalizeRows, optionFrom } from '../../AcademicModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function AcademicFormPage({ Layout, titleCreate, titleEdit, loadEndpoint, storeEndpoint, updateEndpoint, backTo }) {
    const { id } = useParams();
    const edit = Boolean(id);
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ name: '', title: '', code: '', status: '1', type: '1', start_time: '', end_time: '' });
    const [sectionIds, setSectionIds] = useState([]);
    const [subjectRows, setSubjectRows] = useState([{ subject: '', teacher: '' }]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const isClassSetup = loadEndpoint === '/class-setup';
    const isSubjectAssign = loadEndpoint === '/assign-subject';
    const isClassOrSection = loadEndpoint === '/classes' || loadEndpoint === '/section';

    const normalizeStatusForForm = (s) => {
        if (s == null || s === '') return '1';
        const n = Number(s);
        if (n === 2) return '0';
        if (n === 0) return '0';
        if (n === 1) return '1';
        return String(s);
    };

    useEffect(() => {
        const url = edit ? `${loadEndpoint}/edit/${id}` : `${loadEndpoint}/create`;
        setLoading(true);
        axios.get(url, { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta(m);
                if (edit) {
                    const d = r.data?.data || {};
                    setForm((prev) => {
                        const next = { ...prev, ...d, name: d.name ?? d.title ?? prev.name ?? '' };
                        return {
                            ...next,
                            status: normalizeStatusForForm(next.status),
                        };
                    });
                    if (isClassSetup) setSectionIds(m.class_setup_sections || []);
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
    }, [edit, id, loadEndpoint]);

    useEffect(() => {
        if (!isSubjectAssign) return;
        const loadRows = async () => {
            try {
                if (edit && id) {
                    const r = await axios.get('/assign-subject/show', { headers: xhrJson, params: { id } });
                    const rows = (r.data?.data || []).map((x) => ({ subject: x.subject_id, teacher: x.teacher_id }));
                    setSubjectRows(rows.length ? rows : [{ subject: '', teacher: '' }]);
                } else {
                    const r = await axios.get('/assign-subject/add-subject-teacher', { headers: xhrJson, params: { counter: 1 } });
                    setMeta((m) => ({ ...m, ...(r.data?.meta || {}) }));
                    setSubjectRows([{ subject: '', teacher: '' }]);
                }
            } catch {
                setSubjectRows([{ subject: '', teacher: '' }]);
            }
        };
        loadRows();
    }, [isSubjectAssign, edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            const payload = { ...form };
            if (isClassOrSection) {
                delete payload.code;
                delete payload.type;
                payload.name = String(form.name || form.title || '').trim();
                const s = Number(form.status);
                payload.status = s === 2 ? 0 : s === 0 || s === 1 ? s : 1;
            }
            if (isClassSetup) payload.sections = sectionIds;
            if (isSubjectAssign) {
                payload.subjects = subjectRows.map((r) => r.subject).filter(Boolean);
                payload.teachers = subjectRows.map((r) => r.teacher).filter(Boolean);
            }
            if (edit) await axios.put(`${updateEndpoint}/${id}`, payload, { headers: xhrJson });
            else await axios.post(`${storeEndpoint}`, payload, { headers: xhrJson });
            nav(backTo);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Panel Layout={Layout} title={edit ? titleEdit : titleCreate}>
            {err ? <p className="mb-2 text-sm text-red-600">{err}</p> : null}
            {loading ? <FullPageLoader text="Loading form..." /> : null}
            {!loading ? <form onSubmit={submit} className="grid gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2">
                <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-2" placeholder="Name" value={form.name || form.title || ''} onChange={(e) => setForm({ ...form, name: e.target.value, title: e.target.value })} />
                {isClassOrSection ? null : (
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Code" value={form.code || ''} onChange={(e) => setForm({ ...form, code: e.target.value })} />
                )}
                <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.status ?? '1')} onChange={(e) => setForm({ ...form, status: e.target.value })}>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                {(meta.classes || []).length ? (
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.classes || form.class || ''} onChange={(e) => setForm({ ...form, classes: e.target.value, class: e.target.value })}>
                        <option value="">Select class</option>
                        {(meta.classes || []).map((i) => { const o = optionFrom(i); return <option key={o.id} value={o.id}>{o.name}</option>; })}
                    </select>
                ) : null}
                {(meta.section || meta.sections || []).length ? (
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.section || form.sections || ''} onChange={(e) => setForm({ ...form, section: e.target.value, sections: e.target.value })}>
                        <option value="">Select section</option>
                        {(meta.section || meta.sections || []).map((i) => { const o = optionFrom(i); return <option key={o.id} value={o.id}>{o.name}</option>; })}
                    </select>
                ) : null}
                {isClassSetup && (meta.section || []).length ? (
                    <div className="md:col-span-2 rounded-lg border border-gray-200 p-3">
                        <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-600">Sections</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            {(meta.section || []).map((s) => {
                                const opt = optionFrom(s);
                                const checked = sectionIds.includes(opt.id);
                                return (
                                    <label key={opt.id} className="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input
                                            type="checkbox"
                                            checked={checked}
                                            onChange={(e) => {
                                                const on = e.target.checked;
                                                setSectionIds((prev) => on ? [...new Set([...prev, opt.id])] : prev.filter((x) => x !== opt.id));
                                            }}
                                        />
                                        {opt.name}
                                    </label>
                                );
                            })}
                        </div>
                    </div>
                ) : null}
                {(meta.subject || meta.subjects || []).length ? (
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.subject || ''} onChange={(e) => setForm({ ...form, subject: e.target.value })}>
                        <option value="">Select subject</option>
                        {(meta.subject || meta.subjects || []).map((i) => { const o = optionFrom(i); return <option key={o.id} value={o.id}>{o.name}</option>; })}
                    </select>
                ) : null}
                {(meta.teachers || []).length ? (
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.teacher || ''} onChange={(e) => setForm({ ...form, teacher: e.target.value })}>
                        <option value="">Select teacher</option>
                        {(meta.teachers || []).map((i) => { const o = optionFrom(i); return <option key={o.id} value={o.id}>{o.name}</option>; })}
                    </select>
                ) : null}
                {String(loadEndpoint).includes('/time/schedule') ? (
                    <>
                        <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.type || '1')} onChange={(e) => setForm({ ...form, type: e.target.value })}>
                            <option value="1">Class</option>
                            <option value="2">Exam</option>
                        </select>
                        <input type="time" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.start_time || ''} onChange={(e) => setForm({ ...form, start_time: e.target.value })} />
                        <input type="time" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.end_time || ''} onChange={(e) => setForm({ ...form, end_time: e.target.value })} />
                    </>
                ) : null}
                {isSubjectAssign ? (
                    <div className="md:col-span-2 rounded-lg border border-gray-200 p-3">
                        <div className="mb-2 flex items-center justify-between">
                            <p className="text-xs font-semibold uppercase tracking-wide text-gray-600">Subject and Teacher</p>
                            <button
                                type="button"
                                className="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-700 transition hover:bg-gray-50"
                                onClick={() => setSubjectRows((prev) => [...prev, { subject: '', teacher: '' }])}
                            >
                                Add Row
                            </button>
                        </div>
                        <div className="space-y-2">
                            {subjectRows.map((row, idx) => (
                                <div key={idx} className="grid gap-2 md:grid-cols-[1fr_1fr_auto]">
                                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={row.subject || ''} onChange={(e) => setSubjectRows((prev) => prev.map((r, i) => i === idx ? { ...r, subject: e.target.value } : r))}>
                                        <option value="">Select subject</option>
                                        {(meta.subjects || meta.subject || []).map((i) => { const o = optionFrom(i); return <option key={o.id} value={o.id}>{o.name}</option>; })}
                                    </select>
                                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={row.teacher || ''} onChange={(e) => setSubjectRows((prev) => prev.map((r, i) => i === idx ? { ...r, teacher: e.target.value } : r))}>
                                        <option value="">Select teacher</option>
                                        {(meta.teachers || []).map((i) => { const o = optionFrom(i); return <option key={o.id} value={o.id}>{o.name}</option>; })}
                                    </select>
                                    <button type="button" className="rounded-lg border border-gray-200 px-3 py-2 text-sm text-rose-600 transition hover:bg-rose-50" onClick={() => setSubjectRows((prev) => prev.length > 1 ? prev.filter((_, i) => i !== idx) : prev)}>
                                        Remove
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                ) : null}
                <div className="md:col-span-2 flex justify-end gap-2 border-t border-gray-100 pt-3">
                    <Link to={backTo} className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</Link>
                    <button disabled={saving} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">{saving ? 'Saving...' : (edit ? 'Update' : 'Create')}</button>
                </div>
            </form> : null}
        </Panel>
    );
}

