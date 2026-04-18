import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { Shell, pickArray } from '../../PanelListModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function PanelListPage({ Layout, title, endpoint, preferredKey, detailBase }) {
    const [payload, setPayload] = useState(null);
    const [err, setErr] = useState('');

    useEffect(() => {
        axios
            .get(endpoint, { headers: xhrJson })
            .then((r) => setPayload(r.data?.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'));
    }, [endpoint]);

    const rows = useMemo(() => pickArray(payload, preferredKey), [payload, preferredKey]);

    return (
        <Shell Layout={Layout} title={title}>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <div className="rounded border bg-white">
                <div className="grid grid-cols-12 border-b bg-slate-50 px-4 py-2 text-xs font-semibold uppercase text-slate-600">
                    <div className="col-span-1">#</div>
                    <div className="col-span-8">Title</div>
                    <div className="col-span-3 text-right">Action</div>
                </div>
                {rows.map((row, i) => {
                    const label = row?.title || row?.name || row?.subject || row?.book_name || row?.exam_name || `Item ${i + 1}`;
                    return (
                        <div key={row?.id || i} className="grid grid-cols-12 items-center border-b px-4 py-3 text-sm">
                            <div className="col-span-1 text-slate-500">{i + 1}</div>
                            <div className="col-span-8 text-slate-800">{label}</div>
                            <div className="col-span-3 text-right">
                                {detailBase && row?.id ? (
                                    <Link className="text-blue-700 hover:text-blue-900" to={`${detailBase}/${row.id}`}>
                                        View
                                    </Link>
                                ) : (
                                    <span className="text-slate-400">-</span>
                                )}
                            </div>
                        </div>
                    );
                })}
                {!rows.length ? <p className="p-6 text-center text-sm text-slate-500">No records found.</p> : null}
            </div>
        </Shell>
    );
}

