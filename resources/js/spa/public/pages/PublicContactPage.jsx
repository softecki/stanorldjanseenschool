import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicCard, PublicError, PublicLoading, SectionHeader } from '../PublicUi';
import { mergeSchoolMeta } from '../utils';

export default function PublicContactPage() {
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [form, setForm] = useState({ name: '', phone: '', email: '', subject: '', message: '' });
    const [submitErr, setSubmitErr] = useState('');
    const [submitOk, setSubmitOk] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios
            .get('/contact', { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'))
            .finally(() => setLoading(false));
    }, []);

    const school = mergeSchoolMeta(meta);

    const mapHref = useMemo(() => {
        const q = school.address?.trim();
        if (!q) return null;
        return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(q)}`;
    }, [school.address]);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setSubmitErr('');
        setSubmitOk('');
        try {
            const { data: res } = await axios.post('/contact', form, { headers: xhrJson });
            const msg = Array.isArray(res) ? res[1] || res[0] : res?.message || 'Sent.';
            setSubmitOk(msg);
            setForm({ name: '', phone: '', email: '', subject: '', message: '' });
        } catch (ex) {
            const d = ex.response?.data;
            setSubmitErr(Array.isArray(d) ? d[1] || d[0] : d?.message || 'Failed to send.');
        } finally {
            setBusy(false);
        }
    };

    const fields =
        'rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100';

    return (
        <PublicLayout
            title="Contact us"
            subtitle="Reach the right desk — admissions, fees, or general inquiries."
            school={school}
        >
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : (
                <div className="grid gap-10 lg:grid-cols-2">
                    <div className="space-y-6">
                        <PublicCard padding="p-8">
                            <SectionHeader title="Send a message" description="We typically respond within 1–2 business days." />
                            {submitOk ? <p className="mt-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{submitOk}</p> : null}
                            {submitErr ? <p className="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{submitErr}</p> : null}
                            <form className="mt-6 space-y-4" onSubmit={submit}>
                                <label className="block">
                                    <span className="text-sm font-medium text-slate-700">Name</span>
                                    <input
                                        className={`mt-1 w-full ${fields}`}
                                        value={form.name}
                                        onChange={(e) => setForm({ ...form, name: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium text-slate-700">Phone</span>
                                    <input className={`mt-1 w-full ${fields}`} value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium text-slate-700">Email</span>
                                    <input
                                        type="email"
                                        className={`mt-1 w-full ${fields}`}
                                        value={form.email}
                                        onChange={(e) => setForm({ ...form, email: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium text-slate-700">Subject</span>
                                    <input
                                        className={`mt-1 w-full ${fields}`}
                                        value={form.subject}
                                        onChange={(e) => setForm({ ...form, subject: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className="block">
                                    <span className="text-sm font-medium text-slate-700">Message</span>
                                    <textarea
                                        className={`mt-1 w-full ${fields}`}
                                        rows={5}
                                        value={form.message}
                                        onChange={(e) => setForm({ ...form, message: e.target.value })}
                                        required
                                    />
                                </label>
                                <button
                                    type="submit"
                                    disabled={busy}
                                    className="w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                                >
                                    {busy ? 'Sending…' : 'Send message'}
                                </button>
                            </form>
                        </PublicCard>

                        <PublicCard>
                            <h4 className="font-semibold text-slate-900">Before you write</h4>
                            <ul className="mt-3 list-inside list-disc space-y-2 text-sm text-slate-600">
                                <li>Include your child&apos;s class or admission reference if applicable.</li>
                                <li>For fees, mention invoice or student admission number.</li>
                                <li>Urgent safety matters — call the office directly.</li>
                            </ul>
                        </PublicCard>
                    </div>

                    <div className="space-y-6">
                        {(data?.contactInfo || []).map((c) => (
                            <PublicCard key={c.id}>
                                <p className="font-semibold text-slate-900">{c.title || 'Main office'}</p>
                                <p className="mt-2 text-sm text-slate-600">{c.email}</p>
                                <p className="text-sm text-slate-600">{c.phone}</p>
                                <p className="mt-2 text-sm leading-relaxed text-slate-600">{c.address}</p>
                            </PublicCard>
                        ))}

                        {(data?.depContact || []).length ? (
                            <PublicCard>
                                <h4 className="font-semibold text-slate-900">Departments</h4>
                                <ul className="mt-3 space-y-3 text-sm text-slate-600">
                                    {data.depContact.map((d) => (
                                        <li key={d.id} className="border-b border-slate-100 pb-3 last:border-0">
                                            <span className="font-medium text-slate-800">{d.title || d.name}</span>
                                            {d.phone ? <p className="mt-1">Phone: {d.phone}</p> : null}
                                            {d.email ? <p>Email: {d.email}</p> : null}
                                        </li>
                                    ))}
                                </ul>
                            </PublicCard>
                        ) : null}

                        <PublicCard padding="p-0 overflow-hidden">
                            <div className="border-b border-slate-100 px-5 py-3">
                                <p className="text-sm font-semibold text-slate-900">Location</p>
                                <p className="text-xs text-slate-500">Opens maps in a new tab</p>
                            </div>
                            <div className="aspect-video w-full bg-slate-100">
                                {mapHref ? (
                                    <a
                                        href={mapHref}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="flex h-full w-full flex-col items-center justify-center gap-2 p-6 text-center text-sm font-medium text-blue-700 hover:bg-slate-50"
                                    >
                                        <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-blue-800">Map</span>
                                        <span className="text-slate-700">{school.address || 'View on Google Maps'}</span>
                                    </a>
                                ) : (
                                    <div className="flex h-full items-center justify-center p-6 text-sm text-slate-500">Address will appear here when configured in settings.</div>
                                )}
                            </div>
                        </PublicCard>
                    </div>
                </div>
            )}
        </PublicLayout>
    );
}
