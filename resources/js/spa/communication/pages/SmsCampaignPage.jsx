import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';

export function SmsCampaignPage({ Layout }) {
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const load = () =>
        axios
            .get('/communication/smsmail/campaign', { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data || null);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));

    useEffect(() => {
        load();
    }, []);

    const send = async () => {
        setMsg('');
        try {
            const { data: r } = await axios.post('/communication/smsmail/campaign/send', new FormData(), { headers: xhrJson });
            setMsg(r?.message || 'Request completed.');
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Send failed.');
        }
    };

    const retry = async () => {
        setMsg('');
        try {
            const { data: r } = await axios.post('/communication/smsmail/campaign/retry', new FormData(), { headers: xhrJson });
            setMsg(r?.message || 'Retry completed.');
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Retry failed.');
        }
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'SMS campaign'}</h1>
                </div>
                <Link to="/communication/smsmail" className="text-sm text-blue-600 hover:text-blue-800">
                    Back to log
                </Link>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            {msg ? <p className="text-sm text-emerald-700">{msg}</p> : null}
            {data ? (
                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

                    <p className="text-sm text-slate-700">

                        Failed SMS pending: <strong>{data.failed_sms_count}</strong>
                    </p>
                    {data.last_campaign ? (
                        <p className="mt-2 text-xs text-slate-500">Last campaign: {String(data.last_campaign.created_at)}</p>
                    ) : null}
                    <div className="mt-4 flex flex-wrap gap-2">
                        <button type="button" onClick={send} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Run campaign send
                        </button>
                        <button type="button" onClick={retry} className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            Retry failed
                        </button>
                    </div>
                </div>
            ) : null}
        </Shell>
    );
}

