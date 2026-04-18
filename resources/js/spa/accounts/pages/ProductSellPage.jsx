import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
    AccountCard,
    AccountEmptyState,
    AccountPageHeader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import {
    AccountFullPageLoader,
    AccountsCrudListPage,
    AccountsHomePageComponent,
    AccountsPageShell,
    AccountsSectionHeader,
    AccountsSimpleFormPage,
    btnGhost,
    btnPrimary,
    extractRows,
    inputClass,
} from '../AccountsModuleShared';

export function ProductSellPage({ Layout }) {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ name: '', quantity: '', date: '', receipt: '' });
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
        try {
            await axios.post('/product/sellout', form, { headers: xhrJson });
            nav('/accounts/product');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader title={meta.title || 'Sell product'} />
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <select className={inputClass} value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })}>
                            <option value="">Select item</option>
                            {(meta.items || []).map((i) => (
                                <option key={i.id} value={i.id}>
                                    {i.name || i.title}
                                </option>
                            ))}
                        </select>
                        <input className={inputClass} placeholder="Quantity" value={form.quantity || ''} onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
                        <input type="date" className={inputClass} placeholder="Date" value={form.date || ''} onChange={(e) => setForm({ ...form, date: e.target.value })} />
                        <input className={inputClass} placeholder="Receipt" value={form.receipt || ''} onChange={(e) => setForm({ ...form, receipt: e.target.value })} />
                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link to="/accounts/product" className={btnGhost}>
                                Cancel
                            </Link>
                            <button type="submit" disabled={saving} className={btnPrimary + ' disabled:opacity-60'}>
                                {saving ? 'Saving…' : 'Save'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

function formatMoney(n) {
    const v = Number(n);
    if (Number.isNaN(v)) return '—';
    return v.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** Financial summary + recent activity (JSON from `/accounting/dashboard`). */
