import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, paginateRows } from '../../GenderModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function GendersTranslatePage({ Layout }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [langs, setLangs] = useState([]);
    const [names, setNames] = useState({});
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios
            .get(`/genders/translate/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const langsList = r.data?.data?.languages || [];
                setLangs(langsList);
                const tr = r.data?.data?.translates || {};
                const gender = r.data?.data?.gender;
                const n = {};
                langsList.forEach((l) => {
                    const code = l.code || String(l.id);
                    const row = tr[code]?.[0];
                    n[code] = row?.name ?? gender?.name ?? '';
                });
                setNames(n);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, [id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const fd = new FormData();
            langs.forEach((l) => {
                const code = l.code || String(l.id);
                fd.append(`name[${code}]`, names[code] ?? '');
            });
            await axios.post(`/genders/translate_update/${id}`, fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
            nav('/settings/genders');
        } catch (ex) {
            const msg = ex.response?.data?.message || ex.response?.data?.errors;
            setErr(typeof msg === 'object' ? JSON.stringify(msg) : msg || 'Save failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Translate gender'}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-2xl gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                {langs.map((l) => {
                    const code = l.code || String(l.id);
                    return (
                        <div key={code} className="space-y-2 rounded-lg border border-slate-100 p-3">
                            <p className="text-sm font-semibold text-slate-800">{l.name || code}</p>
                            <input
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                                placeholder="Name"
                                value={names[code] ?? ''}
                                onChange={(e) => setNames({ ...names, [code]: e.target.value })}
                            />
                        </div>
                    );
                })}
                <div className="flex gap-2">
                    <button
                        type="submit"
                        disabled={busy}
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {busy ? 'Saving…' : 'Save translations'}
                    </button>
                    <Link to="/settings/genders" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

