import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';

export function NoticeBoardTranslatePage({ Layout }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [langs, setLangs] = useState([]);
    const [titles, setTitles] = useState({});
    const [descriptions, setDescriptions] = useState({});
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios
            .get(`/communication/notice-board/translate/${id}`, { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                const langsList = r.data?.data?.languages || [];
                setLangs(langsList);
                const tr = r.data?.data?.translates || {};
                const nb = r.data?.data?.notice_board;
                const t = {};
                const d = {};
                langsList.forEach((l) => {
                    const code = l.code;
                    const row = tr[code]?.[0];
                    t[code] = row?.title ?? nb?.title ?? '';
                    d[code] = row?.description ?? nb?.description ?? '';
                });
                setTitles(t);
                setDescriptions(d);
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
                fd.append(`title[${code}]`, titles[code] ?? '');
                fd.append(`description[${code}]`, descriptions[code] ?? '');
            });
            fd.append('_method', 'PUT');
            await axios.post(`/communication/notice-board/translate/update/${id}`, fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
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
            <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Translate notice'}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-2xl gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                {langs.map((l) => {
                    const code = l.code || String(l.id);
                    return (
                        <div key={code} className="space-y-2 rounded-lg border border-slate-100 p-3">
                            <p className="text-sm font-semibold text-slate-800">{l.name || code}</p>
                            <input
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                                placeholder="Title"
                                value={titles[code] ?? ''}
                                onChange={(e) => setTitles({ ...titles, [code]: e.target.value })}
                            />
                            <textarea
                                className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                                placeholder="Description"
                                rows={3}
                                value={descriptions[code] ?? ''}
                                onChange={(e) => setDescriptions({ ...descriptions, [code]: e.target.value })}
                            />
                        </div>
                    );
                })}
                <div className="flex gap-2">
                    <button type="submit" disabled={busy} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        {busy ? 'Saving…' : 'Save translations'}
                    </button>
                    <Link to="/communication/notice-board" className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700">
                        Cancel
                    </Link>
                </div>
            </form>
        </Shell>
    );
}

/* --- SMS templates --- */

