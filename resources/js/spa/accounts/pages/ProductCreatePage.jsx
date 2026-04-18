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

export function ProductCreatePage({ Layout }) {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ name: '', quantity: '', price: '' });
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        axios
            .get('/product/create', { headers: xhrJson })
            .then((r) => setMeta(r.data?.meta || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            await axios.post('/product/store', form, { headers: xhrJson });
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
                <AccountsSectionHeader title={meta.title || 'Create product'} />
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <input className={inputClass} placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                        <input className={inputClass} placeholder="Quantity" value={form.quantity || ''} onChange={(e) => setForm({ ...form, quantity: e.target.value })} />
                        <input className={inputClass} placeholder="Price" value={form.price || ''} onChange={(e) => setForm({ ...form, price: e.target.value })} />
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

