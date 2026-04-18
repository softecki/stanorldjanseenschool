import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { Shell, pickArray } from '../../PanelListModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function OnlineExamDetailPage({ Layout, endpoint }) {
    const [payload, setPayload] = useState(null);
    const [err, setErr] = useState('');

    useEffect(() => {
        axios
            .get(endpoint, { headers: xhrJson })
            .then((r) => setPayload(r.data?.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load exam.'));
    }, [endpoint]);

    const entries = Object.entries(payload || {});

    return (
        <Shell Layout={Layout} title="Online Exam Detail">
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <div className="rounded border bg-white p-4">
                {entries.length ? (
                    <div className="grid gap-2 text-sm">
                        {entries.map(([k, v]) => (
                            <div key={k} className="grid grid-cols-12 border-b py-2">
                                <div className="col-span-4 font-medium text-slate-700">{k}</div>
                                <div className="col-span-8 text-slate-800">{typeof v === 'object' ? JSON.stringify(v) : String(v)}</div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="text-sm text-slate-500">No detail available.</p>
                )}
            </div>
        </Shell>
    );
}

