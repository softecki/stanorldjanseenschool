import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    AccountFullPageLoader,
    AccountsPageShell,
    AccountsSectionHeader,
    btnGhost,
    btnPrimary,
    inputClass,
} from '../AccountsModuleShared';

export function ItemCreatePage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ name: '', description: '' });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        axios
            .get(edit ? `/item/edit/${id}` : '/item/create', { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                if (edit) {
                    const data = r.data?.data || {};
                    setForm({ name: data.name || '', description: data.description || '' });
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setErr('');
        try {
            if (edit) await axios.put(`/item/update/${id}`, form, { headers: xhrJson });
            else await axios.post('/item/store', form, { headers: xhrJson });
            nav('/item');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader
                    title={meta.title || (edit ? 'Edit item' : 'Create item')}
                    subtitle="Maintain item names and descriptions for product stock."
                />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="rounded-xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5">
                            <h3 className="text-sm font-semibold text-slate-900">Item details</h3>
                            <p className="mt-1 text-xs text-slate-500">Create an item before registering product stock.</p>
                            <div className="mt-4 grid gap-4 md:grid-cols-2">
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Item name</span>
                                    <input
                                        className={`${inputClass} w-full`}
                                        placeholder="e.g. Uniform, Exercise Book"
                                        value={form.name || ''}
                                        onChange={(e) => setForm({ ...form, name: e.target.value })}
                                    />
                                </label>
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Description</span>
                                    <textarea
                                        className={`${inputClass} w-full`}
                                        placeholder="Description"
                                        rows={4}
                                        value={form.description || ''}
                                        onChange={(e) => setForm({ ...form, description: e.target.value })}
                                    />
                                </label>
                            </div>
                        </div>
                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link to="/item" className={btnGhost}>
                                Cancel
                            </Link>
                            <button type="submit" disabled={saving} className={btnPrimary + ' disabled:opacity-60'}>
                                {saving ? 'Saving...' : edit ? 'Update' : 'Save'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

