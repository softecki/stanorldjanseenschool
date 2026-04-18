import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell } from '../../BankAccountsModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function BankAccountsFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ bank_name: '', account_name: '', account_number: '', status: 1 });
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!edit) {
            axios.get('/banksAccounts/create', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
            return;
        }
        axios
            .get(`/banksAccounts/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                setForm({
                    bank_name: r.data?.data?.bank_name ?? '',
                    account_name: r.data?.data?.account_name ?? '',
                    account_number: r.data?.data?.account_number ?? '',
                    status: r.data?.data?.status ? 1 : 0,
                });
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            if (edit) {
                await axios.put(`/banksAccounts/update/${id}`, form, { headers: xhrJson });
            } else {
                await axios.post('/banksAccounts/store', form, { headers: xhrJson });
            }
            nav('/banks-accounts');
        } catch (ex) {
            const msg = ex.response?.data?.message || ex.response?.data?.errors;
            setErr(typeof msg === 'object' ? JSON.stringify(msg) : msg || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit bank account' : 'New bank account')}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-lg gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className="text-sm font-medium text-slate-700">
                    Bank name
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.bank_name}
                        onChange={(e) => setForm({ ...form, bank_name: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Account name
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.account_name}
                        onChange={(e) => setForm({ ...form, account_name: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Account number
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.account_number}
                        onChange={(e) => setForm({ ...form, account_number: e.target.value })}
                        required
                        disabled={edit}
                    />
                </label>
                <label className="flex items-center gap-2 text-sm text-slate-700">
                    <input
                        type="checkbox"
                        checked={!!Number(form.status)}
                        onChange={(e) => setForm({ ...form, status: e.target.checked ? 1 : 0 })}
                    />
                    Active
                </label>
                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={busy}
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {busy ? 'Saving…' : 'Save'}
                    </button>
                    <Link to="/banks-accounts" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

