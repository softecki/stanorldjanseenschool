import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    AccountFullPageLoader,
    AccountsPageShell,
    AccountsSectionHeader,
    btnGhost,
    btnPrimary,
    inputClass,
} from '../AccountsModuleShared';

export function ProductSellPage({ Layout }) {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const today = new Date().toISOString().slice(0, 10);
    const [form, setForm] = useState({ name: '', quantity: '', date: today, receipt: '' });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        axios
            .get('/product/sell', { headers: xhrJson })
            .then((r) => setMeta(r.data?.meta || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setErr('');
        try {
            await axios.post('/product/sellout', form, { headers: xhrJson });
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
                <AccountsSectionHeader title={meta.title || 'Sell product'} subtitle="Record sold quantity and receipt details." />
                {err ? <p className="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {loading ? <AccountFullPageLoader /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="rounded-xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5">
                            <h3 className="text-sm font-semibold text-slate-900">Sale details</h3>
                            <p className="mt-1 text-xs text-slate-500">Choose an in-stock product and record the sale information.</p>
                            <div className="mt-4 grid gap-4 md:grid-cols-2">
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Product</span>
                                    <select className={`${inputClass} w-full`} value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })}>
                                        <option value="">Select product</option>
                                        {(meta.products || meta.items || []).map((product) => (
                                            <option key={product.id} value={product.item_id || product.name || product.id}>
                                                {product.item_name || product.name || product.title || `Product #${product.id}`}
                                                {product.remained != null ? ` - ${product.remained} available` : ''}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Quantity</span>
                                    <input
                                        type="number"
                                        min="1"
                                        className={`${inputClass} w-full`}
                                        placeholder="0"
                                        value={form.quantity || ''}
                                        onChange={(e) => setForm({ ...form, quantity: e.target.value })}
                                    />
                                </label>
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Date</span>
                                    <input type="date" className={`${inputClass} w-full`} value={form.date || today} onChange={(e) => setForm({ ...form, date: e.target.value })} />
                                </label>
                                <label className="space-y-1 md:col-span-1">
                                    <span className="text-xs font-medium text-slate-600">Receipt</span>
                                    <input
                                        className={`${inputClass} w-full`}
                                        placeholder="Receipt number"
                                        value={form.receipt || ''}
                                        onChange={(e) => setForm({ ...form, receipt: e.target.value })}
                                    />
                                </label>
                            </div>
                        </div>
                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link to="/product" className={btnGhost}>
                                Cancel
                            </Link>
                            <button type="submit" disabled={saving} className={btnPrimary + ' disabled:opacity-60'}>
                                {saving ? 'Saving...' : 'Save'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}
