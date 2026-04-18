import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell } from '../../ReligionModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function ReligionsTranslatePage({ Layout }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [languages, setLanguages] = useState([]);
    const [names, setNames] = useState({});

    useEffect(() => {
        axios.get(`/religions/translate/${id}`, { headers: xhrJson }).then((r) => {
            const data = r.data?.data || {};
            const langs = data.languages || [];
            const trans = data.translates || {};
            const base = data.religion || {};
            setLanguages(langs);
            const n = {};
            langs.forEach((l) => {
                const code = l.code || String(l.id);
                n[code] = trans[code]?.[0]?.name || base.name || '';
            });
            setNames(n);
        });
    }, [id]);

    const submit = async (e) => {
        e.preventDefault();
        await axios.post(`/religions/translate_update/${id}`, { name: names }, { headers: xhrJson });
        nav('/settings/religions');
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold">Translate religion</h1>
            <form onSubmit={submit} className="grid max-w-xl gap-3 rounded border bg-white p-4">
                {languages.map((l) => {
                    const code = l.code || String(l.id);
                    return <input key={code} className="rounded border px-3 py-2" placeholder={`Name (${code})`} value={names[code] || ''} onChange={(e) => setNames({ ...names, [code]: e.target.value })} />;
                })}
                <button className="rounded bg-blue-600 px-3 py-2 text-white">Save translations</button>
            </form>
        </Shell>
    );
}

