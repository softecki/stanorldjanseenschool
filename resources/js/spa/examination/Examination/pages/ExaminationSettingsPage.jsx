import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, readPaginate } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function ExaminationSettingsPage({ Layout }) {
    const [meta, setMeta] = useState({});
    const [value, setValue] = useState('');
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios.get('/examination-settings', { headers: xhrJson }).then((r) => {
            const m = r.data?.meta || {};
            setMeta(m);
            setValue(m.average_pass_marks != null ? String(m.average_pass_marks) : '');
        });
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            await axios.put(
                '/examination-settings/update',
                { fields: ['average_pass_marks'], values: [value] },
                { headers: xhrJson },
            );
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Update failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <Link to="/examination" className="text-sm text-blue-600 hover:text-blue-800">
                ← Examination
            </Link>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Examination settings'}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-lg gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label className="text-sm font-medium text-slate-700">
                    Average pass marks
                    <input
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={value}
                        onChange={(e) => setValue(e.target.value)}
                    />
                </label>
                <button
                    type="submit"
                    disabled={busy}
                    className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                >
                    {busy ? 'Saving…' : 'Save'}
                </button>
            </form>
        </Shell>
    );
}

/* ——— Exam assign ——— */

