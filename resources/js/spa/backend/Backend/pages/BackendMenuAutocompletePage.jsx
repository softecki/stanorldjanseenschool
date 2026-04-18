import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BackendModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function BackendMenuAutocompletePage({ Layout }) {
    const [q, setQ] = useState('');
    const [rows, setRows] = useState([]);
    const search = async (value) => {
        const { data } = await axios.post('/search-menu-data', { search: value }, { headers: xhrJson });
        setRows(Array.isArray(data) ? data : []);
    };
    return (
        <Shell Layout={Layout} title="Backend Menu Autocomplete">
            <input className="w-full rounded border px-3 py-2" value={q} onChange={(e) => { const v = e.target.value; setQ(v); search(v); }} placeholder="Search menu..." />
            <div className="rounded border bg-white p-4 text-sm">
                {rows.map((r, i) => <div key={i} className="border-b py-2">{r.title} - {r.route_name}</div>)}
                {!rows.length ? <p className="text-slate-500">Type to search menus.</p> : null}
            </div>
        </Shell>
    );
}

