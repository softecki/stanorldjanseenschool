import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

export function StudentCreatePage() {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [sections, setSections] = useState([]);
    const [parents, setParents] = useState([]);
    const [form, setForm] = useState({
        admission_no: '',
        first_name: '',
        last_name: '',
        date_of_birth: '',
        admission_date: '',
        gender: '',
        category: '',
        class: '',
        section: '',
        mobile: '',
        second_mobile: '',
        email: '',
        residance_address: '',
        parent: '',
        status: '1',
    });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setLoading(true);
        axios.get('/student/create', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta(m);
                const classOptions = mappedClassOptions(m.classes || []);
                setForm((f) => ({
                    ...f,
                    category: m.categories?.[0]?.id || '',
                    gender: m.genders?.[0]?.id || '',
                    class: classOptions[0]?.id || '',
                }));
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
        axios.get('/parent', { headers: xhrJson })
            .then((r) => setParents(r.data?.data?.data || r.data?.data || []))
            .catch(() => setParents([]));
    }, []);

    useEffect(() => {
        if (!form.class) {
            setSections([]);
            return;
        }
        axios.get('/class-setup/get-sections', { headers: xhrJson, params: { id: form.class } })
            .then((r) => {
                const list = Array.isArray(r.data) ? r.data : (Array.isArray(r.data?.data) ? r.data.data : []);
                setSections(list);
                const firstId = list?.[0]?.section?.id || list?.[0]?.id || '';
                setForm((f) => ({ ...f, section: f.section || firstId }));
            })
            .catch(() => setSections([]));
    }, [form.class]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            await axios.post('/student/store', form, { headers: xhrJson });
            nav('/students');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Create failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Create Student'}</h1>
                        <Link to="/students" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Back</Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading student form..." /> : null}
                {!loading ? <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-3">
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Admission No" value={form.admission_no} onChange={(e) => setForm({ ...form, admission_no: e.target.value })} />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="First name" value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} required />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Last name" value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} required />
                    <input type="date" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.date_of_birth} onChange={(e) => setForm({ ...form, date_of_birth: e.target.value })} />
                    <input type="date" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.admission_date} onChange={(e) => setForm({ ...form, admission_date: e.target.value })} />
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.gender} onChange={(e) => setForm({ ...form, gender: e.target.value })}>
                        <option value="">Select gender</option>
                        {(meta.genders || []).map((g) => <option key={g.id} value={g.id}>{g.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })}>
                        <option value="">Select category</option>
                        {(meta.categories || []).map((c) => <option key={c.id} value={c.id}>{c.title || c.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.class} onChange={(e) => setForm({ ...form, class: e.target.value, section: '' })} required>
                        <option value="">Select class</option>
                        {mappedClassOptions(meta.classes || []).map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.section} onChange={(e) => setForm({ ...form, section: e.target.value })} required>
                        <option value="">Select section</option>
                        {sections.map((s) => <option key={s?.section?.id || s.id} value={s?.section?.id || s.id}>{s?.section?.name || s.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.parent} onChange={(e) => setForm({ ...form, parent: e.target.value })}>
                        <option value="">Select parent</option>
                        {parents.map((p) => <option key={p.id} value={p.id}>{p.name || `${p.first_name || ''} ${p.last_name || ''}`.trim() || `Parent #${p.id}`}</option>)}
                    </select>
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Parent mobile" value={form.mobile} onChange={(e) => setForm({ ...form, mobile: e.target.value })} />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Second mobile" value={form.second_mobile} onChange={(e) => setForm({ ...form, second_mobile: e.target.value })} />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-2" placeholder="Email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })}>
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                    </select>
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-3" placeholder="Residence address" value={form.residance_address} onChange={(e) => setForm({ ...form, residance_address: e.target.value })} />
                    <div className="md:col-span-3 flex justify-end gap-2 border-t border-gray-100 pt-3">
                        <Link to="/students" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</Link>
                        <button disabled={saving} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">{saving ? 'Saving...' : 'Create Student'}</button>
                    </div>
                </form> : null}
            </div>
        </AdminLayout>
    );
}

