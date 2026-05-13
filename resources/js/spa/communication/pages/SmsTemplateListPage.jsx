import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateState } from '../CommunicationModuleShared';
import {
    IconPlus,
    UiActionGroup,
    UiButton,
    UiButtonLink,
    UiHeadRow,
    UiInlineLoader,
    UiPageLoader,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../ui/UiKit';

function formatApiError(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (d.errors && typeof d.errors === 'object') {
        const parts = [];
        Object.keys(d.errors).forEach((k) => {
            const v = d.errors[k];
            parts.push(`${k}: ${Array.isArray(v) ? v.join(' ') : v}`);
        });
        return parts.length ? parts.join(' · ') : 'Validation failed.';
    }
    return 'Request failed.';
}

export function SmsTemplateListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [page, setPage] = useState(1);
    const [lastPage, setLastPage] = useState(1);
    const [total, setTotal] = useState(0);
    const [from, setFrom] = useState(null);
    const [to, setTo] = useState(null);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [loading, setLoading] = useState(true);
    const [deliveryBusy, setDeliveryBusy] = useState(false);
    const [deleteId, setDeleteId] = useState(null);

    const load = useCallback((p = 1) => {
        setLoading(true);
        setErr('');
        const q = p > 1 ? `?page=${p}` : '';
        return axios
            .get(`/communication/template${q}`, { headers: xhrJson })
            .then((r) => {
                const st = paginateState(r);
                setRows(st.rows);
                setPage(st.page);
                setLastPage(st.lastPage);
                setTotal(st.total);
                setFrom(st.from);
                setTo(st.to);
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(formatApiError(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load(1);
    }, [load]);

    const remove = async (tid) => {
        if (!window.confirm('Delete this template?')) return;
        setErr('');
        setDeleteId(tid);
        try {
            await axios.delete(`/communication/template/delete/${tid}`, { headers: xhrJson });
            await load(page);
            setInfo('Template deleted.');
            setTimeout(() => setInfo(''), 4000);
        } catch (ex) {
            setErr(formatApiError(ex));
        } finally {
            setDeleteId(null);
        }
    };

    const runDelivery = async () => {
        setErr('');
        setInfo('');
        setDeliveryBusy(true);
        try {
            const { data } = await axios.get('/communication/template/delivery', { headers: xhrJson });
            const m = data?.message || 'Delivery sync completed.';
            const ok = data?.updated === true;
            setInfo(ok ? `${m} Tracking rows were updated where possible.` : `${m} No remote report rows to apply.`);
        } catch (ex) {
            setErr(formatApiError(ex));
        } finally {
            setDeliveryBusy(false);
        }
    };

    return (
        <Shell Layout={Layout}>
            <div className="space-y-8">
                <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-indigo-900 to-violet-900 px-6 py-9 text-white shadow-xl sm:px-10">
                    <div className="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden />
                    <div className="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-200/90">Communication</p>
                            <h1 className="mt-1 text-3xl font-bold tracking-tight sm:text-4xl">{meta.title || 'SMS / Mail templates'}</h1>
                            <p className="mt-2 max-w-xl text-sm text-indigo-100/90">
                                Reusable bodies for SMS/Mail sends. Sync delivery pulls status from the messaging provider into{' '}
                                <code className="rounded bg-white/15 px-1.5 py-0.5 text-xs">sms_tracking</code>.
                            </p>
                        </div>
                        <div className="flex flex-shrink-0 flex-wrap gap-2">
                            <UiButton
                                type="button"
                                variant="secondary"
                                disabled={deliveryBusy}
                                onClick={runDelivery}
                                className="border-white/30 bg-white/10 text-white hover:bg-white/20"
                            >
                                {deliveryBusy ? (
                                    <>
                                        <UiInlineLoader /> Syncing…
                                    </>
                                ) : (
                                    'Sync delivery reports'
                                )}
                            </UiButton>
                            <UiButtonLink
                                to="/communication/template/create"
                                className="border-0 bg-white text-indigo-900 shadow-lg hover:bg-indigo-50"
                                leftIcon={<IconPlus className="h-4 w-4" />}
                            >
                                Create template
                            </UiButtonLink>
                        </div>
                    </div>
                </section>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {info ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        {info}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading templates…" />
                ) : (
                    <>
                        <div className="rounded-2xl border border-slate-200/80 bg-white/90 p-1 shadow-lg shadow-slate-200/50">
                            <UiTableWrap className="overflow-hidden rounded-xl border-0 shadow-none">
                                <UiTable>
                                    <UiTHead className="bg-slate-50/90">
                                        <UiHeadRow>
                                            <UiTH>Title</UiTH>
                                            <UiTH className="w-28">Type</UiTH>
                                            <UiTH className="text-right">Actions</UiTH>
                                        </UiHeadRow>
                                    </UiTHead>
                                    <UiTBody>
                                        {rows.length ? (
                                            rows.map((row) => (
                                                <UiTR key={row.id}>
                                                    <UiTD className="font-semibold text-slate-900">{row.title}</UiTD>
                                                    <UiTD>
                                                        <span
                                                            className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide ${
                                                                row.type === 'sms'
                                                                    ? 'bg-sky-100 text-sky-800'
                                                                    : 'bg-violet-100 text-violet-800'
                                                            }`}
                                                        >
                                                            {row.type || '—'}
                                                        </span>
                                                    </UiTD>
                                                    <UiTD className="text-right">
                                                        <UiActionGroup
                                                            editTo={`/communication/template/${row.id}/edit`}
                                                            onDelete={() => remove(row.id)}
                                                            busy={deleteId === row.id}
                                                        />
                                                    </UiTD>
                                                </UiTR>
                                            ))
                                        ) : (
                                            <UiTableEmptyRow colSpan={3} message="No templates yet. Create one for fee reminders or mail merges." />
                                        )}
                                    </UiTBody>
                                </UiTable>
                            </UiTableWrap>
                        </div>

                        {total > 0 && from != null && to != null ? (
                            <p className="text-center text-xs text-slate-500">
                                Showing <span className="font-medium text-slate-700">{from}</span>–
                                <span className="font-medium text-slate-700">{to}</span> of{' '}
                                <span className="font-medium text-slate-700">{total}</span>
                            </p>
                        ) : null}

                        <UiPager
                            page={page}
                            lastPage={lastPage}
                            onPrev={() => load(page - 1)}
                            onNext={() => load(page + 1)}
                            className="rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3"
                        />
                    </>
                )}
            </div>
        </Shell>
    );
}
