import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

export function StudentUploadPage() {
    const nav = useNavigate();
    const [form, setForm] = useState({ document_format: '1', document_files: null });
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const [busy, setBusy] = useState(false);
    const submit = async (e) => {
        e.preventDefault();
        if (!form.document_files) {
            setErr('Please select a file to upload.');
            return;
        }
        setErr('');
        setMsg('');
        setBusy(true);
        const fd = new FormData();
        fd.append('document_format', form.document_format);
        fd.append('document_files', form.document_files);
        try {
            await axios.post('/student/uploadStudent', fd, { headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' } });
            setMsg('Upload completed successfully.');
            setForm((f) => ({ ...f, document_files: null }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Upload failed.');
        } finally {
            setBusy(false);
        }
    };
    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">Upload Students Details</h1>
                        <button type="button" onClick={() => nav('/students')} className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Back</button>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {msg ? <p className="mb-3 text-sm text-emerald-600">{msg}</p> : null}
                <form onSubmit={submit} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm md:grid-cols-[220px_1fr_auto]">
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.document_format} onChange={(e) => setForm((f) => ({ ...f, document_format: e.target.value }))}>
                        <option value="1">Format 1</option>
                        <option value="2">Format 2</option>
                    </select>
                    <input type="file" accept=".xlsx,.xls,.csv" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" onChange={(e) => setForm((f) => ({ ...f, document_files: e.target.files?.[0] || null }))} />
                    <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60" disabled={busy}>
                        {busy ? 'Uploading...' : 'Submit'}
                    </button>
                </form>
                <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    <p><strong>Format 1:</strong> includes fees assignment.</p>
                    <p><strong>Format 2:</strong> class-only student import (no fees assignment).</p>
                    <a href="/student/download-template" className="mt-2 inline-flex rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-medium text-amber-700 transition hover:bg-amber-100">
                        Download Students Excel Format
                    </a>
                </div>
            </div>
        </AdminLayout>
    );
}

