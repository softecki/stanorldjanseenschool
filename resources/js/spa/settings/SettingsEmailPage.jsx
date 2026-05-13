import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import { UiButton, UiInlineLoader, UiPageLoader } from '../ui/UiKit';

function rowsToMap(rows) {
    const m = {};
    if (!Array.isArray(rows)) return m;
    rows.forEach((r) => {
        if (r && r.name != null) m[String(r.name)] = r.value != null ? String(r.value) : '';
    });
    return m;
}

function formatErr(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (d.errors && typeof d.errors === 'object') {
        return Object.entries(d.errors)
            .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(' ') : v}`)
            .join(' · ');
    }
    return 'Request failed.';
}

const inputClass =
    'mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500/20';
const labelClass = 'block text-sm font-medium text-slate-700';

/** SMTP / mail settings (legacy mail-settings form). */
export function SettingsEmailPage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'Email settings', can_update: true });
    const [form, setForm] = useState({
        mail_host: '',
        mail_port: '',
        mail_address: '',
        from_name: '',
        mail_username: '',
        mail_password: '',
        encryption: '',
    });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/email-setting', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta({
                    title: m.title || 'Email settings',
                    can_update: m.can_update !== false,
                });
                const raw = m.data;
                const list = Array.isArray(raw) ? raw : Array.isArray(raw?.data) ? raw.data : [];
                const map = rowsToMap(list);
                setForm({
                    mail_host: map.mail_host || '',
                    mail_port: map.mail_port || '',
                    mail_address: map.mail_address || '',
                    from_name: map.from_name || '',
                    mail_username: map.mail_username || '',
                    mail_password: '',
                    encryption: map.encryption != null && map.encryption !== '' ? String(map.encryption) : '',
                });
            })
            .catch((ex) => setErr(formatErr(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const save = async (e) => {
        e.preventDefault();
        if (!meta.can_update) return;
        setSaving(true);
        setErr('');
        try {
            const body = { ...form };
            if (!body.mail_password || !String(body.mail_password).trim()) {
                delete body.mail_password;
            }
            await axios.post('/email-setting', body, {
                headers: { ...xhrJson, 'Content-Type': 'application/json' },
            });
            setInfo('Email settings saved.');
            setTimeout(() => setInfo(''), 4000);
            setForm((f) => ({ ...f, mail_password: '' }));
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-3xl space-y-6 p-6">
                <div className="rounded-3xl bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 px-6 py-8 text-white shadow-xl sm:px-8">
                    <Link to="/settings" className="text-xs font-semibold uppercase tracking-wide text-sky-200/90 hover:text-white">
                        ← Settings
                    </Link>
                    <h1 className="mt-2 text-2xl font-bold sm:text-3xl">{meta.title}</h1>
                    <p className="mt-2 max-w-xl text-sm text-sky-100/90">Outgoing mail (SMTP) for system emails. Leave password blank to keep the current stored password.</p>
                </div>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900" role="alert">
                        {err}
                    </div>
                ) : null}
                {info ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950" role="status">
                        {info}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading mail settings…" />
                ) : (
                    <form onSubmit={save} className="space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className={labelClass}>
                                Mail host *
                                <input className={inputClass} value={form.mail_host} onChange={(e) => setForm({ ...form, mail_host: e.target.value })} required />
                            </label>
                            <label className={labelClass}>
                                Mail port *
                                <input className={inputClass} value={form.mail_port} onChange={(e) => setForm({ ...form, mail_port: e.target.value })} required />
                            </label>
                            <label className={labelClass}>
                                From address *
                                <input type="email" className={inputClass} value={form.mail_address} onChange={(e) => setForm({ ...form, mail_address: e.target.value })} required />
                            </label>
                            <label className={labelClass}>
                                From name *
                                <input className={inputClass} value={form.from_name} onChange={(e) => setForm({ ...form, from_name: e.target.value })} required />
                            </label>
                            <label className={labelClass}>
                                SMTP username *
                                <input className={inputClass} value={form.mail_username} onChange={(e) => setForm({ ...form, mail_username: e.target.value })} required />
                            </label>
                            <label className={labelClass}>
                                SMTP password <span className="font-normal text-slate-400">(optional)</span>
                                <input
                                    type="password"
                                    className={inputClass}
                                    value={form.mail_password}
                                    onChange={(e) => setForm({ ...form, mail_password: e.target.value })}
                                    placeholder="Leave blank to keep existing"
                                    autoComplete="new-password"
                                />
                            </label>
                            <label className={`${labelClass} sm:col-span-2`}>
                                Encryption *
                                <select className={inputClass} value={form.encryption} onChange={(e) => setForm({ ...form, encryption: e.target.value })} required>
                                    <option value="">Select…</option>
                                    <option value="null">None</option>
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                            </label>
                        </div>
                        <div className="flex justify-end border-t border-slate-100 pt-4">
                            <UiButton type="submit" disabled={saving || !meta.can_update} className="bg-sky-600 text-white hover:bg-sky-700">
                                {saving ? (
                                    <>
                                        <UiInlineLoader /> Saving…
                                    </>
                                ) : (
                                    'Save'
                                )}
                            </UiButton>
                        </div>
                        {!meta.can_update ? <p className="text-center text-xs text-slate-500">No permission to update email settings.</p> : null}
                    </form>
                )}
            </div>
        </AdminLayout>
    );
}
