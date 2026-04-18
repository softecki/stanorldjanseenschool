import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

export function StudentEditPage() {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({});
    const [meta, setMeta] = useState({});
    const [sections, setSections] = useState([]);
    const [parents, setParents] = useState([]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    useEffect(() => {
        setLoading(true);
        axios.get(`/student/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const payload = r.data?.data || {};
                const m = r.data?.meta || {};
                setMeta(m);
                setForm({
                    ...payload,
                    class: m?.session_class_student?.classes_id || '',
                    section: m?.session_class_student?.section_id || '',
                    parent: payload?.parent ?? payload?.parent_id ?? '',
                    status: String(payload?.status ?? '1'),
                });
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'))
            .finally(() => setLoading(false));
        axios.get('/parent', { headers: xhrJson })
            .then((r) => setParents(r.data?.data?.data || r.data?.data || []))
            .catch(() => setParents([]));
    }, [id]);

    useEffect(() => {
        if (!form.class) return;
        axios.get('/class-setup/get-sections', { headers: xhrJson, params: { id: form.class } })
            .then((r) => {
                const list = Array.isArray(r.data) ? r.data : (Array.isArray(r.data?.data) ? r.data.data : []);
                setSections(list);
            })
            .catch(() => setSections([]));
    }, [form.class]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            await axios.put('/student/update', { ...form, id }, { headers: xhrJson });
            nav('/students');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Update failed.');
        } finally {
            setSaving(false);
        }
    };
    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Edit Student'}</h1>
                        <Link to="/students" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Back</Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading student data..." /> : null}
                {!loading ? <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3">
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.admission_no || ''} onChange={(e) => setForm({ ...form, admission_no: e.target.value })} placeholder="Admission No" />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.first_name || ''} onChange={(e) => setForm({ ...form, first_name: e.target.value })} placeholder="First Name" required />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.last_name || ''} onChange={(e) => setForm({ ...form, last_name: e.target.value })} placeholder="Last Name" required />
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.class || ''} onChange={(e) => setForm({ ...form, class: e.target.value, section: '' })} required>
                        <option value="">Select class</option>
                        {mappedClassOptions(meta.classes || []).map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.section || ''} onChange={(e) => setForm({ ...form, section: e.target.value })} required>
                        <option value="">Select section</option>
                        {sections.map((s) => <option key={s?.section?.id || s.id} value={s?.section?.id || s.id}>{s?.section?.name || s.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.parent || ''} onChange={(e) => setForm({ ...form, parent: e.target.value })} required>
                        <option value="">Select parent</option>
                        {parents.map((p) => <option key={p.id} value={p.id}>{p.name || `${p.first_name || ''} ${p.last_name || ''}`.trim() || `Parent #${p.id}`}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.status || '1')} onChange={(e) => setForm({ ...form, status: e.target.value })} required>
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                    </select>
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.mobile || form.mobile_no || ''} onChange={(e) => setForm({ ...form, mobile: e.target.value })} placeholder="Mobile" />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.email || ''} onChange={(e) => setForm({ ...form, email: e.target.value })} placeholder="Email" />
                    <div className="md:col-span-3 flex justify-end gap-2 border-t border-gray-100 pt-3">
                        <Link to="/students" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</Link>
                        <button disabled={saving} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">{saving ? 'Saving...' : 'Save Changes'}</button>
                    </div>
                </form> : null}
            </div>
        </AdminLayout>
    );
}

