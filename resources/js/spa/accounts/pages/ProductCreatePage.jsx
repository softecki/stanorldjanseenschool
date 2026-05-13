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

export function ProductCreatePage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ name: '', quantity: '', price: '' });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        axios
            .get(edit ? `/product/edit/${id}` : '/product/create', { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                if (edit) {
                    const data = r.data?.data || {};
                    setForm({
                        name: data.name || '',
                        quantity: data.quantity || '',
                        remained: data.remained ?? data.quantity ?? '',
                        itemout: data.itemout || '',
                        price: data.price || '',
                    });
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
            if (edit) await axios.put(`/product/update/${id}`, form, { headers: xhrJson });
            else await axios.post('/product/store', form, { headers: xhrJson });
            nav('/product');
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
                    title={meta.title || (edit ? 'Edit product' : 'Create product')}
                    subtitle="Register inventory quantities, remaining stock, and unit price."
                />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="rounded-xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5">
                            <h3 className="text-sm font-semibold text-slate-900">Product details</h3>
                            <p className="mt-1 text-xs text-slate-500">Choose the item and set stock counts and price.</p>
                            <div className="mt-4 grid gap-4 md:grid-cols-2">
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Item</span>
                                    <select className={`${inputClass} w-full`} value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })}>
                                        <option value="">Select item</option>
                                        {(meta.items || []).map((item) => (
                                            <option key={item.id} value={item.id}>
                                                {item.name || item.title || `Item #${item.id}`}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Quantity</span>
                                    <input
                                        type="number"
                                        min="0"
                                        className={`${inputClass} w-full`}
                                        placeholder="0"
                                        value={form.quantity || ''}
                                        onChange={(e) => setForm({ ...form, quantity: e.target.value, remained: edit ? form.remained : e.target.value })}
                                    />
                                </label>
                                {edit ? (
                                    <>
                                        <label className="space-y-1 md:col-span-1">
                                            <span className="text-xs font-medium text-slate-600">Remaining</span>
                                            <input
                                                type="number"
                                                min="0"
                                                className={`${inputClass} w-full`}
                                                placeholder="0"
                                                value={form.remained || ''}
                                                onChange={(e) => setForm({ ...form, remained: e.target.value })}
                                            />
                                        </label>
                                        <label className="space-y-1 md:col-span-1">
                                            <span className="text-xs font-medium text-slate-600">Sold</span>
                                            <input
                                                type="number"
                                                min="0"
                                                className={`${inputClass} w-full`}
                                                placeholder="0"
                                                value={form.itemout || ''}
                                                onChange={(e) => setForm({ ...form, itemout: e.target.value })}
                                            />
                                        </label>
                                    </>
                                ) : null}
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Unit price</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        className={`${inputClass} w-full`}
                                        placeholder="0.00"
                                        value={form.price || ''}
                                        onChange={(e) => setForm({ ...form, price: e.target.value })}
                                    />
                                </label>
                            </div>
                        </div>
                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link to="/product" className={btnGhost}>
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

