import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell } from '../CommunicationModuleShared';
import { UiButton, UiInlineLoader, UiPageLoader } from '../../ui/UiKit';

function formatApiError(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Save failed.';
    if (d.errors && typeof d.errors === 'object') {
        const parts = [];
        Object.keys(d.errors).forEach((k) => {
            const v = d.errors[k];
            parts.push(`${k}: ${Array.isArray(v) ? v.join(' ') : v}`);
        });
        return parts.length ? parts.join(' · ') : d.message || 'Validation failed.';
    }
    if (typeof d.message === 'string') return d.message;
    try {
        return JSON.stringify(d);
    } catch {
        return 'Save failed.';
    }
}

const field =
    'mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
const label = 'block text-sm font-medium text-slate-700';

export function SmsTemplateFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ title: '', type: 'sms', sms_description: '', mail_description: '' });
    const [file, setFile] = useState(null);
    const [existingAttachmentId, setExistingAttachmentId] = useState(null);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);
    const [pageLoading, setPageLoading] = useState(true);

    useEffect(() => {
        setPageLoading(true);
        setErr('');
        if (!edit) {
            axios
                .get('/communication/template/create', { headers: xhrJson })
                .then((r) => setMeta(r.data?.meta || {}))
                .catch((ex) => setErr(formatApiError(ex)))
                .finally(() => setPageLoading(false));
            return;
        }
        axios
            .get(`/communication/template/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const t = r.data?.data?.template ?? r.data?.data;
                setExistingAttachmentId(t?.attachment ?? null);
                setForm({
                    title: t?.title ?? '',
                    type: t?.type ?? 'sms',
                    sms_description: t?.sms_description ?? '',
                    mail_description: t?.mail_description ?? '',
                });
            })
            .catch((ex) => setErr(formatApiError(ex)))
            .finally(() => setPageLoading(false));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const fd = new FormData();
            fd.append('title', form.title);
            fd.append('type', form.type);
            if (form.type === 'sms') {
                fd.append('sms_description', form.sms_description);
            } else {
                fd.append('mail_description', form.mail_description);
                if (file) fd.append('attachment', file);
            }
            const headers = { ...xhrJson };
            if (edit) {
                fd.append('_method', 'PUT');
                await axios.post(`/communication/template/update/${id}`, fd, { headers });
            } else {
                await axios.post('/communication/template/store', fd, { headers });
            }
            nav('/communication/template');
        } catch (ex) {
            setErr(formatApiError(ex));
        } finally {
            setBusy(false);
        }
    };

    if (pageLoading) {
        return (
            <Shell Layout={Layout}>
                <UiPageLoader text={edit ? 'Loading template…' : 'Loading form…'} />
            </Shell>
        );
    }

    return (
        <Shell Layout={Layout}>
            <div className="mx-auto max-w-2xl space-y-8">
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-indigo-900 to-violet-900 px-6 py-8 text-white shadow-xl sm:px-8">
                    <div className="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200/90">Templates</p>
                            <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{meta.title || (edit ? 'Edit template' : 'Create template')}</h1>
                        </div>
                        <Link
                            to="/communication/template"
                            className="inline-flex shrink-0 items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"
                        >
                            Back to list
                        </Link>
                    </div>
                </div>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}

                <form onSubmit={submit} className="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg sm:p-8">
                    <label className={label}>
                        Title
                        <input
                            className={field}
                            value={form.title}
                            onChange={(e) => setForm({ ...form, title: e.target.value })}
                            placeholder="e.g. Fee reminder — Q1"
                            required
                        />
                    </label>
                    <label className={label}>
                        Type
                        <select className={field} value={form.type} onChange={(e) => setForm({ ...form, type: e.target.value })}>
                            <option value="sms">SMS</option>
                            <option value="mail">Mail</option>
                        </select>
                    </label>
                    {form.type === 'sms' ? (
                        <label className={label}>
                            SMS body
                            <textarea
                                className={`${field} min-h-[140px]`}
                                rows={6}
                                value={form.sms_description}
                                onChange={(e) => setForm({ ...form, sms_description: e.target.value })}
                                placeholder="Message text. Placeholders like {name} may be used where supported."
                                required
                            />
                        </label>
                    ) : (
                        <div className="space-y-6">
                            <label className={label}>
                                Mail body (HTML allowed)
                                <textarea
                                    className={`${field} min-h-[180px]`}
                                    rows={10}
                                    value={form.mail_description}
                                    onChange={(e) => setForm({ ...form, mail_description: e.target.value })}
                                    required
                                />
                            </label>
                            <div>
                                <span className={label}>Attachment (optional)</span>
                                <input
                                    type="file"
                                    className="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-xs file:font-medium file:text-white"
                                    onChange={(e) => setFile(e.target.files?.[0] || null)}
                                />
                                {edit && existingAttachmentId && !file ? (
                                    <p className="mt-2 text-xs text-slate-500">
                                        An attachment is already stored. Choose a new file to replace it; leave empty to keep the current file.
                                    </p>
                                ) : null}
                            </div>
                        </div>
                    )}
                    <div className="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <Link
                            to="/communication/template"
                            className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-center text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                        >
                            Cancel
                        </Link>
                        <UiButton type="submit" disabled={busy} className="min-w-[120px] rounded-xl shadow-lg shadow-indigo-500/20">
                            {busy ? (
                                <>
                                    <UiInlineLoader /> Saving…
                                </>
                            ) : edit ? (
                                'Save changes'
                            ) : (
                                'Create template'
                            )}
                        </UiButton>
                    </div>
                </form>
            </div>
        </Shell>
    );
}
