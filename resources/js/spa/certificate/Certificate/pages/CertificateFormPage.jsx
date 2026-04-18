import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, paginateRows, splitTitle, buildDescriptionHtml } from '../../CertificateModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function CertificateFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({
        title: '',
        top_text: '',
        description: '',
        bottom_left_text: '',
        bottom_right_text: '',
        logo: true,
        name: true,
    });
    const [files, setFiles] = useState({});
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!edit) {
            axios.get('/certificate/create', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
            return;
        }
        axios
            .get(`/certificate/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const c = r.data?.data?.certificate ?? r.data?.data;
                setForm({
                    title: c?.title ?? '',
                    top_text: c?.top_text ?? '',
                    description: c?.description ?? '',
                    bottom_left_text: c?.bottom_left_text ?? '',
                    bottom_right_text: c?.bottom_right_text ?? '',
                    logo: !!c?.logo,
                    name: !!c?.name,
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
            fd.append('top_text', form.top_text);
            fd.append('description', form.description);
            fd.append('bottom_left_text', form.bottom_left_text);
            fd.append('bottom_right_text', form.bottom_right_text);
            fd.append('logo', form.logo ? 'on' : '');
            fd.append('name', form.name ? 'on' : '');
            if (files.bg_image) fd.append('bg_image', files.bg_image);
            if (files.bottom_left_signature) fd.append('bottom_left_signature', files.bottom_left_signature);
            if (files.bottom_right_signature) fd.append('bottom_right_signature', files.bottom_right_signature);

            if (edit) {
                fd.append('_method', 'PUT');
                await axios.post(`/certificate/update/${id}`, fd, {
                    headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
                });
            } else {
                await axios.post('/certificate/store', fd, {
                    headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
                });
            }
            nav('/certificate');
        } catch (ex) {
            const msg = ex.response?.data?.message || ex.response?.data?.errors;
            setErr(typeof msg === 'object' ? JSON.stringify(msg) : msg || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit certificate' : 'Create certificate')}</h1>
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
                    Top text
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.top_text}
                        onChange={(e) => setForm({ ...form, top_text: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Description (supports [student_name], [class_name], …)
                    <textarea
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        rows={4}
                        value={form.description}
                        onChange={(e) => setForm({ ...form, description: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Background image
                    <input
                        type="file"
                        accept="image/*"
                        className="mt-1 w-full text-sm"
                        onChange={(e) => setFiles({ ...files, bg_image: e.target.files?.[0] || null })}
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Bottom left text
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.bottom_left_text}
                        onChange={(e) => setForm({ ...form, bottom_left_text: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Bottom left signature
                    <input
                        type="file"
                        accept="image/*"
                        className="mt-1 w-full text-sm"
                        onChange={(e) => setFiles({ ...files, bottom_left_signature: e.target.files?.[0] || null })}
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Bottom right text
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.bottom_right_text}
                        onChange={(e) => setForm({ ...form, bottom_right_text: e.target.value })}
                        required
                    />
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Bottom right signature
                    <input
                        type="file"
                        accept="image/*"
                        className="mt-1 w-full text-sm"
                        onChange={(e) => setFiles({ ...files, bottom_right_signature: e.target.files?.[0] || null })}
                    />
                </label>
                <label className="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" checked={form.logo} onChange={(e) => setForm({ ...form, logo: e.target.checked })} />
                    Show school logo
                </label>
                <label className="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" checked={form.name} onChange={(e) => setForm({ ...form, name: e.target.checked })} />
                    Show student name
                </label>
                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={busy}
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {busy ? 'Saving…' : 'Save'}
                    </button>
                    <Link to="/certificate" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

