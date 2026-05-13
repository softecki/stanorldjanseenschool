import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicCard, PublicError, PublicLoading, SectionHeader, Timeline } from '../PublicUi';
import { mergeSchoolMeta, safeArray } from '../utils';

const PAY_STEPS = [
    { title: 'Transfer or deposit', body: 'Complete payment using the bank details provided in the instructions below.' },
    { title: 'Capture slip', body: 'Take a clear photo of the receipt or stamped slip — JPG or PNG.' },
    { title: 'Upload here', body: 'Submit the image for verification. Keep your reference number handy.' },
];

export default function PublicOnlineAdmissionFeesPage() {
    const { reference, admissionId } = useParams();
    const nav = useNavigate();
    const [payload, setPayload] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [file, setFile] = useState(null);
    const [busy, setBusy] = useState(false);
    const [msg, setMsg] = useState('');

    useEffect(() => {
        axios
            .get(`/online-admission-fees/${reference}/${admissionId}`, { headers: xhrJson })
            .then((r) => {
                setPayload(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Could not load fees.'))
            .finally(() => setLoading(false));
    }, [reference, admissionId]);

    const school = mergeSchoolMeta(meta);

    const submit = async (e) => {
        e.preventDefault();
        if (!file) {
            setMsg('Please choose a payment slip image.');
            return;
        }
        setBusy(true);
        setMsg('');
        const fd = new FormData();
        fd.append('id', admissionId);
        fd.append('payment_image', file);
        try {
            const { data } = await axios.post('/online-admission-fees', fd, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const redirect = data?.redirect || '/online-admission';
            const u = new URL(redirect, window.location.origin);
            nav(`${u.pathname}${u.search}`);
        } catch (ex) {
            const d = ex.response?.data;
            setMsg(d?.message || d?.errors?.payment_image?.[0] || 'Upload failed.');
        } finally {
            setBusy(false);
        }
    };

    const admission = payload?.admission;
    const fees = payload?.fees;
    const instruction = payload?.payment_instruction;
    const feeRows = safeArray(fees).length ? safeArray(fees) : fees && typeof fees === 'object' ? Object.values(fees) : [];

    return (
        <PublicLayout
            title="Admission payment"
            subtitle="Secure proof upload — reference stays tied to your application."
            school={school}
        >
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : (
                <div className="mx-auto max-w-4xl space-y-8">
                    <div className="grid gap-6 lg:grid-cols-3">
                        <PublicCard className="lg:col-span-2">
                            <SectionHeader title="Payment steps" />
                            <div className="mt-6">
                                <Timeline items={PAY_STEPS} />
                            </div>
                        </PublicCard>
                        <PublicCard>
                            <h4 className="font-semibold text-slate-900">Need help?</h4>
                            <p className="mt-2 text-sm text-slate-600">
                                Email {school.email || 'the registrar'} or call {school.phone || 'the office'} with your reference number.
                            </p>
                        </PublicCard>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg ring-1 ring-slate-900/5">
                        {admission ? (
                            <div className="text-sm text-slate-600">
                                <p>
                                    Reference: <strong className="text-slate-900">{admission.reference_no}</strong>
                                </p>
                                <p className="mt-2">
                                    Applicant:{' '}
                                    <strong className="text-slate-900">
                                        {admission.first_name} {admission.last_name}
                                    </strong>
                                </p>
                            </div>
                        ) : null}

                        {feeRows.length ? (
                            <div className="mt-6 rounded-xl border border-blue-100 bg-blue-50/80 p-4 text-sm">
                                <p className="font-semibold text-blue-900">Fee lines</p>
                                <ul className="mt-2 space-y-1 text-blue-900/90">
                                    {feeRows.slice(0, 12).map((row, idx) => (
                                        <li key={row.id ?? idx} className="flex justify-between gap-4">
                                            <span>{row.name || row.title || row.fees_master?.name || 'Fee item'}</span>
                                            <span className="tabular-nums font-medium">
                                                {row.amount != null ? Number(row.amount).toLocaleString() : '—'}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ) : (
                            <div className="mt-6 rounded-xl bg-blue-50 p-4 text-sm text-blue-900">
                                <p className="font-semibold">Fee summary</p>
                                <p className="mt-2 text-blue-800">Fee assignment is loaded from your admission record when configured.</p>
                            </div>
                        )}

                        {instruction?.value ? (
                            <div
                                className="prose prose-sm prose-slate mt-6 max-w-none rounded-xl border border-slate-200 p-4"
                                dangerouslySetInnerHTML={{ __html: instruction.value }}
                            />
                        ) : null}

                        <form onSubmit={submit} className="mt-8 space-y-4">
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Payment slip (jpeg/png/jpg/gif)</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    className="mt-2 block w-full text-sm"
                                    onChange={(e) => setFile(e.target.files?.[0] || null)}
                                />
                            </label>
                            {msg ? (
                                <p className="text-sm text-red-600" role="alert">
                                    {msg}
                                </p>
                            ) : null}
                            <button
                                type="submit"
                                disabled={busy}
                                className="w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                            >
                                {busy ? 'Uploading…' : 'Submit payment proof'}
                            </button>
                        </form>
                    </div>
                </div>
            )}
        </PublicLayout>
    );
}
