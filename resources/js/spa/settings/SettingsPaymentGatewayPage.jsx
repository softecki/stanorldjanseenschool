import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import { UiButton, UiInlineLoader, UiPageLoader } from '../ui/UiKit';

function rowsToMap(rows) {
    const m = {};
    if (!Array.isArray(rows)) return m;
    rows.forEach((r) => {
        if (r && r.name != null) m[String(r.name)] = r.value != null ? String(r.value) : '';
    });
    return m;
}

function formatErr(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (d.errors && typeof d.errors === 'object') {
        return Object.entries(d.errors)
            .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(' ') : v}`)
            .join(' · ');
    }
    return 'Request failed.';
}

const fieldClass =
    'mt-1 w-full rounded-xl border border-slate-200/90 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-inner shadow-slate-100/80 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20';

const labelClass = 'block text-sm font-medium text-slate-700';

function GatewayTile({ active, title, description, badge, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`flex flex-col rounded-2xl border-2 p-5 text-left transition ${
                active
                    ? 'border-emerald-500 bg-gradient-to-br from-emerald-50 to-white shadow-md ring-2 ring-emerald-500/30'
                    : 'border-slate-200/90 bg-white/80 hover:border-emerald-200 hover:bg-emerald-50/30'
            }`}
        >
            <div className="flex items-center justify-between gap-2">
                <span className="text-lg font-bold tracking-tight text-slate-900">{title}</span>
                {badge ? (
                    <span className="rounded-full bg-slate-900/90 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">{badge}</span>
                ) : null}
            </div>
            <p className="mt-2 text-sm leading-relaxed text-slate-600">{description}</p>
        </button>
    );
}

function ModePill({ active, children, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`rounded-full px-4 py-2 text-sm font-semibold transition ${
                active ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
            }`}
        >
            {children}
        </button>
    );
}

export function SettingsPaymentGatewayPage() {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [meta, setMeta] = useState({ title: 'Payment gateway', can_update: true, app_demo: false });
    const [gateway, setGateway] = useState('Stripe');
    const [paypalMode, setPaypalMode] = useState('Sandbox');
    const [form, setForm] = useState({
        stripe_key: '',
        stripe_secret: '',
        paypal_sandbox_api_username: '',
        paypal_sandbox_api_password: '',
        paypal_sandbox_api_secret: '',
        paypal_sandbox_api_certificate: '',
        paypal_live_api_username: '',
        paypal_live_api_password: '',
        paypal_live_api_secret: '',
        paypal_live_api_certificate: '',
    });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/payment-gateway-setting', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                const demo = Boolean(m.app_demo);
                setMeta({
                    title: m.title || 'Payment gateway settings',
                    can_update: m.can_update !== false,
                    app_demo: demo,
                });
                const map = rowsToMap(Array.isArray(m.data) ? m.data : m.data?.data || []);
                const pg = map.payment_gateway === 'PayPal' ? 'PayPal' : 'Stripe';
                setGateway(pg);
                setPaypalMode(map.paypal_payment_mode === 'Live' ? 'Live' : 'Sandbox');
                setForm({
                    stripe_key: demo ? '' : map.stripe_key || '',
                    stripe_secret: demo ? '' : map.stripe_secret || '',
                    paypal_sandbox_api_username: demo ? '' : map.paypal_sandbox_api_username || '',
                    paypal_sandbox_api_password: demo ? '' : map.paypal_sandbox_api_password || '',
                    paypal_sandbox_api_secret: demo ? '' : map.paypal_sandbox_api_secret || '',
                    paypal_sandbox_api_certificate: demo ? '' : map.paypal_sandbox_api_certificate || '',
                    paypal_live_api_username: map.paypal_live_api_username || '',
                    paypal_live_api_password: map.paypal_live_api_password || '',
                    paypal_live_api_secret: map.paypal_live_api_secret || '',
                    paypal_live_api_certificate: map.paypal_live_api_certificate || '',
                });
            })
            .catch((ex) => setErr(formatErr(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const payload = useMemo(() => {
        const base = { payment_gateway: gateway };
        if (gateway === 'Stripe') {
            return { ...base, stripe_key: form.stripe_key, stripe_secret: form.stripe_secret };
        }
        return {
            ...base,
            paypal_payment_mode: paypalMode,
            ...(paypalMode === 'Sandbox'
                ? {
                      paypal_sandbox_api_username: form.paypal_sandbox_api_username,
                      paypal_sandbox_api_password: form.paypal_sandbox_api_password,
                      paypal_sandbox_api_secret: form.paypal_sandbox_api_secret,
                      paypal_sandbox_api_certificate: form.paypal_sandbox_api_certificate,
                  }
                : {
                      paypal_live_api_username: form.paypal_live_api_username,
                      paypal_live_api_password: form.paypal_live_api_password,
                      paypal_live_api_secret: form.paypal_live_api_secret,
                      paypal_live_api_certificate: form.paypal_live_api_certificate,
                  }),
        };
    }, [gateway, paypalMode, form]);

    const save = async (e) => {
        e.preventDefault();
        if (!meta.can_update) return;
        setSaving(true);
        setErr('');
        try {
            await axios.post('/payment-gateway-setting', payload, {
                headers: { ...xhrJson, 'Content-Type': 'application/json' },
            });
            setInfo('Payment gateway settings saved.');
            setTimeout(() => setInfo(''), 4000);
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-4xl space-y-8 p-6 pb-12">
                <section className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-emerald-950 to-slate-900 px-6 py-10 text-white shadow-2xl sm:px-10">
                    <div className="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-emerald-500/20 blur-3xl" aria-hidden />
                    <div className="pointer-events-none absolute -bottom-16 -left-16 h-56 w-56 rounded-full bg-teal-400/15 blur-3xl" aria-hidden />
                    <div className="relative">
                        <Link to="/settings" className="text-xs font-semibold uppercase tracking-wide text-emerald-200/90 hover:text-white">
                            ← Settings
                        </Link>
                        <h1 className="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{meta.title}</h1>
                        <p className="mt-3 max-w-2xl text-sm leading-relaxed text-emerald-100/90">
                            Connect Stripe or PayPal for online fee payments. Credentials are stored as application settings; switch providers anytime.
                        </p>
                        {meta.app_demo ? (
                            <p className="mt-4 inline-flex rounded-xl border border-amber-400/40 bg-amber-500/10 px-3 py-2 text-xs font-medium text-amber-100">
                                Demo build: sandbox keys are not shown in the form for Stripe / PayPal test fields.
                            </p>
                        ) : null}
                    </div>
                </section>

                {err ? (
                    <div className="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900 shadow-sm" role="alert">
                        {err}
                    </div>
                ) : null}
                {info ? (
                    <div className="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950 shadow-sm" role="status">
                        {info}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading payment settings…" />
                ) : (
                    <form onSubmit={save} className="space-y-8">
                        <div>
                            <h2 className="text-sm font-semibold uppercase tracking-wide text-slate-500">Provider</h2>
                            <p className="mt-1 text-sm text-slate-600">Pick the processor your school uses at checkout.</p>
                            <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                <GatewayTile
                                    active={gateway === 'Stripe'}
                                    title="Stripe"
                                    description="Cards and wallets via Stripe Checkout / Elements. Best for predictable card fees."
                                    badge="Cards"
                                    onClick={() => setGateway('Stripe')}
                                />
                                <GatewayTile
                                    active={gateway === 'PayPal'}
                                    title="PayPal"
                                    description="PayPal account or card flows. Choose Sandbox for testing, Live for production."
                                    badge="PayPal"
                                    onClick={() => setGateway('PayPal')}
                                />
                            </div>
                        </div>

                        <div className="rounded-3xl border border-slate-200/80 bg-white/95 p-6 shadow-xl shadow-slate-200/50 backdrop-blur sm:p-8">
                            {gateway === 'Stripe' ? (
                                <div className="space-y-5">
                                    <div className="flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 pb-4">
                                        <div>
                                            <h3 className="text-lg font-semibold text-slate-900">Stripe keys</h3>
                                            <p className="mt-1 text-sm text-slate-500">Publishable key starts with <code className="rounded bg-slate-100 px-1">pk_</code>; secret with <code className="rounded bg-slate-100 px-1">sk_</code>.</p>
                                        </div>
                                    </div>
                                    <label className={labelClass}>
                                        Publishable key
                                        <input
                                            className={`${fieldClass} font-mono text-xs sm:text-sm`}
                                            value={form.stripe_key}
                                            onChange={(e) => setForm((f) => ({ ...f, stripe_key: e.target.value }))}
                                            placeholder="pk_live_… or pk_test_…"
                                            autoComplete="off"
                                        />
                                    </label>
                                    <label className={labelClass}>
                                        Secret key
                                        <input
                                            type="password"
                                            className={`${fieldClass} font-mono text-xs sm:text-sm`}
                                            value={form.stripe_secret}
                                            onChange={(e) => setForm((f) => ({ ...f, stripe_secret: e.target.value }))}
                                            placeholder="sk_live_… or sk_test_…"
                                            autoComplete="new-password"
                                        />
                                    </label>
                                </div>
                            ) : (
                                <div className="space-y-6">
                                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                                        <div>
                                            <h3 className="text-lg font-semibold text-slate-900">PayPal API</h3>
                                            <p className="mt-1 text-sm text-slate-500">Credentials differ between Sandbox and Live environments.</p>
                                        </div>
                                        <div className="flex gap-2 rounded-full bg-slate-100 p-1">
                                            <ModePill active={paypalMode === 'Sandbox'} onClick={() => setPaypalMode('Sandbox')}>
                                                Sandbox
                                            </ModePill>
                                            <ModePill active={paypalMode === 'Live'} onClick={() => setPaypalMode('Live')}>
                                                Live
                                            </ModePill>
                                        </div>
                                    </div>

                                    {paypalMode === 'Sandbox' ? (
                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <label className={labelClass}>
                                                API username *
                                                <input className={fieldClass} value={form.paypal_sandbox_api_username} onChange={(e) => setForm((f) => ({ ...f, paypal_sandbox_api_username: e.target.value }))} />
                                            </label>
                                            <label className={labelClass}>
                                                API password *
                                                <input type="password" className={fieldClass} value={form.paypal_sandbox_api_password} onChange={(e) => setForm((f) => ({ ...f, paypal_sandbox_api_password: e.target.value }))} />
                                            </label>
                                            <label className={labelClass}>
                                                API secret *
                                                <input type="password" className={fieldClass} value={form.paypal_sandbox_api_secret} onChange={(e) => setForm((f) => ({ ...f, paypal_sandbox_api_secret: e.target.value }))} />
                                            </label>
                                            <label className={`${labelClass} sm:col-span-2`}>
                                                API certificate <span className="font-normal text-slate-400">(optional)</span>
                                                <input className={fieldClass} value={form.paypal_sandbox_api_certificate} onChange={(e) => setForm((f) => ({ ...f, paypal_sandbox_api_certificate: e.target.value }))} />
                                            </label>
                                        </div>
                                    ) : (
                                        <div className="grid gap-4 sm:grid-cols-2">
                                            <label className={labelClass}>
                                                API username *
                                                <input className={fieldClass} value={form.paypal_live_api_username} onChange={(e) => setForm((f) => ({ ...f, paypal_live_api_username: e.target.value }))} />
                                            </label>
                                            <label className={labelClass}>
                                                API password *
                                                <input type="password" className={fieldClass} value={form.paypal_live_api_password} onChange={(e) => setForm((f) => ({ ...f, paypal_live_api_password: e.target.value }))} />
                                            </label>
                                            <label className={labelClass}>
                                                API secret *
                                                <input type="password" className={fieldClass} value={form.paypal_live_api_secret} onChange={(e) => setForm((f) => ({ ...f, paypal_live_api_secret: e.target.value }))} />
                                            </label>
                                            <label className={`${labelClass} sm:col-span-2`}>
                                                API certificate <span className="font-normal text-slate-400">(optional)</span>
                                                <input className={fieldClass} value={form.paypal_live_api_certificate} onChange={(e) => setForm((f) => ({ ...f, paypal_live_api_certificate: e.target.value }))} />
                                            </label>
                                        </div>
                                    )}
                                </div>
                            )}

                            <div className="mt-8 flex flex-col items-stretch gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">
                                <p className="text-xs text-slate-500">Changes apply to new online fee checkouts after save.</p>
                                <UiButton type="submit" disabled={saving || !meta.can_update} className="min-w-[140px] bg-emerald-600 text-white hover:bg-emerald-700">
                                    {saving ? (
                                        <>
                                            <UiInlineLoader /> Saving…
                                        </>
                                    ) : (
                                        'Save settings'
                                    )}
                                </UiButton>
                            </div>
                            {!meta.can_update ? <p className="mt-2 text-center text-xs text-slate-500">You do not have permission to update payment gateway settings.</p> : null}
                        </div>
                    </form>
                )}
            </div>
        </AdminLayout>
    );
}
