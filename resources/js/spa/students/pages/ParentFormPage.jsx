import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader } from '../StudentModuleShared';

const field =
    'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm min-w-0 placeholder:text-gray-400';

export function ParentFormPage({ edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({
        guardian_name: '',
        guardian_mobile: '',
        guardian_email: '',
        guardian_profession: '',
        guardian_relation: '',
        guardian_address: '',
        father_name: '',
        father_mobile: '',
        father_profession: '',
        father_nationality: '',
        mother_name: '',
        mother_mobile: '',
        mother_profession: '',
        status: '1',
    });
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        const url = edit ? `/parent/edit/${id}` : '/parent/create';
        setLoading(true);
        axios
            .get(url, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                if (edit) {
                    const p = r.data?.data || {};
                    setForm({
                        guardian_name: p.guardian_name ?? '',
                        guardian_mobile: p.guardian_mobile ?? '',
                        guardian_email: p.guardian_email ?? '',
                        guardian_profession: p.guardian_profession ?? '',
                        guardian_relation: p.guardian_relation ?? '',
                        guardian_address: p.guardian_address ?? '',
                        father_name: p.father_name ?? '',
                        father_mobile: p.father_mobile ?? '',
                        father_profession: p.father_profession ?? '',
                        father_nationality: p.father_nationality ?? '',
                        mother_name: p.mother_name ?? '',
                        mother_mobile: p.mother_mobile ?? '',
                        mother_profession: p.mother_profession ?? '',
                        status: String(p.status ?? '1'),
                    });
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'))
            .finally(() => setLoading(false));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            const payload = {
                guardian_name: form.guardian_name,
                guardian_mobile: form.guardian_mobile,
                guardian_email: form.guardian_email || null,
                guardian_profession: form.guardian_profession || null,
                guardian_relation: form.guardian_relation || null,
                guardian_address: form.guardian_address || null,
                father_name: form.father_name || null,
                father_mobile: form.father_mobile || null,
                father_profession: form.father_profession || null,
                father_nationality: form.father_nationality || null,
                mother_name: form.mother_name || null,
                mother_mobile: form.mother_mobile || null,
                mother_profession: form.mother_profession || null,
                status: form.status,
            };
            if (edit) {
                await axios.put(`/parent/update/${id}`, payload, { headers: xhrJson });
            } else {
                await axios.post('/parent/store', payload, { headers: xhrJson });
            }
            nav('/parents');
        } catch (ex) {
            const msg = ex.response?.data?.message;
            const errs = ex.response?.data?.errors;
            if (errs && typeof errs === 'object') {
                setErr(Object.values(errs).flat().join(' ') || msg || 'Save failed.');
            } else {
                setErr(msg || 'Save failed.');
            }
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || (edit ? 'Edit parent' : 'Create parent')}</h1>
                        <Link
                            to="/parents"
                            className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Back
                        </Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text={edit ? 'Loading parent…' : 'Loading form…'} /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-6">
                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Primary guardian</h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    Guardian name *
                                    <input
                                        className={field}
                                        value={form.guardian_name}
                                        onChange={(e) => setForm({ ...form, guardian_name: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Guardian mobile *
                                    <input
                                        className={field}
                                        value={form.guardian_mobile}
                                        onChange={(e) => setForm({ ...form, guardian_mobile: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Guardian email
                                    <input
                                        type="email"
                                        className={field}
                                        value={form.guardian_email}
                                        onChange={(e) => setForm({ ...form, guardian_email: e.target.value })}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Profession
                                    <input className={field} value={form.guardian_profession} onChange={(e) => setForm({ ...form, guardian_profession: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Relation to student
                                    <input className={field} value={form.guardian_relation} onChange={(e) => setForm({ ...form, guardian_relation: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    Address
                                    <textarea
                                        rows={2}
                                        className={field}
                                        value={form.guardian_address}
                                        onChange={(e) => setForm({ ...form, guardian_address: e.target.value })}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Status *
                                    <select className={field} value={form.status} onChange={(e) => setForm({ ...form, status: e.target.value })} required>
                                        <option value="1">Active</option>
                                        <option value="2">Inactive</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Father</h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Name
                                    <input className={field} value={form.father_name} onChange={(e) => setForm({ ...form, father_name: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Mobile
                                    <input className={field} value={form.father_mobile} onChange={(e) => setForm({ ...form, father_mobile: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Profession
                                    <input className={field} value={form.father_profession} onChange={(e) => setForm({ ...form, father_profession: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Nationality
                                    <input className={field} value={form.father_nationality} onChange={(e) => setForm({ ...form, father_nationality: e.target.value })} />
                                </label>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Mother</h2>
                            <div className="grid gap-4 md:grid-cols-2">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Name
                                    <input className={field} value={form.mother_name} onChange={(e) => setForm({ ...form, mother_name: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    Mobile
                                    <input className={field} value={form.mother_mobile} onChange={(e) => setForm({ ...form, mother_mobile: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    Profession
                                    <input className={field} value={form.mother_profession} onChange={(e) => setForm({ ...form, mother_profession: e.target.value })} />
                                </label>
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link to="/parents" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={saving}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60"
                            >
                                {saving ? 'Saving…' : edit ? 'Update parent' : 'Create parent'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </div>
        </AdminLayout>
    );
}
