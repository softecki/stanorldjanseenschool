import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import { UiButton, UiInlineLoader, UiPageLoader } from '../ui/UiKit';

function mediaUrl(path) {
    if (!path) return '';
    const s = String(path);
    if (s.startsWith('http') || s.startsWith('//')) return s;
    return s.startsWith('/') ? s : `/${s}`;
}

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

/** Full general settings form (parity with legacy Blade). */
export function SettingsGeneralPage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'General settings', can_update: true });
    const [languages, setLanguages] = useState([]);
    const [sessions, setSessions] = useState([]);
    const [currencies, setCurrencies] = useState([]);
    const [values, setValues] = useState({});
    const [files, setFiles] = useState({ light_logo: null, dark_logo: null, favicon: null });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/general-settings', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta({
                    title: m.title || 'General settings',
                    can_update: m.can_update !== false,
                });
                setLanguages(Array.isArray(m.languages) ? m.languages : m.languages?.data || []);
                setSessions(Array.isArray(m.sessions) ? m.sessions : m.sessions?.data || []);
                setCurrencies(Array.isArray(m.currencies) ? m.currencies : m.currencies?.data || []);
                const raw = m.data;
                const list = Array.isArray(raw) ? raw : raw?.data || [];
                setValues(rowsToMap(list));
            })
            .catch((ex) => setErr(formatErr(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const form = useMemo(
        () => ({
            application_name: values.application_name ?? '',
            footer_text: values.footer_text ?? '',
            address: values.address ?? '',
            map_key: values.map_key ?? '',
            phone: values.phone ?? '',
            email: values.email ?? '',
            school_about: values.school_about ?? '',
            default_langauge: values.default_langauge ?? '',
            session: values.session != null && values.session !== '' ? String(values.session) : '',
            currency_code: values.currency_code ?? '',
        }),
        [values],
    );

    const setField = (name, v) => {
        setValues((prev) => ({ ...prev, [name]: v }));
    };

    const submit = async (e) => {
        e.preventDefault();
        if (!meta.can_update) return;
        setErr('');
        setSaving(true);
        try {
            const fd = new FormData();
            fd.append('application_name', form.application_name);
            fd.append('footer_text', form.footer_text);
            fd.append('address', form.address);
            fd.append('map_key', form.map_key);
            fd.append('phone', form.phone);
            fd.append('email', form.email);
            fd.append('school_about', form.school_about);
            fd.append('default_langauge', form.default_langauge);
            fd.append('session', form.session);
            fd.append('currency_code', form.currency_code);
            if (files.light_logo) fd.append('light_logo', files.light_logo);
            if (files.dark_logo) fd.append('dark_logo', files.dark_logo);
            if (files.favicon) fd.append('favicon', files.favicon);

            await axios.post('/general-settings', fd, {
                headers: { ...xhrJson },
            });
            setInfo('Settings saved.');
            setTimeout(() => setInfo(''), 4000);
            setFiles({ light_logo: null, dark_logo: null, favicon: null });
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSaving(false);
        }
    };

    const inputClass = 'mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900';
    const labelClass = 'block text-sm font-medium text-slate-700';

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl space-y-6 p-6">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <Link to="/settings" className="text-xs font-medium text-blue-700 hover:underline">
                            ← Settings
                        </Link>
                        <h1 className="mt-2 text-2xl font-semibold text-slate-900">{meta.title}</h1>
                        <p className="mt-1 text-sm text-slate-600">
                            Branding, locale, session, currency, and contact details. Logos and favicon are optional on each save.
                        </p>
                    </div>
                    <a
                        href="/general-settings/translate"
                        className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50"
                    >
                        Translate labels
                    </a>
                </div>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {info ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        {info}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading settings…" />
                ) : (
                    <form onSubmit={submit} className="space-y-8 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
                        <section className="space-y-4">
                            <h2 className="text-sm font-semibold uppercase tracking-wide text-slate-500">Branding</h2>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <label className={labelClass}>
                                    Application name *
                                    <input
                                        className={inputClass}
                                        value={form.application_name}
                                        onChange={(e) => setField('application_name', e.target.value)}
                                        required
                                    />
                                </label>
                                <label className={labelClass}>
                                    Footer text *
                                    <input
                                        className={inputClass}
                                        value={form.footer_text}
                                        onChange={(e) => setField('footer_text', e.target.value)}
                                        required
                                    />
                                </label>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <p className={labelClass}>Light logo</p>
                                    <img
                                        src={mediaUrl(values.light_logo)}
                                        alt=""
                                        className="mt-2 h-16 max-w-full rounded-lg border border-slate-100 bg-slate-50 object-contain p-2"
                                    />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        className="mt-2 block w-full text-sm text-slate-600"
                                        onChange={(e) => setFiles((f) => ({ ...f, light_logo: e.target.files?.[0] || null }))}
                                    />
                                </div>
                                <div>
                                    <p className={labelClass}>Dark logo</p>
                                    <img
                                        src={mediaUrl(values.dark_logo)}
                                        alt=""
                                        className="mt-2 h-16 max-w-full rounded-lg border border-slate-100 bg-slate-900/90 object-contain p-2"
                                    />
                                    <input
                                        type="file"
                                        accept="image/*"
                                        className="mt-2 block w-full text-sm text-slate-600"
                                        onChange={(e) => setFiles((f) => ({ ...f, dark_logo: e.target.files?.[0] || null }))}
                                    />
                                </div>
                            </div>

                            <div>
                                <p className={labelClass}>Favicon</p>
                                <img
                                    src={mediaUrl(values.favicon)}
                                    alt=""
                                    className="mt-2 h-14 w-14 rounded-lg border border-slate-100 bg-slate-50 object-contain p-1"
                                />
                                <input
                                    type="file"
                                    accept="image/*"
                                    className="mt-2 block w-full text-sm text-slate-600"
                                    onChange={(e) => setFiles((f) => ({ ...f, favicon: e.target.files?.[0] || null }))}
                                />
                            </div>
                        </section>

                        <section className="space-y-4 border-t border-slate-100 pt-6">
                            <h2 className="text-sm font-semibold uppercase tracking-wide text-slate-500">Locale &amp; session</h2>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <label className={labelClass}>
                                    Default language *
                                    <select
                                        className={inputClass}
                                        value={form.default_langauge}
                                        onChange={(e) => setField('default_langauge', e.target.value)}
                                        required
                                    >
                                        <option value="">Select…</option>
                                        {languages.map((row) => (
                                            <option key={row.id ?? row.code} value={row.code}>
                                                {row.name}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className={labelClass}>
                                    Currency *
                                    <select
                                        className={inputClass}
                                        value={form.currency_code}
                                        onChange={(e) => setField('currency_code', e.target.value)}
                                        required
                                    >
                                        <option value="">Select…</option>
                                        {currencies.map((c) => (
                                            <option key={c.code} value={c.code}>
                                                {c.code} — {c.symbol}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className={`${labelClass} sm:col-span-2`}>
                                    Active session *
                                    <select
                                        className={inputClass}
                                        value={form.session}
                                        onChange={(e) => setField('session', e.target.value)}
                                        required
                                    >
                                        <option value="">Select…</option>
                                        {sessions.map((row) => (
                                            <option key={row.id} value={String(row.id)}>
                                                {row.name}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                            </div>
                        </section>

                        <section className="space-y-4 border-t border-slate-100 pt-6">
                            <h2 className="text-sm font-semibold uppercase tracking-wide text-slate-500">Contact &amp; map</h2>
                            <label className={labelClass}>
                                Address *
                                <input className={inputClass} value={form.address} onChange={(e) => setField('address', e.target.value)} required />
                            </label>
                            <label className={labelClass}>
                                Map embed / key *
                                <textarea className={`${inputClass} min-h-[100px]`} value={form.map_key} onChange={(e) => setField('map_key', e.target.value)} required />
                            </label>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <label className={labelClass}>
                                    Phone *
                                    <input className={inputClass} value={form.phone} onChange={(e) => setField('phone', e.target.value)} required />
                                </label>
                                <label className={labelClass}>
                                    Email *
                                    <input type="email" className={inputClass} value={form.email} onChange={(e) => setField('email', e.target.value)} required />
                                </label>
                            </div>
                            <label className={labelClass}>
                                School about *
                                <textarea
                                    className={`${inputClass} min-h-[140px]`}
                                    value={form.school_about}
                                    onChange={(e) => setField('school_about', e.target.value)}
                                    required
                                />
                            </label>
                        </section>

                        <div className="flex justify-end border-t border-slate-100 pt-4">
                            <UiButton type="submit" disabled={saving || !meta.can_update}>
                                {saving ? (
                                    <>
                                        <UiInlineLoader /> Saving…
                                    </>
                                ) : (
                                    'Save changes'
                                )}
                            </UiButton>
                        </div>
                        {!meta.can_update ? <p className="text-center text-xs text-slate-500">You do not have permission to update general settings.</p> : null}
                    </form>
                )}
            </div>
        </AdminLayout>
    );
}
