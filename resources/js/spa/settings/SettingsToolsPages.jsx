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

const inputClass = 'mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900';
const labelClass = 'block text-sm font-medium text-slate-700';

function SettingsShell({ title, subtitle, children, loading }) {
    return (
        <AdminLayout>
            <div className="mx-auto max-w-3xl space-y-6 p-6">
                <div>
                    <Link to="/settings" className="text-xs font-medium text-blue-700 hover:underline">
                        ← Settings
                    </Link>
                    <h1 className="mt-2 text-2xl font-semibold text-slate-900">{title}</h1>
                    {subtitle ? <p className="mt-1 text-sm text-slate-600">{subtitle}</p> : null}
                </div>
                {loading ? <UiPageLoader text="Loading…" /> : children}
            </div>
        </AdminLayout>
    );
}

/** Storage driver: local vs S3 + AWS fields (legacy `storage_setting` form). */
export function SettingsStoragePage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'Storage', can_update: true });
    const [v, setV] = useState({
        file_system: 'local',
        aws_access_key_id: '',
        aws_secret_key: '',
        aws_region: '',
        aws_bucket: '',
        aws_endpoint: '',
    });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/storage-setting', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta({ title: m.title || 'Storage settings', can_update: m.can_update !== false });
                const list = Array.isArray(m.data) ? m.data : m.data?.data || [];
                const map = rowsToMap(list);
                setV({
                    file_system: map.file_system || 'local',
                    aws_access_key_id: map.aws_access_key_id || '',
                    aws_secret_key: map.aws_secret_key || '',
                    aws_region: map.aws_region || '',
                    aws_bucket: map.aws_bucket || '',
                    aws_endpoint: map.aws_endpoint || '',
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
            await axios.put('/storage-setting-update', { ...v }, { headers: { ...xhrJson, 'Content-Type': 'application/json' } });
            setInfo('Storage settings saved.');
            setTimeout(() => setInfo(''), 4000);
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSaving(false);
        }
    };

    const s3 = v.file_system === 's3';

    return (
        <SettingsShell
            title={meta.title}
            subtitle="Choose local disk or Amazon S3. When S3 is selected, all AWS fields are required."
            loading={loading}
        >
            {err ? (
                <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{err}</div>
            ) : null}
            {info ? (
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{info}</div>
            ) : null}
            <form onSubmit={save} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className={labelClass}>
                    File system *
                    <select className={inputClass} value={v.file_system} onChange={(e) => setV({ ...v, file_system: e.target.value })} required>
                        <option value="">Select…</option>
                        <option value="local">Local</option>
                        <option value="s3">S3</option>
                    </select>
                </label>
                {s3 ? (
                    <div className="grid gap-4 sm:grid-cols-2">
                        <label className={labelClass}>
                            AWS access key ID *
                            <input className={inputClass} value={v.aws_access_key_id} onChange={(e) => setV({ ...v, aws_access_key_id: e.target.value })} required={s3} />
                        </label>
                        <label className={labelClass}>
                            AWS secret key *
                            <input className={inputClass} value={v.aws_secret_key} onChange={(e) => setV({ ...v, aws_secret_key: e.target.value })} required={s3} />
                        </label>
                        <label className={labelClass}>
                            Region *
                            <input className={inputClass} value={v.aws_region} onChange={(e) => setV({ ...v, aws_region: e.target.value })} required={s3} />
                        </label>
                        <label className={labelClass}>
                            Bucket *
                            <input className={inputClass} value={v.aws_bucket} onChange={(e) => setV({ ...v, aws_bucket: e.target.value })} required={s3} />
                        </label>
                        <label className={`${labelClass} sm:col-span-2`}>
                            Endpoint *
                            <input className={inputClass} value={v.aws_endpoint} onChange={(e) => setV({ ...v, aws_endpoint: e.target.value })} required={s3} />
                        </label>
                    </div>
                ) : (
                    <p className="text-sm text-slate-600">With local storage, uploads stay on this server. Switch to S3 to edit AWS credentials.</p>
                )}
                <div className="flex justify-end border-t border-slate-100 pt-4">
                    <UiButton type="submit" disabled={saving || !meta.can_update}>
                        {saving ? (
                            <>
                                <UiInlineLoader /> Saving…
                            </>
                        ) : (
                            'Save'
                        )}
                    </UiButton>
                </div>
                {!meta.can_update ? <p className="text-center text-xs text-slate-500">No permission to update storage settings.</p> : null}
            </form>
        </SettingsShell>
    );
}

/** Manual exam result generation (legacy task scheduler screen). */
export function SettingsTaskSchedulersPage() {
    const [loading, setLoading] = useState(true);
    const [running, setRunning] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'Task schedules', can_run_tasks: true });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/task-schedulers', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta({
                    title: m.title || 'Task schedules',
                    can_run_tasks: m.can_run_tasks !== false,
                });
            })
            .catch((ex) => setErr(formatErr(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const run = async () => {
        if (!meta.can_run_tasks) return;
        if (!window.confirm('Run examination result generation now? This can take a while.')) return;
        setRunning(true);
        setErr('');
        try {
            const { data } = await axios.get('/result-generate', { headers: xhrJson });
            setInfo(data?.message || 'Completed.');
            setTimeout(() => setInfo(''), 5000);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setRunning(false);
        }
    };

    return (
        <SettingsShell
            title={meta.title}
            subtitle="Run the same Artisan job as the legacy “Run” link for examination results. Schedule `php artisan schedule:run` on the server for automatic runs."
            loading={loading}
        >
            {err ? <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{err}</div> : null}
            {info ? <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{info}</div> : null}
            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p className="text-sm font-medium text-slate-800">Examination result generate (manual)</p>
                <p className="mt-2 text-sm text-slate-600">Triggers <code className="rounded bg-slate-100 px-1">php artisan exam:result-generate</code>.</p>
                <div className="mt-4">
                    <UiButton type="button" onClick={run} disabled={running || !meta.can_run_tasks}>
                        {running ? (
                            <>
                                <UiInlineLoader /> Running…
                            </>
                        ) : (
                            'Run now'
                        )}
                    </UiButton>
                </div>
                {!meta.can_run_tasks ? <p className="mt-3 text-xs text-slate-500">You do not have permission to run this action.</p> : null}
            </div>
        </SettingsShell>
    );
}

/** Database migrations from the browser (legacy software update screen). */
export function SettingsSoftwareUpdatePage() {
    const [loading, setLoading] = useState(true);
    const [running, setRunning] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'Software update', can_migrate: true });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/software-update', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta({
                    title: m.title || 'Software update',
                    can_migrate: m.can_migrate !== false,
                });
            })
            .catch((ex) => setErr(formatErr(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const migrate = async () => {
        if (!meta.can_migrate) return;
        if (!window.confirm('Run database migrations now? Only continue if you know what this does.')) return;
        setRunning(true);
        setErr('');
        try {
            const { data } = await axios.get('/install-update', { headers: xhrJson });
            setInfo(data?.message || 'Migrations finished.');
            setTimeout(() => setInfo(''), 5000);
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setRunning(false);
        }
    };

    return (
        <SettingsShell
            title={meta.title}
            subtitle="Applies pending Laravel migrations (`php artisan migrate`). Use with care on production."
            loading={loading}
        >
            {err ? <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{err}</div> : null}
            {info ? <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{info}</div> : null}
            <div className="rounded-2xl border border-amber-200 bg-amber-50/80 p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-amber-950">Database update</h2>
                <p className="mt-2 text-sm text-amber-950/90">Same action as the legacy “Database update” button.</p>
                <div className="mt-4">
                    <UiButton type="button" onClick={migrate} disabled={running || !meta.can_migrate} className="bg-amber-800 text-white hover:bg-amber-900">
                        {running ? (
                            <>
                                <UiInlineLoader /> Running migrations…
                            </>
                        ) : (
                            'Run migrations'
                        )}
                    </UiButton>
                </div>
                {!meta.can_migrate ? <p className="mt-3 text-xs text-amber-900/80">You do not have permission to run migrations.</p> : null}
            </div>
        </SettingsShell>
    );
}

/** Google reCAPTCHA site/secret and on/off flag. */
export function SettingsRecaptchaPage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'reCAPTCHA', can_update: true });
    const [form, setForm] = useState({ recaptcha_sitekey: '', recaptcha_secret: '', recaptcha_status: '1' });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/recaptcha-setting', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta({ title: m.title || 'reCAPTCHA', can_update: m.can_update !== false });
                const map = rowsToMap(Array.isArray(m.data) ? m.data : m.data?.data || []);
                setForm({
                    recaptcha_sitekey: map.recaptcha_sitekey || '',
                    recaptcha_secret: map.recaptcha_secret || '',
                    recaptcha_status: map.recaptcha_status !== '' && map.recaptcha_status !== undefined ? String(map.recaptcha_status) : '1',
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
            await axios.post('/recaptcha-setting', { ...form }, { headers: { ...xhrJson, 'Content-Type': 'application/json' } });
            setInfo('reCAPTCHA settings saved.');
            setTimeout(() => setInfo(''), 4000);
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSaving(false);
        }
    };

    return (
        <SettingsShell title={meta.title} subtitle="Keys and status for Google reCAPTCHA on public forms." loading={loading}>
            {err ? <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{err}</div> : null}
            {info ? <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{info}</div> : null}
            <form onSubmit={save} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className={labelClass}>
                    Site key *
                    <input className={inputClass} value={form.recaptcha_sitekey} onChange={(e) => setForm({ ...form, recaptcha_sitekey: e.target.value })} required />
                </label>
                <label className={labelClass}>
                    Secret key *
                    <input className={inputClass} value={form.recaptcha_secret} onChange={(e) => setForm({ ...form, recaptcha_secret: e.target.value })} required />
                </label>
                <label className={labelClass}>
                    Status *
                    <select className={inputClass} value={form.recaptcha_status} onChange={(e) => setForm({ ...form, recaptcha_status: e.target.value })} required>
                        <option value="">Select…</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </label>
                <div className="flex justify-end border-t border-slate-100 pt-4">
                    <UiButton type="submit" disabled={saving || !meta.can_update}>
                        {saving ? (
                            <>
                                <UiInlineLoader /> Saving…
                            </>
                        ) : (
                            'Save'
                        )}
                    </UiButton>
                </div>
                {!meta.can_update ? <p className="text-center text-xs text-slate-500">No permission to update reCAPTCHA.</p> : null}
            </form>
        </SettingsShell>
    );
}

/** Twilio SMS credentials (legacy SMS settings form). */
export function SettingsSmsPage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'SMS settings', can_update: true });
    const [form, setForm] = useState({
        twilio_account_sid: '',
        twilio_auth_token: '',
        twilio_phone_number: '',
    });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/sms-setting', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta({ title: m.title || 'SMS settings', can_update: m.can_update !== false });
                const map = rowsToMap(Array.isArray(m.data) ? m.data : m.data?.data || []);
                setForm({
                    twilio_account_sid: map.twilio_account_sid || '',
                    twilio_auth_token: map.twilio_auth_token || '',
                    twilio_phone_number: map.twilio_phone_number || '',
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
            await axios.post('/sms-setting', { ...form }, { headers: { ...xhrJson, 'Content-Type': 'application/json' } });
            setInfo('SMS settings saved.');
            setTimeout(() => setInfo(''), 4000);
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSaving(false);
        }
    };

    return (
        <SettingsShell title={meta.title} subtitle="Twilio account SID, auth token, and sender phone number for outbound SMS." loading={loading}>
            {err ? <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{err}</div> : null}
            {info ? <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{info}</div> : null}
            <form onSubmit={save} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className={labelClass}>
                    Twilio account SID *
                    <input className={inputClass} value={form.twilio_account_sid} onChange={(e) => setForm({ ...form, twilio_account_sid: e.target.value })} required />
                </label>
                <label className={labelClass}>
                    Twilio auth token *
                    <input className={inputClass} value={form.twilio_auth_token} onChange={(e) => setForm({ ...form, twilio_auth_token: e.target.value })} required />
                </label>
                <label className={labelClass}>
                    Twilio phone number *
                    <input className={inputClass} value={form.twilio_phone_number} onChange={(e) => setForm({ ...form, twilio_phone_number: e.target.value })} required />
                </label>
                <div className="flex justify-end border-t border-slate-100 pt-4">
                    <UiButton type="submit" disabled={saving || !meta.can_update}>
                        {saving ? (
                            <>
                                <UiInlineLoader /> Saving…
                            </>
                        ) : (
                            'Save'
                        )}
                    </UiButton>
                </div>
                {!meta.can_update ? <p className="text-center text-xs text-slate-500">No permission to update SMS settings.</p> : null}
            </form>
        </SettingsShell>
    );
}
