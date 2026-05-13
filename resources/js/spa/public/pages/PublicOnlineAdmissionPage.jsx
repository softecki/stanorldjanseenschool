import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicCard, PublicError, PublicLoading, SectionHeader, Timeline } from '../PublicUi';
import { mergeSchoolMeta, safeArray } from '../utils';

const FAQ = [
    { q: 'How long does review take?', a: 'Admissions are reviewed in order. You will see a confirmation message after submit.' },
    { q: 'What if payment is required?', a: 'If enabled, you will be redirected to upload a payment slip after your application is received.' },
    { q: 'Can I edit after submit?', a: 'Contact the registrar for corrections; include your phone number and student name.' },
];

const STEPS = [
    { title: '1. Apply online', body: 'Complete the form with accurate contact details and class preferences.' },
    { title: '2. School review', body: 'Our team validates information and may call to confirm.' },
    { title: '3. Next steps', body: 'If payment applies, you will be guided to the fees page to finish enrollment.' },
];

export default function PublicOnlineAdmissionPage() {
    const nav = useNavigate();
    const [boot, setBoot] = useState(null);
    const [meta, setMeta] = useState(null);
    const [classes, setClasses] = useState([]);
    const [sections, setSections] = useState([]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [form, setForm] = useState({
        first_name: '',
        last_name: '',
        phone: '',
        email: '',
        session: '',
        class: '',
        section: '',
        dob: '',
        gender: '',
        religion: '',
        guardian_name: '',
        guardian_phone: '',
        guardian_profession: '',
        father_name: '',
        father_phone: '',
        father_profession: '',
        mother_name: '',
        mother_phone: '',
        mother_profession: '',
        place_of_birth: '',
        father_nationality: '',
        previous_school_info: '',
    });

    useEffect(() => {
        axios
            .get('/online-admission', { headers: xhrJson })
            .then((r) => {
                setBoot(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'))
            .finally(() => setLoading(false));
    }, []);

    const school = mergeSchoolMeta(meta);
    const sessions = safeArray(boot?.sessions);
    const religions = safeArray(boot?.religions);
    const genders = safeArray(boot?.genders);

    useEffect(() => {
        if (!form.session) {
            setClasses([]);
            setForm((f) => ({ ...f, class: '', section: '' }));
            return;
        }
        axios.get('/get-classes', { params: { session: form.session }, headers: xhrJson }).then((r) => setClasses(Array.isArray(r.data) ? r.data : []));
    }, [form.session]);

    useEffect(() => {
        if (!form.session || !form.class) {
            setSections([]);
            setForm((f) => ({ ...f, section: '' }));
            return;
        }
        axios
            .get('/get-sections', { params: { session: form.session, class: form.class }, headers: xhrJson })
            .then((r) => setSections(Array.isArray(r.data) ? r.data : []));
    }, [form.session, form.class]);

    const input = 'mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setErr('');
        const fd = new FormData();
        Object.entries(form).forEach(([k, v]) => {
            if (v !== '' && v !== null && v !== undefined) fd.append(k, v);
        });
        fd.append('previous_school', '0');
        try {
            const { data } = await axios.post('/online-admission', fd, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const to = data?.redirect;
            if (to) {
                try {
                    const u = new URL(to, window.location.origin);
                    nav(`${u.pathname}${u.search}`);
                } catch {
                    nav('/online-admission');
                }
            } else {
                nav('/online-admission');
            }
        } catch (ex) {
            const d = ex.response?.data;
            setErr(typeof d === 'string' ? d : d?.message || JSON.stringify(d?.errors || 'Submit failed.'));
        } finally {
            setBusy(false);
        }
    };

    return (
        <PublicLayout
            title="Online admission"
            subtitle="Structured intake with registrar review — accurate details speed up approval."
            school={school}
        >
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : (
                <>
                    <div className="grid gap-8 lg:grid-cols-3">
                        <div className="lg:col-span-2 space-y-8">
                            <PublicCard>
                                <SectionHeader title="Admission journey" description="What happens after you click submit." />
                                <div className="mt-6">
                                    <Timeline items={STEPS} />
                                </div>
                            </PublicCard>
                            <PublicCard>
                                <SectionHeader title="Requirements checklist" />
                                <ul className="mt-4 list-inside list-disc space-y-2 text-sm text-slate-600">
                                    <li>Legal names matching birth certificate or prior records.</li>
                                    <li>Guardian contacts that can receive SMS or calls.</li>
                                    <li>Prior school notes if transferring mid-year.</li>
                                    <li>
                                        Questions? <Link className="font-semibold text-blue-700 hover:underline" to="/contact">Contact us</Link>.
                                    </li>
                                </ul>
                            </PublicCard>
                        </div>
                        <div className="space-y-6">
                            <PublicCard>
                                <h4 className="font-semibold text-slate-900">Registrar desk</h4>
                                <p className="mt-2 text-sm text-slate-600">
                                    {school.phone ? <>Phone: {school.phone}</> : <>Call the main office for intake status.</>}
                                </p>
                                {school.email ? <p className="mt-1 text-sm text-slate-600">Email: {school.email}</p> : null}
                            </PublicCard>
                            <PublicCard>
                                <h4 className="font-semibold text-slate-900">FAQs</h4>
                                <dl className="mt-4 space-y-4 text-sm">
                                    {FAQ.map((item) => (
                                        <div key={item.q}>
                                            <dt className="font-medium text-slate-800">{item.q}</dt>
                                            <dd className="mt-1 text-slate-600">{item.a}</dd>
                                        </div>
                                    ))}
                                </dl>
                            </PublicCard>
                        </div>
                    </div>

                    <form onSubmit={submit} className="mx-auto mt-12 max-w-4xl space-y-8 rounded-2xl border border-slate-200 bg-white p-8 shadow-lg ring-1 ring-slate-900/5">
                        <SectionHeader title="Application form" description="Fields marked * are required." />
                        <div className="grid gap-4 md:grid-cols-2">
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">First name *</span>
                                <input className={input} required value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Last name *</span>
                                <input className={input} required value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Phone *</span>
                                <input className={input} required value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Email *</span>
                                <input type="email" className={input} required value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                            </label>
                            <label className="block md:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Session *</span>
                                <select className={input} required value={form.session} onChange={(e) => setForm({ ...form, session: e.target.value })}>
                                    <option value="">Select</option>
                                    {sessions.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.defaultTranslate?.name || s.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Class *</span>
                                <select
                                    className={input}
                                    required
                                    value={form.class}
                                    onChange={(e) => setForm({ ...form, class: e.target.value })}
                                    disabled={!form.session}
                                >
                                    <option value="">Select</option>
                                    {classes.map((row) => (
                                        <option key={row.id} value={row.classes_id}>
                                            {row.class?.name || row.classes_id}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Section</span>
                                <select
                                    className={input}
                                    value={form.section}
                                    onChange={(e) => setForm({ ...form, section: e.target.value })}
                                    disabled={!form.class}
                                >
                                    <option value="">Select</option>
                                    {sections.map((row) => (
                                        <option key={row.section_id} value={row.section_id}>
                                            {row.section?.name || row.section_id}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Date of birth</span>
                                <input type="date" className={input} value={form.dob} onChange={(e) => setForm({ ...form, dob: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Gender</span>
                                <select className={input} value={form.gender} onChange={(e) => setForm({ ...form, gender: e.target.value })}>
                                    <option value="">Select</option>
                                    {genders.map((g) => (
                                        <option key={g.id} value={g.id}>
                                            {g.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block md:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Religion</span>
                                <select className={input} value={form.religion} onChange={(e) => setForm({ ...form, religion: e.target.value })}>
                                    <option value="">Select</option>
                                    {religions.map((r) => (
                                        <option key={r.id} value={r.id}>
                                            {r.defaultTranslate?.name || r.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block md:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Guardian name</span>
                                <input className={input} value={form.guardian_name} onChange={(e) => setForm({ ...form, guardian_name: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Guardian phone</span>
                                <input className={input} value={form.guardian_phone} onChange={(e) => setForm({ ...form, guardian_phone: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Guardian profession</span>
                                <input className={input} value={form.guardian_profession} onChange={(e) => setForm({ ...form, guardian_profession: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Father name</span>
                                <input className={input} value={form.father_name} onChange={(e) => setForm({ ...form, father_name: e.target.value })} />
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Father phone</span>
                                <input className={input} value={form.father_phone} onChange={(e) => setForm({ ...form, father_phone: e.target.value })} />
                            </label>
                            <label className="block md:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Mother name</span>
                                <input className={input} value={form.mother_name} onChange={(e) => setForm({ ...form, mother_name: e.target.value })} />
                            </label>
                            <label className="block md:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Place of birth</span>
                                <input className={input} value={form.place_of_birth} onChange={(e) => setForm({ ...form, place_of_birth: e.target.value })} />
                            </label>
                            <label className="block md:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Previous school info</span>
                                <textarea className={input} rows={3} value={form.previous_school_info} onChange={(e) => setForm({ ...form, previous_school_info: e.target.value })} />
                            </label>
                        </div>
                        <button
                            type="submit"
                            disabled={busy}
                            className="w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                        >
                            {busy ? 'Submitting…' : 'Submit application'}
                        </button>
                    </form>
                </>
            )}
        </PublicLayout>
    );
}
