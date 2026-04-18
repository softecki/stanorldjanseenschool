import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, readPaginate } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function MarksGradesFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({ name: '', point: '', percent_from: '', percent_upto: '' });
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!edit) {
            axios.get('/marks-grade/create', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
            return;
        }
        axios
            .get(`/marks-grade/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const mg = r.data?.data?.marks_grade ?? r.data?.data;
                setForm({
                    name: mg?.name ?? '',
                    point: mg?.point ?? '',
                    percent_from: mg?.percent_from ?? '',
                    percent_upto: mg?.percent_upto ?? '',
                });
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            if (edit) await axios.put(`/marks-grade/update/${id}`, { ...form, id }, { headers: xhrJson });
            else await axios.post('/marks-grade/store', form, { headers: xhrJson });
            nav('/examination/marks-grades');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex items-center gap-3">
                <Link to="/examination/marks-grades" className="text-sm text-blue-600 hover:text-blue-800">
                    ← Marks grades
                </Link>
            </div>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit marks grade' : 'Create marks grade')}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-lg gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                {['name', 'point', 'percent_from', 'percent_upto'].map((k) => (
                    <label key={k} className="text-sm font-medium text-slate-700">
                        {k.replace(/_/g, ' ')}
                        <input
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            value={form[k]}
                            onChange={(e) => setForm({ ...form, [k]: e.target.value })}
                            required
                        />
                    </label>
                ))}
                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={busy}
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        {busy ? 'Saving…' : 'Save'}
                    </button>
                    <Link to="/examination/marks-grades" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

/* ——— Examination settings ——— */

