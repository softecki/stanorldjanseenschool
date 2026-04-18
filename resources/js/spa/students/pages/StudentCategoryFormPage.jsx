import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

export function StudentCategoryFormPage({ edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({ title: '' });
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);
    const [loading, setLoading] = useState(edit);

    useEffect(() => {
        if (!edit || !id) return;
        setLoading(true);
        axios.get(`/student/category/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const d = r.data?.data || r.data || {};
                setForm({ title: d.title || d.name || '' });
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load category.'))
            .finally(() => setLoading(false));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            if (edit) await axios.put(`/student/category/update/${id}`, form, { headers: xhrJson });
            else await axios.post('/student/category/store', form, { headers: xhrJson });
            nav('/categories');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to save category.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{edit ? 'Edit Category' : 'Create Category'}</h1>
                        <Link to="/categories" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Back</Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading category form..." /> : null}
                {!loading ? <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div className="space-y-1">
                        <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">Category Title</label>
                        <input
                            className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"
                            placeholder="Enter category title"
                            value={form.title}
                            onChange={(e) => setForm({ title: e.target.value })}
                            required
                        />
                    </div>
                    <div className="flex justify-end gap-2 border-t border-gray-100 pt-3">
                        <Link to="/categories" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</Link>
                        <button disabled={busy} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                            {busy ? 'Saving...' : (edit ? 'Update Category' : 'Create Category')}
                        </button>
                    </div>
                </form> : null}
            </div>
        </AdminLayout>
    );
}

