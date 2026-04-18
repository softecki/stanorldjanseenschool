import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';

export function SmsTemplateFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ title: '', type: 'sms', sms_description: '', mail_description: '' });
    const [file, setFile] = useState(null);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!edit) {
            axios.get('/communication/template/create', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
            return;
        }
        axios
            .get(`/communication/template/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const t = r.data?.data?.template ?? r.data?.data;
                setForm({
                    title: t?.title ?? '',
                    type: t?.type ?? 'sms',
                    sms_description: t?.sms_description ?? '',
                    mail_description: t?.mail_description ?? '',
                });
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const fd = new FormData();
            fd.append('title', form.title);
            fd.append('type', form.type);
            if (form.type === 'sms') fd.append('sms_description', form.sms_description);
            else {
                fd.append('mail_description', form.mail_description);
                if (file) fd.append('attachment', file);
            }
            if (edit) {
                fd.append('_method', 'PUT');
                await axios.post(`/communication/template/update/${id}`, fd, {
                    headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
                });
            } else {
                await axios.post('/communication/template/store', fd, {
                    headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
                });
            }
            nav('/communication/template');
        } catch (ex) {
            const msg = ex.response?.data?.message || ex.response?.data?.errors;
            setErr(typeof msg === 'object' ? JSON.stringify(msg) : msg || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit template' : 'Create template')}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-2xl gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className="text-sm font-medium text-slate-700">
                    Title
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.title}
                        onChange={(e) => setForm({ ...form, title: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Type
                    <select className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })}>
                        <option value="sms">SMS</option>
                        <option value="mail">Mail</option>
                    </select>
                </label>
                {form.type === 'sms' ? (
                    <label className="text-sm font-medium text-slate-700">
                        SMS body
                        <textarea
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            rows={4}
                            value={form.sms_description}
                            onChange={(e) => setForm({ ...form, sms_description: e.target.value })}
                            required
                        />
                    </label>
                ) : (
                    <>
                        <label className="text-sm font-medium text-slate-700">
                            Mail body
                            <textarea
                                className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                                rows={4}
                                value={form.mail_description}
                                onChange={(e) => setForm({ ...form, mail_description: e.target.value })}
                                required
                            />
                        </label>
                        <label className="text-sm font-medium text-slate-700">
                            Attachment
                            <input type="file" className="mt-1 w-full text-sm" onChange={(e) => setFile(e.target.files?.[0] || null)} />
                        </label>
                    </>
                )}
                <div className="flex gap-2">
                    <button type="submit" disabled={busy} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        {busy ? 'Saving…' : 'Save'}
                    </button>
                    <Link to="/communication/template" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

/* --- SMS / Mail log --- */

