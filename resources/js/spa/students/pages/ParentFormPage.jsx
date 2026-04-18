import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

export function ParentFormPage({ edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    useEffect(() => {
        const url = edit ? `/parent/edit/${id}` : '/parent/create';
        setLoading(true);
        axios.get(url, { headers: xhrJson }).then((r) => {
            if (edit) setForm(r.data?.data || {});
        }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.')).finally(() => setLoading(false));
    }, [edit, id]);
    const submit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            if (edit) await axios.put(`/parent/update/${id}`, form, { headers: xhrJson });
            else await axios.post('/parent/store', form, { headers: xhrJson });
            nav('/parents');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };
    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{edit ? 'Edit Parent' : 'Create Parent'}</h1>
                        <Link to="/parents" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Back
                        </Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading parent form..." /> : null}
                {!loading ? <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2">
                    <div className="space-y-1">
                        <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">First Name</label>
                        <input className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="First name" value={form.first_name || ''} onChange={(e) => setForm({ ...form, first_name: e.target.value })} />
                    </div>
                    <div className="space-y-1">
                        <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">Last Name</label>
                        <input className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Last name" value={form.last_name || ''} onChange={(e) => setForm({ ...form, last_name: e.target.value })} />
                    </div>
                    <div className="space-y-1 md:col-span-2">
                        <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">Phone</label>
                        <input className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Phone" value={form.phone || ''} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                    </div>
                    <div className="md:col-span-2 flex items-center justify-end gap-2 border-t border-gray-100 pt-3">
                        <Link to="/parents" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Cancel
                        </Link>
                        <button disabled={saving} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                            {saving ? 'Saving...' : (edit ? 'Update Parent' : 'Create Parent')}
                        </button>
                    </div>
                </form> : null}
            </div>
        </AdminLayout>
    );
}

