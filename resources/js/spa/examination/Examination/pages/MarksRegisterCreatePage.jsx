import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, readPaginate } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function MarksRegisterCreatePage({ Layout }) {
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [classId, setClassId] = useState('');
    const [examType, setExamType] = useState('');
    const [subject, setSubject] = useState('');
    const [file, setFile] = useState(null);
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios.get('/marks-register/create', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const fd = new FormData();
            fd.append('class', classId);
            fd.append('exam_type', examType);
            fd.append('subject', subject);
            if (file) fd.append('document_files', file);
            await axios.post('/marks-register/store', fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
            nav('/examination/marks-register');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    const classes = meta.classes || [];
    const examTypes = meta.exam_types || [];
    const subjects = meta.subjects || [];

    return (
        <Shell Layout={Layout}>
            <Link to="/examination/marks-register" className="text-sm text-blue-600 hover:text-blue-800">
                ← Marks register
            </Link>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Create marks register'}</h1>
            <p className="text-sm text-slate-500">Upload an Excel/CSV file with columns matching the school import (e.g. reg_number, marks).</p>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-lg gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className="text-sm font-medium text-slate-700">
                    Class
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={classId}
                        onChange={(e) => setClassId(e.target.value)}
                        required
                    >
                        <option value="">—</option>
                        {classes.map((item) => {
                            const c = item.class || item;
                            const cid = c.id || item.classes_id;
                            return (
                                <option key={cid} value={cid}>
                                    {c.name}
                                </option>
                            );
                        })}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Exam type
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={examType}
                        onChange={(e) => setExamType(e.target.value)}
                        required
                    >
                        <option value="">—</option>
                        {examTypes.map((et) => (
                            <option key={et.id} value={et.id}>
                                {et.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Subject
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={subject}
                        onChange={(e) => setSubject(e.target.value)}
                        required
                    >
                        <option value="">—</option>
                        {subjects.map((s) => (
                            <option key={s.id} value={s.id}>
                                {s.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Spreadsheet
                    <input
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        className="mt-1 w-full text-sm"
                        onChange={(e) => setFile(e.target.files?.[0] || null)}
                        required
                    />
                </label>
                <button type="submit" disabled={busy} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                    {busy ? 'Uploading…' : 'Submit'}
                </button>
            </form>
        </Shell>
    );
}

