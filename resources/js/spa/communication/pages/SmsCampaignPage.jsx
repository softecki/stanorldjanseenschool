import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell } from '../CommunicationModuleShared';
import { UiButton, UiInlineLoader, UiPageLoader } from '../../ui/UiKit';

function formatAxiosMessage(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (d.message && typeof d.message === 'object') return JSON.stringify(d.message);
    return 'Request failed.';
}

function formatCampaignDate(value) {
    if (!value) return '—';
    try {
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return String(value);
        return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
    } catch {
        return String(value);
    }
}

export function SmsCampaignPage({ Layout }) {
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const [loading, setLoading] = useState(true);
    const [sendBusy, setSendBusy] = useState(false);
    const [retryBusy, setRetryBusy] = useState(false);

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/communication/smsmail/campaign', { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data ?? null);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(formatAxiosMessage(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const send = async () => {
        setMsg('');
        setErr('');
        setSendBusy(true);
        try {
            const { data: r } = await axios.post('/communication/smsmail/campaign/send', new FormData(), { headers: xhrJson });
            if (r?.status === false) {
                setErr(r?.message || 'Campaign was not run.');
                return;
            }
            setMsg(r?.message || 'Campaign finished.');
            await load();
        } catch (ex) {
            setErr(formatAxiosMessage(ex));
        } finally {
            setSendBusy(false);
        }
    };

    const retry = async () => {
        setMsg('');
        setErr('');
        setRetryBusy(true);
        try {
            const { data: r } = await axios.post('/communication/smsmail/campaign/retry', new FormData(), { headers: xhrJson });
            if (r?.status === false) {
                setErr(r?.message || 'Retry did not complete.');
                return;
            }
            setMsg(r?.message || 'Retry finished.');
            await load();
        } catch (ex) {
            setErr(formatAxiosMessage(ex));
        } finally {
            setRetryBusy(false);
        }
    };

    const lc = data?.last_campaign;
    const pending = data?.failed_sms_count ?? 0;

    return (
        <Shell Layout={Layout}>
            <div className="mx-auto max-w-3xl space-y-8">
                <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-teal-700 to-cyan-800 px-6 py-9 text-white shadow-xl sm:px-10">
                    <div className="pointer-events-none absolute -right-16 top-0 h-56 w-56 rounded-full bg-white/10 blur-3xl" aria-hidden />
                    <div className="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100/90">Communication</p>
                            <h1 className="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{meta.title || data?.title || 'SMS campaign'}</h1>
                            <p className="mt-3 max-w-xl text-sm leading-relaxed text-emerald-50/95">
                                Fee-reminder campaign: finds guardians with unpaid quarter amounts for the current quarter (per server rules), sends SMS
                                through the configured gateway, and logs outcomes. <strong className="text-white">Runs only on Fridays</strong> (other days
                                return a clear error).
                            </p>
                        </div>
                        <Link
                            to="/communication/smsmail"
                            className="inline-flex shrink-0 items-center justify-center rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"
                        >
                            Back to SMS log
                        </Link>
                    </div>
                </section>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {msg ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        {msg}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading campaign status…" />
                ) : data ? (
                    <div className="space-y-6 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg sm:p-8">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="rounded-xl border border-amber-100 bg-amber-50/80 p-4">
                                <p className="text-xs font-semibold uppercase tracking-wide text-amber-900/80">Failed SMS (pending retry)</p>
                                <p className="mt-1 text-3xl font-bold tabular-nums text-amber-950">{pending}</p>
                                <p className="mt-2 text-xs text-amber-900/80">Rows in <code className="rounded bg-amber-100/80 px-1">failed_sms</code> with is_sent = 0.</p>
                            </div>
                            <div className="rounded-xl border border-slate-100 bg-slate-50/90 p-4">
                                <p className="text-xs font-semibold uppercase tracking-wide text-slate-600">Last campaign run</p>
                                {lc ? (
                                    <dl className="mt-2 space-y-1 text-sm text-slate-800">
                                        <div className="flex justify-between gap-2">
                                            <dt className="text-slate-500">When</dt>
                                            <dd className="font-medium">{formatCampaignDate(lc.created_at)}</dd>
                                        </div>
                                        {lc.quarter != null ? (
                                            <div className="flex justify-between gap-2">
                                                <dt className="text-slate-500">Quarter</dt>
                                                <dd className="font-medium">Q{lc.quarter}</dd>
                                            </div>
                                        ) : null}
                                        {lc.total_students != null ? (
                                            <div className="flex justify-between gap-2">
                                                <dt className="text-slate-500">Students (query)</dt>
                                                <dd className="font-medium tabular-nums">{lc.total_students}</dd>
                                            </div>
                                        ) : null}
                                        {lc.sent_count != null ? (
                                            <div className="flex justify-between gap-2">
                                                <dt className="text-slate-500">Sent</dt>
                                                <dd className="font-medium tabular-nums text-emerald-700">{lc.sent_count}</dd>
                                            </div>
                                        ) : null}
                                        {lc.failed_count != null ? (
                                            <div className="flex justify-between gap-2">
                                                <dt className="text-slate-500">Failed</dt>
                                                <dd className="font-medium tabular-nums text-rose-700">{lc.failed_count}</dd>
                                            </div>
                                        ) : null}
                                    </dl>
                                ) : (
                                    <p className="mt-2 text-sm text-slate-600">No campaign has been logged yet in <code className="text-xs">sms_campaign_logs</code>.</p>
                                )}
                            </div>
                        </div>

                        <div className="flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:flex-wrap">
                            <UiButton
                                type="button"
                                variant="primary"
                                disabled={sendBusy}
                                onClick={send}
                                className="rounded-xl bg-emerald-600 shadow-lg shadow-emerald-600/25 hover:bg-emerald-700"
                            >
                                {sendBusy ? (
                                    <>
                                        <UiInlineLoader /> Running campaign…
                                    </>
                                ) : (
                                    'Run campaign send'
                                )}
                            </UiButton>
                            <UiButton type="button" variant="secondary" disabled={retryBusy || pending === 0} onClick={retry} className="rounded-xl">
                                {retryBusy ? (
                                    <>
                                        <UiInlineLoader /> Retrying…
                                    </>
                                ) : (
                                    'Retry failed (up to 100)'
                                )}
                            </UiButton>
                        </div>
                        {pending === 0 ? <p className="text-xs text-slate-500">Retry is disabled while there are zero pending failed messages.</p> : null}
                    </div>
                ) : (
                    <p className="text-center text-sm text-slate-600">No campaign data returned.</p>
                )}
            </div>
        </Shell>
    );
}
