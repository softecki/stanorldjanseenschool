import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader } from '../StudentModuleShared';

const field = 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm min-w-0 placeholder:text-gray-400';

export function StudentCategoryFormPage({ edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({
        name: '',
        description: '',
        shortcode: '',
        status: '1',
    });
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setErr('');
        setLoading(true);
        if (edit && id) {
            axios
                .get(`/student/category/edit/${id}`, { headers: xhrJson })
                .then((r) => {
                    setMeta(r.data?.meta || {});
                    const d = r.data?.data || {};
                    setForm({
                        name: d.name || d.title || '',
                        description: d.description || '',
                        shortcode: d.shortcode || '',
                        status: String(d.status ?? '1'),
                    });
                })
                .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load category.'))
                .finally(() => setLoading(false));
            return;
        }
        axios
            .get('/student/category/create', { headers: xhrJson })
            .then((r) => setMeta(r.data?.meta || {}))
            .catch(() => setMeta({}))
            .finally(() => setLoading(false));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        const payload = {
            name: form.name,
            description: form.description || null,
            shortcode: form.shortcode || null,
            status: form.status,
        };
        try {
            if (edit) await axios.put(`/student/category/update/${id}`, payload, { headers: xhrJson });
            else await axios.post('/student/category/store', payload, { headers: xhrJson });
            nav('/categories');
        } catch (ex) {
            const msg = ex.response?.data?.message;
            const errs = ex.response?.data?.errors;
            if (errs && typeof errs === 'object') {
                setErr(Object.values(errs).flat().join(' ') || msg || 'Failed to save.');
            } else {
                setErr(msg || 'Failed to save category.');
            }
        } finally {
            setBusy(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || (edit ? 'Edit category' : 'Create category')}</h1>
                        <Link
                            to="/categories"
                            className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Back
                        </Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text={edit ? 'Loading category…' : 'Loading form…'} /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div className="space-y-1">
                            <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">Name *</label>
                            <input
                                className={field}
                                placeholder="Category name"
                                value={form.name}
                                onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                                required
                            />
                        </div>
                        <div className="space-y-1">
                            <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">Shortcode</label>
                            <input
                                className={field}
                                placeholder="Optional code (e.g. DAY, BOARD)"
                                value={form.shortcode}
                                onChange={(e) => setForm((f) => ({ ...f, shortcode: e.target.value }))}
                            />
                        </div>
                        <div className="space-y-1">
                            <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">Description</label>
                            <textarea
                                rows={4}
                                className={field}
                                placeholder="Details for staff (optional)"
                                value={form.description}
                                onChange={(e) => setForm((f) => ({ ...f, description: e.target.value }))}
                            />
                        </div>
                        <div className="space-y-1">
                            <label className="text-xs font-semibold uppercase tracking-wide text-gray-600">Status *</label>
                            <select
                                className={field}
                                value={form.status}
                                onChange={(e) => setForm((f) => ({ ...f, status: e.target.value }))}
                                required
                            >
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                        </div>
                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link
                                to="/categories"
                                className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={busy}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60"
                            >
                                {busy ? 'Saving…' : edit ? 'Update category' : 'Create category'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </div>
        </AdminLayout>
    );
}
