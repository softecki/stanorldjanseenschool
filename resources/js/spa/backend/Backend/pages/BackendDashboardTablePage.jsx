import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BackendModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function BackendDashboardTablePage({ Layout }) {
    const [term, setTerm] = useState('monthly');
    const [rows, setRows] = useState([]);
    const load = async (t) => {
        const { data } = await axios.get(`/dashboardUpdate/${t}`, { headers: xhrJson });
        setRows(data?.data?.collection_summary || []);
    };
    useEffect(() => { load(term); }, []); // eslint-disable-line react-hooks/exhaustive-deps
    return (
        <Shell Layout={Layout} title="Backend Dashboard Table">
            <div className="flex gap-2">
                {['daily', 'weekly', 'monthly', 'yearly'].map((t) => (
                    <button key={t} className={`rounded px-3 py-1 text-sm ${term === t ? 'bg-blue-600 text-white' : 'border'}`} onClick={() => { setTerm(t); load(t); }}>
                        {t}
                    </button>
                ))}
            </div>
            <div className="rounded border bg-white p-4 text-sm">
                {Array.isArray(rows) && rows.length ? rows.map((r, i) => <div key={i} className="border-b py-2">{JSON.stringify(r)}</div>) : <p className="text-slate-500">No summary rows.</p>}
            </div>
        </Shell>
    );
}

