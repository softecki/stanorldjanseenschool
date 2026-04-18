import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';

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
    const [file, setFile] = useState(null);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!edit) {
            axios.get('/communication/notice-board/create', { headers: xhrJson }).then((r) => {
                setMeta(r.data?.meta || {});
                setRoles(r.data?.meta?.roles || []);
            });
            return;
        }
        axios
            .get(`/communication/notice-board/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const nb = r.data?.data?.['notice-board'] ?? r.data?.data;
                setRoles(r.data?.data?.roles || r.data?.meta?.roles || []);
                setForm({
                    title: nb?.title ?? '',
                    publish_date: nb?.publish_date ? nb.publish_date.slice(0, 16) : '',
                    date: nb?.date ?? '',
                    description: nb?.description ?? '',
                    status: nb?.status ?? 1,
                    is_visible_web: nb?.is_visible_web ? 1 : 0,
                    visible_to: Array.isArray(nb?.visible_to) ? nb.visible_to : [],
                });
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [edit, id]);

    const toggleRole = (rid) => {
        const id = Number(rid);
        setForm((f) => {
            const set = new Set((f.visible_to || []).map(Number));
            if (set.has(id)) set.delete(id);
            else set.add(id);
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
            if (edit) {
                fd.append('_method', 'PUT');
                await axios.post(`/communication/notice-board/update/${id}`, fd, {
                    headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
                });
            } else {
                await axios.post('/communication/notice-board/store', fd, {
                    headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
                });
            }
            nav('/communication/notice-board');
        } catch (ex) {
            const msg = ex.response?.data?.message || ex.response?.data?.errors;
            setErr(typeof msg === 'object' ? JSON.stringify(msg) : msg || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit notice' : 'Create notice')}</h1>
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
                    Publish date
                    <input
                        type="datetime-local"
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.publish_date}
                        onChange={(e) => setForm({ ...form, publish_date: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Date
                    <input
                        type="date"
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.date}
                        onChange={(e) => setForm({ ...form, date: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Description
                    <textarea
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        rows={5}
                        value={form.description}
                        onChange={(e) => setForm({ ...form, description: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Attachment
                    <input type="file" accept="image/*" className="mt-1 w-full text-sm" onChange={(e) => setFile(e.target.files?.[0] || null)} />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Status
                    <select className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" value={form.status} onChange={(e) => setForm({ ...form, status: Number(e.target.value) })}>
                        <option value={1}>Active</option>
                        <option value={0}>Inactive</option>
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Visible on website
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.is_visible_web}
                        onChange={(e) => setForm({ ...form, is_visible_web: Number(e.target.value) })}
                    >
                        <option value={1}>Yes</option>
                        <option value={0}>No</option>
                    </select>
                </label>
                <div>
                    <p className="text-sm font-medium text-slate-700">Visible to roles</p>
                    <div className="mt-2 flex flex-wrap gap-3">
                        {roles.map((r) => (
                            <label key={r.id} className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={form.visible_to.includes(r.id)} onChange={() => toggleRole(r.id)} />
                                {r.name}
                            </label>
                        ))}
                    </div>
                </div>
                <div className="flex gap-2">
                    <button type="submit" disabled={busy} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        {busy ? 'Saving…' : 'Save'}
                    </button>
                    <Link to="/communication/notice-board" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

