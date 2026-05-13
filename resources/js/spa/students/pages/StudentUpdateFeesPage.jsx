import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';

const DOC_TYPES = [
    { value: '1', label: 'School fees' },
    { value: '2', label: 'Outstanding' },
    { value: '3', label: 'Transport' },
];

/** SPA replacement for Blade `student/updatefees` — uploads Excel via same POST as legacy. */
export function StudentUpdateFeesPage() {
    const [meta, setMeta] = useState({ title: 'Update Fees' });
    const [documentType, setDocumentType] = useState('');
    const [file, setFile] = useState(null);
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios
            .get('/student/updatefees', { headers: xhrJson })
            .then((r) => {
                if (r.data?.meta?.title) setMeta((m) => ({ ...m, title: r.data.meta.title }));
            })
            .catch(() => {});
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setMsg('');
        if (!documentType) {
            setErr('Select a document type.');
            return;
        }
        if (!file) {
            setErr('Choose an Excel (.xlsx, .xls) or CSV file.');
            return;
        }

        const fd = new FormData();
        fd.append('document_type', documentType);
        fd.append('document_files', file);

        setBusy(true);
        try {
            const res = await axios.post('/student/updateStudentFees', fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
            setMsg(res.data?.message || 'Fees file processed successfully.');
            setFile(null);
            setDocumentType('');
        } catch (ex) {
            const d = ex.response?.data;
            setErr(d?.message || d?.errors?.document_files?.[0] || d?.errors?.document_type?.[0] || 'Upload failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-3xl px-4 py-5 sm:px-6 lg:py-6">
                <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{meta.title}</h1>
                        <p className="mt-1 text-sm text-slate-600">
                            Upload spreadsheet to refresh student fees (same types and POST as legacy school form).
                        </p>
                    </div>
                    <Link to="/collections" className="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                        ← Back to collect
                    </Link>
                </div>

                {err ? (
                    <div className="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {msg ? (
                    <div className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        {msg}{' '}
                        <Link to="/collections" className="font-semibold text-emerald-800 underline-offset-2 hover:underline">
                            Open collect list
                        </Link>
                    </div>
                ) : null}

                <form onSubmit={submit} className="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div>
                        <label className="mb-2 block text-sm font-medium text-slate-700">Document type</label>
                        <select
                            className="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            value={documentType}
                            onChange={(e) => setDocumentType(e.target.value)}
                            required
                        >
                            <option value="">— Select document type —</option>
                            {DOC_TYPES.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="mb-2 block text-sm font-medium text-slate-700">Spreadsheet file</label>
                        <input
                            type="file"
                            accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            className="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                            onChange={(e) => setFile(e.target.files?.[0] || null)}
                        />
                        <p className="mt-2 text-xs text-slate-500">Allowed: xlsx, xls, csv (max ~2 MB).</p>
                    </div>
                    <div className="flex justify-end border-t border-slate-100 pt-4">
                        <button
                            type="submit"
                            disabled={busy}
                            className="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {busy ? 'Submitting…' : 'Submit'}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
