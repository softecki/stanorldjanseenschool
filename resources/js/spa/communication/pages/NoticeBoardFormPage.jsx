import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell } from '../CommunicationModuleShared';
import { IconCalendar, UiButton, UiInlineLoader, UiPageLoader } from '../../ui/UiKit';

function toDatetimeLocalValue(ds) {
    if (!ds) return '';
    return String(ds).replace(' ', 'T').slice(0, 16);
}

function formatApiError(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Save failed.';
    if (typeof d.message === 'string' && d.message && !d.errors) return d.message;
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

const fieldClass =
    'mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20';
const labelClass = 'block text-sm font-medium text-slate-700';

export function NoticeBoardFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [roles, setRoles] = useState([]);
    const [form, setForm] = useState({
        title: '',
        publish_date: '',
        date: '',
        description: '',
        status: 1,
        is_visible_web: 0,
        visible_to: [],
    });
    const [existingAttachmentId, setExistingAttachmentId] = useState(null);
    const [file, setFile] = useState(null);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);
    const [pageLoading, setPageLoading] = useState(true);

    useEffect(() => {
        setPageLoading(true);
        setErr('');
        if (!edit) {
            axios
                .get('/communication/notice-board/create', { headers: xhrJson })
                .then((r) => {
                    setMeta(r.data?.meta || {});
                    setRoles(r.data?.meta?.roles || []);
                })
                .catch((ex) => setErr(formatApiError(ex)))
                .finally(() => setPageLoading(false));
            return;
        }
        axios
            .get(`/communication/notice-board/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const payload = r.data?.data;
                const nb = payload?.['notice-board'] ?? payload;
                setRoles(payload?.roles || r.data?.meta?.roles || []);
                setExistingAttachmentId(nb?.attachment ?? null);
                const vis = nb?.visible_to;
                const visibleList = Array.isArray(vis) ? vis.map((x) => Number(x)) : [];
                setForm({
                    title: nb?.title ?? '',
                    publish_date: toDatetimeLocalValue(nb?.publish_date),
                    date: nb?.date ? String(nb.date).slice(0, 10) : '',
                    description: nb?.description ?? '',
                    status: nb?.status !== undefined && nb?.status !== null ? Number(nb.status) : 1,
                    is_visible_web: nb?.is_visible_web ? 1 : 0,
                    visible_to: visibleList,
                });
            })
            .catch((ex) => setErr(formatApiError(ex)))
            .finally(() => setPageLoading(false));
    }, [edit, id]);

    const toggleRole = (roleId) => {
        const rid = Number(roleId);
        setForm((f) => {
            const set = new Set((f.visible_to || []).map(Number));
            if (set.has(rid)) set.delete(rid);
            else set.add(rid);
            return { ...f, visible_to: [...set] };
        });
    };

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const fd = new FormData();
            fd.append('title', form.title);
            fd.append('publish_date', form.publish_date);
            fd.append('date', form.date);
            fd.append('description', form.description);
            fd.append('status', String(form.status));
            fd.append('is_visible_web', String(form.is_visible_web));
            form.visible_to.forEach((v) => fd.append('visible_to[]', v));
            if (file) fd.append('attachment', file);
            const headers = { ...xhrJson };
            if (edit) {
                fd.append('_method', 'PUT');
                await axios.post(`/communication/notice-board/update/${id}`, fd, { headers });
            } else {
                await axios.post('/communication/notice-board/store', fd, { headers });
            }
            nav('/communication/notice-board');
        } catch (ex) {
            setErr(formatApiError(ex));
        } finally {
            setBusy(false);
        }
    };

    if (pageLoading) {
        return (
            <Shell Layout={Layout}>
                <UiPageLoader text={edit ? 'Loading notice…' : 'Loading form…'} />
            </Shell>
        );
    }

    return (
        <Shell Layout={Layout}>
            <div className="mx-auto max-w-6xl space-y-8">
                <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 px-6 py-8 text-white shadow-xl sm:px-8">
                    <div className="pointer-events-none absolute -right-12 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl" aria-hidden />
                    <div className="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-100/90">Notice board</p>
                            <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">
                                {meta.title || (edit ? 'Edit notice' : 'Create notice')}
                            </h1>
                            <p className="mt-2 max-w-xl text-sm text-indigo-100/90">
                                {edit
                                    ? 'Update the announcement, schedule, audience, and optional image. Changes apply immediately for allowed roles.'
                                    : 'Compose a clear title and message, set when it publishes, and choose which roles can see it in their dashboards.'}
                            </p>
                        </div>
                        <Link
                            to="/communication/notice-board"
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

                <form onSubmit={submit} className="grid gap-8 lg:grid-cols-[1fr_minmax(260px,320px)]">
                    <div className="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg shadow-slate-200/40 sm:p-8">
                        <div className="border-b border-slate-100 pb-4">
                            <h2 className="text-lg font-semibold text-slate-900">Content</h2>
                            <p className="mt-1 text-sm text-slate-500">What people read in the notice.</p>
                        </div>

                        <label className={labelClass}>
                            Title
                            <input
                                className={fieldClass}
                                value={form.title}
                                onChange={(e) => setForm({ ...form, title: e.target.value })}
                                placeholder="e.g. Mid-term break — school closed"
                                required
                            />
                        </label>

                        <label className={labelClass}>
                            Description
                            <textarea
                                className={`${fieldClass} min-h-[160px] resize-y`}
                                rows={6}
                                value={form.description}
                                onChange={(e) => setForm({ ...form, description: e.target.value })}
                                placeholder="Full message for the notice board. You can include basic HTML if your site supports it."
                                required
                            />
                        </label>

                        <div>
                            <span className={labelClass}>Banner image (optional)</span>
                            <div className="mt-1.5 rounded-xl border border-dashed border-slate-200 bg-slate-50/80 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/30">
                                <input
                                    type="file"
                                    accept="image/*"
                                    className="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-indigo-700"
                                    onChange={(e) => setFile(e.target.files?.[0] || null)}
                                />
                                {edit && existingAttachmentId && !file ? (
                                    <p className="mt-2 text-xs text-slate-500">
                                        A file is already attached. Upload a new image to replace it; leave empty to keep the current one.
                                    </p>
                                ) : (
                                    <p className="mt-2 text-xs text-slate-500">PNG or JPG recommended. Max size depends on your server settings.</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-md shadow-slate-200/30">
                            <div className="mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                                <IconCalendar className="h-5 w-5 text-indigo-600" />
                                <h2 className="text-base font-semibold text-slate-900">Schedule</h2>
                            </div>
                            <div className="space-y-4">
                                <label className={labelClass}>
                                    Publish date &amp; time
                                    <input
                                        type="datetime-local"
                                        className={fieldClass}
                                        value={form.publish_date}
                                        onChange={(e) => setForm({ ...form, publish_date: e.target.value })}
                                        required
                                    />
                                    <span className="mt-1 block text-xs text-slate-500">When the notice becomes visible in the app.</span>
                                </label>
                                <label className={labelClass}>
                                    Notice date
                                    <input
                                        type="date"
                                        className={fieldClass}
                                        value={form.date}
                                        onChange={(e) => setForm({ ...form, date: e.target.value })}
                                        required
                                    />
                                    <span className="mt-1 block text-xs text-slate-500">Calendar date shown with the notice (e.g. event day).</span>
                                </label>
                            </div>
                        </div>

                        <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-md shadow-slate-200/30">
                            <h2 className="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Status &amp; website</h2>
                            <div className="mt-4 space-y-4">
                                <label className={labelClass}>
                                    Record status
                                    <select
                                        className={fieldClass}
                                        value={form.status}
                                        onChange={(e) => setForm({ ...form, status: Number(e.target.value) })}
                                    >
                                        <option value={1}>Active</option>
                                        <option value={0}>Inactive</option>
                                    </select>
                                </label>
                                <label className={labelClass}>
                                    Visible on public website
                                    <select
                                        className={fieldClass}
                                        value={form.is_visible_web}
                                        onChange={(e) => setForm({ ...form, is_visible_web: Number(e.target.value) })}
                                    >
                                        <option value={1}>Yes</option>
                                        <option value={0}>No</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <div className="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-md shadow-slate-200/30">
                            <h2 className="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Audience</h2>
                            <p className="mt-2 text-xs text-slate-500">Select which roles can see this notice in their panel.</p>
                            <div className="mt-4 max-h-56 space-y-2 overflow-y-auto pr-1">
                                {roles.length ? (
                                    roles.map((r) => {
                                        const checked = form.visible_to.map(Number).includes(Number(r.id));
                                        return (
                                            <label
                                                key={r.id}
                                                className={`flex cursor-pointer items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition ${
                                                    checked
                                                        ? 'border-indigo-200 bg-indigo-50/80 text-indigo-900'
                                                        : 'border-slate-100 bg-slate-50/50 text-slate-700 hover:border-slate-200'
                                                }`}
                                            >
                                                <input
                                                    type="checkbox"
                                                    className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                    checked={checked}
                                                    onChange={() => toggleRole(r.id)}
                                                />
                                                <span className="font-medium">{r.name}</span>
                                            </label>
                                        );
                                    })
                                ) : (
                                    <p className="text-sm text-slate-500">No roles available. Check staff roles in settings.</p>
                                )}
                            </div>
                        </div>

                        <div className="flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <Link
                                to="/communication/notice-board"
                                className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-center text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Cancel
                            </Link>
                            <UiButton type="submit" variant="primary" disabled={busy} className="min-w-[140px] rounded-xl py-2.5 shadow-lg shadow-indigo-500/25">
                                {busy ? (
                                    <>
                                        <UiInlineLoader />
                                        Saving…
                                    </>
                                ) : edit ? (
                                    'Save changes'
                                ) : (
                                    'Publish notice'
                                )}
                            </UiButton>
                        </div>
                    </div>
                </form>
            </div>
        </Shell>
    );
}
