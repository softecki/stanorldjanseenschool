import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../PanelProfileModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function PanelProfileViewPage({ Layout, title, endpoint }) {
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');

    useEffect(() => {
        axios.get(endpoint, { headers: xhrJson })
            .then((r) => setData(r.data?.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load profile.'));
    }, [endpoint]);

    return (
        <Shell Layout={Layout} title={title}>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <div className="rounded border bg-white p-4 text-sm">
                <div className="grid grid-cols-12 border-b py-2">
                    <span className="col-span-4 font-medium text-slate-700">Title</span>
                    <span className="col-span-8 text-slate-800">{data?.title || '-'}</span>
                </div>
            </div>
        </Shell>
    );
}

