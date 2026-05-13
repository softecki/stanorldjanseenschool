import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell } from '../CommunicationModuleShared';
import { UiButton, UiInlineLoader, UiPageLoader } from '../../ui/UiKit';

function csvJoin(arr) {
    if (!Array.isArray(arr) || !arr.length) return '';
    return arr.map(String).join(',');
}

function formatApiError(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
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
        return 'Request failed.';
    }
}

const field =
    'mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
const label = 'block text-sm font-medium text-slate-700';

export function SmsMailCreatePage({ Layout }) {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [templates, setTemplates] = useState([]);
    const [roles, setRoles] = useState([]);
    const [classRows, setClassRows] = useState([]);
    const [sections, setSections] = useState([]);
    const [userList, setUserList] = useState([]);
    const [form, setForm] = useState({
        title: '',
        type: 'sms',
        user_type: 'role',
        role_ids: [],
        filterRoleId: '',
        userIds: [],
        class_id: '',
        section_ids: [],
        template_mail: '',
        sms_description: '',
        mail_description: '',
    });
    const [mailFile, setMailFile] = useState(null);
    const [excel, setExcel] = useState(null);
    const [preview, setPreview] = useState(null);
    const [previewLoading, setPreviewLoading] = useState(false);
    const [pageLoading, setPageLoading] = useState(true);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        setPageLoading(true);
        axios
            .get('/communication/smsmail/create', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta(m);
                setTemplates(Array.isArray(m.templates) ? m.templates : []);
                setRoles(Array.isArray(m.roles) ? m.roles : []);
                setClassRows(Array.isArray(m.classes) ? m.classes : []);
            })
            .catch((ex) => setErr(formatApiError(ex)))
            .finally(() => setPageLoading(false));
    }, []);

    useEffect(() => {
        if (form.user_type !== 'class' || !form.class_id) {
            setSections([]);
            return;
        }
        axios
            .get('/class-setup/get-sections', { params: { id: form.class_id }, headers: xhrJson })
            .then((res) => {
                const raw = Array.isArray(res.data) ? res.data : [];
                setSections(
                    raw
                        .map((item) => ({
                            id: item.section?.id,
                            name: item.section?.name,
                        }))
                        .filter((x) => x.id != null),
                );
            })
            .catch(() => setSections([]));
    }, [form.class_id, form.user_type]);

    useEffect(() => {
        if (form.user_type !== 'individual' || !form.filterRoleId) {
            setUserList([]);
            return;
        }
        axios
            .get('/communication/smsmail/users', { params: { role_id: form.filterRoleId }, headers: xhrJson })
            .then((res) => {
                setUserList(Array.isArray(res.data) ? res.data : []);
            })
            .catch(() => setUserList([]));
    }, [form.user_type, form.filterRoleId]);

    useEffect(() => {
        if (form.type !== 'mail' || !form.template_mail) return;
        let cancelled = false;
        axios
            .get('/communication/smsmail/template', { params: { template_id: form.template_mail }, headers: xhrJson })
            .then((res) => {
                const d = res.data;
                if (cancelled || !d || d.type !== 'mail') return;
                setForm((f) => ({ ...f, mail_description: d.mail_description != null ? String(d.mail_description) : f.mail_description }));
            })
            .catch(() => {});
        return () => {
            cancelled = true;
        };
    }, [form.type, form.template_mail]);

    const toggleInList = (key, id, checked) => {
        const n = Number(id);
        setForm((f) => {
            const cur = new Set((f[key] || []).map(Number));
            if (checked) cur.add(n);
            else cur.delete(n);
            return { ...f, [key]: [...cur] };
        });
    };

    const runPreview = async () => {
        if (!excel) {
            setErr('Choose an Excel file first.');
            return;
        }
        if (!form.sms_description.trim()) {
            setErr('Enter the SMS message before preview.');
            return;
        }
        setPreviewLoading(true);
        setErr('');
        try {
            const fd = new FormData();
            fd.append('excel_file', excel);
            fd.append('sms_description', form.sms_description);
            const { data } = await axios.post('/communication/smsmail/preview', fd, { headers: xhrJson });
            if (data?.status && data?.data?.students) {
                setPreview(data.data);
            } else {
                setPreview(null);
                setErr(data?.message || 'Preview failed.');
            }
        } catch (ex) {
            setPreview(null);
            setErr(formatApiError(ex));
        } finally {
            setPreviewLoading(false);
        }
    };

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const fd = new FormData();
            fd.append('title', form.title.trim() || 'Bulk SMS');
            fd.append('type', form.type);
            fd.append('template_sms', form.type === 'sms' ? 'SMS' : '');
            fd.append('template_mail', form.type === 'mail' ? form.template_mail : '');
            fd.append('sms_description', form.sms_description || '');
            fd.append('mail_description', form.mail_description || '');
            fd.append('user_type', form.user_type);
            fd.append('role_ids', csvJoin(form.role_ids));
            fd.append('role', form.filterRoleId || '');
            fd.append('users', csvJoin(form.userIds));
            fd.append('class_id', form.class_id || '');
            fd.append('section_ids', csvJoin(form.section_ids));
            if (mailFile) fd.append('attachment', mailFile);
            if (excel && form.type === 'sms') fd.append('excel_file', excel);

            const { data, status } = await axios.post('/communication/smsmail/store', fd, { headers: xhrJson });
            if (status === 200 && data?.status) {
                nav('/communication/smsmail');
                return;
            }
            setErr(data?.message || 'Send was rejected.');
        } catch (ex) {
            setErr(formatApiError(ex));
        } finally {
            setBusy(false);
        }
    };

    const classOptions = classRows
        .map((row) => ({
            id: row.class?.id ?? row.classes_id,
            name: row.class?.name ?? row.name,
        }))
        .filter((x) => x.id != null);

    const mailTemplates = templates.filter((t) => t.type === 'mail');

    if (pageLoading) {
        return (
            <Shell Layout={Layout}>
                <UiPageLoader text="Loading send form…" />
            </Shell>
        );
    }

    return (
        <Shell Layout={Layout}>
            <div className="mx-auto max-w-5xl space-y-8">
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-600 via-indigo-600 to-violet-700 px-6 py-8 text-white shadow-xl sm:px-8">
                    <div className="pointer-events-none absolute -bottom-16 -left-10 h-48 w-48 rounded-full bg-white/10 blur-3xl" aria-hidden />
                    <div className="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-sky-100/90">SMS / Mail</p>
                            <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{meta.title || 'Create send'}</h1>
                            <p className="mt-2 max-w-2xl text-sm text-indigo-100/95">
                                Matches the legacy composer: SMS with optional Excel ({'{name}'}, {'{balance}'}), role / individual / class targeting, or mail with template and attachment.
                            </p>
                        </div>
                        <Link
                            to="/communication/smsmail"
                            className="inline-flex shrink-0 items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"
                        >
                            Back to log
                        </Link>
                    </div>
                </div>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}

                <form onSubmit={submit} className="space-y-8">
                    <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg sm:p-8">
                        <h2 className="border-b border-slate-100 pb-3 text-lg font-semibold text-slate-900">Basics</h2>
                        <div className="mt-6 grid gap-6 sm:grid-cols-2">
                            <label className={label}>
                                Title
                                <input
                                    className={field}
                                    value={form.title}
                                    onChange={(e) => setForm({ ...form, title: e.target.value })}
                                    placeholder="e.g. Fee reminder — Form 4"
                                    required
                                />
                            </label>
                            <label className={label}>
                                Type
                                <select
                                    className={field}
                                    value={form.type}
                                    onChange={(e) => setForm({ ...form, type: e.target.value, template_mail: '' })}
                                >
                                    <option value="sms">SMS</option>
                                    <option value="mail">Mail</option>
                                </select>
                            </label>
                        </div>
                    </div>

                    {form.type === 'sms' ? (
                        <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg sm:p-8">
                            <h2 className="border-b border-slate-100 pb-3 text-lg font-semibold text-slate-900">SMS message</h2>
                            <label className={`${label} mt-6`}>
                                Message body
                                <textarea
                                    className={`${field} min-h-[120px]`}
                                    rows={5}
                                    value={form.sms_description}
                                    onChange={(e) => setForm({ ...form, sms_description: e.target.value })}
                                    placeholder='Example: Hello {name}, balance {balance}. Use {name} and {balance} for Excel sends.'
                                    required
                                />
                            </label>

                            <div className="mt-6 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold text-indigo-900">Excel send (optional)</p>
                                        <p className="mt-1 text-xs text-indigo-800/90">
                                            Same as legacy: names column, optional CLASS N S: NAME rows. Download the school template first.
                                        </p>
                                    </div>
                                    <a
                                        href="/communication/smsmail/download-template"
                                        className="inline-flex items-center justify-center rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs font-medium text-indigo-700 shadow-sm hover:bg-indigo-50"
                                    >
                                        Download template
                                    </a>
                                </div>
                                <input
                                    type="file"
                                    accept=".xlsx,.xls,.csv"
                                    className="mt-3 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-3 file:py-2 file:text-xs file:font-medium file:text-white"
                                    onChange={(e) => {
                                        setExcel(e.target.files?.[0] || null);
                                        setPreview(null);
                                    }}
                                />
                                <div className="mt-3 flex flex-wrap gap-2">
                                    <UiButton type="button" variant="secondary" disabled={previewLoading} onClick={runPreview}>
                                        {previewLoading ? (
                                            <>
                                                <UiInlineLoader /> Preview…
                                            </>
                                        ) : (
                                            'Preview students'
                                        )}
                                    </UiButton>
                                </div>
                            </div>

                            {preview?.students?.length ? (
                                <div className="mt-6 overflow-x-auto rounded-xl border border-slate-200">
                                    <table className="min-w-full divide-y divide-slate-200 text-xs">
                                        <thead className="bg-slate-50">
                                            <tr>
                                                <th className="px-3 py-2 text-left font-semibold text-slate-600">#</th>
                                                <th className="px-3 py-2 text-left font-semibold text-slate-600">Name</th>
                                                <th className="px-3 py-2 text-left font-semibold text-slate-600">Balance</th>
                                                <th className="px-3 py-2 text-left font-semibold text-slate-600">Phone</th>
                                                <th className="px-3 py-2 text-left font-semibold text-slate-600">Preview</th>
                                                <th className="px-3 py-2 text-left font-semibold text-slate-600">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100 bg-white">
                                            {preview.students.map((s, i) => (
                                                <tr key={i} className={s.phone ? '' : 'bg-amber-50/60'}>
                                                    <td className="px-3 py-2 text-slate-500">{i + 1}</td>
                                                    <td className="px-3 py-2 font-medium text-slate-800">{s.name}</td>
                                                    <td className="px-3 py-2 tabular-nums text-slate-700">{s.balance != null ? Number(s.balance).toLocaleString() : '0'}</td>
                                                    <td className="px-3 py-2 font-mono text-slate-700">{s.phone || '—'}</td>
                                                    <td className="max-w-xs px-3 py-2 text-slate-600">
                                                        <span className="line-clamp-2" title={s.message_preview}>
                                                            {s.message_preview || '—'}
                                                        </span>
                                                    </td>
                                                    <td className="px-3 py-2">
                                                        <span className={s.phone ? 'font-medium text-emerald-700' : 'font-medium text-amber-800'}>{s.phone ? 'Found' : 'No phone'}</span>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                    <p className="border-t border-slate-100 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                        Total: {preview.total_students ?? preview.students.length} students in file.
                                    </p>
                                </div>
                            ) : null}
                        </div>
                    ) : (
                        <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg sm:p-8">
                            <h2 className="border-b border-slate-100 pb-3 text-lg font-semibold text-slate-900">Mail</h2>
                            <div className="mt-6 space-y-6">
                                <label className={label}>
                                    Mail template
                                    <select
                                        className={field}
                                        value={form.template_mail}
                                        onChange={(e) => setForm({ ...form, template_mail: e.target.value })}
                                        required
                                    >
                                        <option value="">Select template</option>
                                        {mailTemplates.map((t) => (
                                            <option key={t.id} value={t.id}>
                                                {t.title}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <label className={label}>
                                    Mail body
                                    <textarea
                                        className={`${field} min-h-[160px]`}
                                        rows={8}
                                        value={form.mail_description}
                                        onChange={(e) => setForm({ ...form, mail_description: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className={label}>
                                    Attachment
                                    <input
                                        type="file"
                                        className="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-700 file:px-3 file:py-2 file:text-xs file:font-medium file:text-white"
                                        onChange={(e) => setMailFile(e.target.files?.[0] || null)}
                                    />
                                </label>
                            </div>
                        </div>
                    )}

                    <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg sm:p-8">
                        <h2 className="border-b border-slate-100 pb-3 text-lg font-semibold text-slate-900">Audience</h2>
                        <p className="mt-2 text-xs text-slate-500">Ignored for the SMS+Excel path (numbers come from the file). Otherwise required fields match the legacy form.</p>

                        <label className={`${label} mt-6`}>
                            Target group
                            <select
                                className={field}
                                value={form.user_type}
                                onChange={(e) =>
                                    setForm({
                                        ...form,
                                        user_type: e.target.value,
                                        role_ids: [],
                                        filterRoleId: '',
                                        userIds: [],
                                        class_id: '',
                                        section_ids: [],
                                    })
                                }
                            >
                                <option value="role">Role</option>
                                <option value="individual">Individual users</option>
                                <option value="class">Class &amp; sections</option>
                            </select>
                        </label>

                        {form.user_type === 'role' ? (
                            <div className="mt-6">
                                <p className={label}>Roles (multi-select)</p>
                                <div className="mt-2 max-h-52 space-y-2 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                                    {roles.map((r) => {
                                        const checked = form.role_ids.map(Number).includes(Number(r.id));
                                        return (
                                            <label key={r.id} className="flex cursor-pointer items-center gap-2 text-sm text-slate-800">
                                                <input
                                                    type="checkbox"
                                                    className="h-4 w-4 rounded border-slate-300 text-indigo-600"
                                                    checked={checked}
                                                    onChange={(e) => toggleInList('role_ids', r.id, e.target.checked)}
                                                />
                                                {r.name} <span className="text-xs text-slate-400">(id {r.id})</span>
                                            </label>
                                        );
                                    })}
                                </div>
                            </div>
                        ) : null}

                        {form.user_type === 'individual' ? (
                            <div className="mt-6 grid gap-6 sm:grid-cols-2">
                                <label className={label}>
                                    Filter by role
                                    <select
                                        className={field}
                                        value={form.filterRoleId}
                                        onChange={(e) => setForm({ ...form, filterRoleId: e.target.value, userIds: [] })}
                                        required
                                    >
                                        <option value="">Select role</option>
                                        {roles.map((r) => (
                                            <option key={r.id} value={r.id}>
                                                {r.name}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <div className="sm:col-span-2">
                                    <p className={label}>Users</p>
                                    <div className="mt-2 max-h-52 space-y-2 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                                        {userList.length ? (
                                            userList.map((u) => {
                                                const checked = form.userIds.map(Number).includes(Number(u.id));
                                                return (
                                                    <label key={u.id} className="flex cursor-pointer items-center gap-2 text-sm text-slate-800">
                                                        <input
                                                            type="checkbox"
                                                            className="h-4 w-4 rounded border-slate-300 text-indigo-600"
                                                            checked={checked}
                                                            onChange={(e) => toggleInList('userIds', u.id, e.target.checked)}
                                                        />
                                                        {u.name}
                                                    </label>
                                                );
                                            })
                                        ) : (
                                            <p className="text-sm text-slate-500">Pick a role to load users.</p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ) : null}

                        {form.user_type === 'class' ? (
                            <div className="mt-6 grid gap-6 sm:grid-cols-2">
                                <label className={label}>
                                    Class
                                    <select
                                        className={field}
                                        value={form.class_id}
                                        onChange={(e) => setForm({ ...form, class_id: e.target.value, section_ids: [] })}
                                        required
                                    >
                                        <option value="">Select class</option>
                                        {classOptions.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <div className="sm:col-span-2">
                                    <p className={label}>Sections</p>
                                    <div className="mt-2 flex flex-wrap gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                                        {sections.length ? (
                                            sections.map((s) => {
                                                const checked = form.section_ids.map(Number).includes(Number(s.id));
                                                return (
                                                    <label key={s.id} className="flex cursor-pointer items-center gap-2 text-sm text-slate-800">
                                                        <input
                                                            type="checkbox"
                                                            className="h-4 w-4 rounded border-slate-300 text-indigo-600"
                                                            checked={checked}
                                                            onChange={(e) => toggleInList('section_ids', s.id, e.target.checked)}
                                                        />
                                                        {s.name}
                                                    </label>
                                                );
                                            })
                                        ) : (
                                            <p className="text-sm text-slate-500">{form.class_id ? 'No sections for this class.' : 'Select a class first.'}</p>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ) : null}
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <Link
                            to="/communication/smsmail"
                            className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-center text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                        >
                            Cancel
                        </Link>
                        <UiButton type="submit" disabled={busy} className="min-w-[160px] rounded-xl shadow-lg shadow-indigo-500/25">
                            {busy ? (
                                <>
                                    <UiInlineLoader /> Sending…
                                </>
                            ) : (
                                'Submit send'
                            )}
                        </UiButton>
                    </div>
                </form>
            </div>
        </Shell>
    );
}
