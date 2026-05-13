import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    ActionButtons,
    EntityListPage,
    EntityViewPage,
    FeesEntityFormPage,
    FullPageLoader,
    TransactionsListPage,
    normalizeFeesTransactionRows,
    optionLabel,
    panelTitle,
    statusChoices,
    studentsTableClass,
} from '../FeesModuleShared';

export function FeesCollectionEditPage({ Layout }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setLoading(true);
        axios.get(`/fees-collect/edit/${id}`, { headers: xhrJson }).then((r) => setForm(r.data?.data || {})).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.')).finally(() => setLoading(false));
    }, [id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            await axios.put(`/fees-collect/update/${id}`, form, { headers: xhrJson });
            nav('/collections');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Update failed.');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h1 className="text-2xl font-semibold text-gray-800">Edit Fees Collection</h1>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading collection..." /> : null}
                {!loading ? <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-2">
                    <input type="number" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Paid Amount" value={form.paid_amount || ''} onChange={(e) => setForm({ ...form, paid_amount: e.target.value })} />
                    <input type="number" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Remained Amount" value={form.remained_amount || ''} onChange={(e) => setForm({ ...form, remained_amount: e.target.value })} />
                    <div className="md:col-span-2 flex justify-end gap-2 border-t border-gray-100 pt-3">
                        <Link to="/collections" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</Link>
                        <button disabled={saving} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">{saving ? 'Saving...' : 'Update'}</button>
                    </div>
                </form> : null}
            </div>
        </Layout>
    );
}

