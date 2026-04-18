import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';

export function SmsMailCreatePage({ Layout }) {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [roles, setRoles] = useState([]);
    const [form, setForm] = useState({
        title: 'Bulk SMS',
        type: 'sms',
        user_type: 'role',
        role_ids: '',
        sms_description: '',
    });
    const [excel, setExcel] = useState(null);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios.get('/communication/smsmail/create', { headers: xhrJson }).then((r) => {
            setMeta(r.data?.meta || {});
            setRoles(r.data?.meta?.roles || []);
        });
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const fd = new FormData();
            fd.append('title', form.title);
            fd.append('type', form.type);
            fd.append('user_type', form.user_type);
            fd.append('role_ids', form.role_ids);
            fd.append('sms_description', form.sms_description);
            if (excel) fd.append('excel_file', excel);
            await axios.post('/communication/smsmail/store', fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
            nav('/communication/smsmail');
        } catch (ex) {
            const msg = ex.response?.data?.message || ex.response?.data?.errors || ex.response?.data;
            setErr(typeof msg === 'object' ? JSON.stringify(msg) : msg || 'Send failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Send SMS'}</h1>
            <p className="text-sm text-slate-500">
                Upload Excel (optional) for personalized SMS, or use role targeting. For template, use{' '}
                <a href="/communication/smsmail/download-template" className="text-blue-600 hover:underline">
                    download template
                </a>
                .
            </p>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-2xl gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className="text-sm font-medium text-slate-700">
                    Title
                    <input className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Role (comma-separated IDs allowed)
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.role_ids}
                        onChange={(e) => setForm({ ...form, role_ids: e.target.value })}
                        placeholder="e.g. 1"
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    SMS message ({'{name}'}, {'{balance}'})
                    <textarea
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        rows={4}
                        value={form.sms_description}
                        onChange={(e) => setForm({ ...form, sms_description: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Excel file (optional)
                    <input type="file" accept=".xlsx,.xls,.csv" className="mt-1 w-full text-sm" onChange={(e) => setExcel(e.target.files?.[0] || null)} />
                </label>
                <p className="text-xs text-slate-500">Available roles: {roles.map((r) => `${r.name} (${r.id})`).join(', ') || '—'}</p>
                <div className="flex gap-2">
                    <button type="submit" disabled={busy} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        {busy ? 'Sending…' : 'Send'}
                    </button>
                    <Link to="/communication/smsmail" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

