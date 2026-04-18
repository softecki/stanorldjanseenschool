import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, paginateRows, splitTitle, buildDescriptionHtml } from '../../CertificateModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function CertificateGeneratePage({ Layout }) {
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);
    const [classes, setClasses] = useState([]);
    const [certificates, setCertificates] = useState([]);
    const [sections, setSections] = useState([]);
    const [sessionStudents, setSessionStudents] = useState([]);
    const [form, setForm] = useState({ class: '', section: '', student: '', certificate: '' });
    const [result, setResult] = useState(null);

    useEffect(() => {
        axios
            .get('/certificate/generate', { headers: xhrJson })
            .then((r) => {
                setMeta(r.data?.meta || {});
                setClasses(r.data?.data?.classes || []);
                setCertificates(r.data?.data?.certificates || []);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, []);

    useEffect(() => {
        if (!form.class) {
            setSections([]);
            return;
        }
        axios
            .get(`/class-setup/get-sections?id=${encodeURIComponent(form.class)}`, { headers: xhrJson })
            .then((r) => setSections(r.data || []))
            .catch(() => setSections([]));
    }, [form.class]);

    useEffect(() => {
        if (!form.class || !form.section) {
            setSessionStudents([]);
            return;
        }
        axios
            .get(`/certificate/session-students?class=${encodeURIComponent(form.class)}&section=${encodeURIComponent(form.section)}`, {
                headers: xhrJson,
            })
            .then((r) => setSessionStudents(r.data?.data || []))
            .catch(() => setSessionStudents([]));
    }, [form.class, form.section]);

    const search = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        setResult(null);
        try {
            const fd = new FormData();
            fd.append('class', form.class);
            fd.append('section', form.section);
            fd.append('certificate', form.certificate);
            if (form.student) fd.append('student', form.student);
            const { data } = await axios.post('/certificate/generate', fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
            setResult(data?.data || null);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Search failed.');
        } finally {
            setBusy(false);
        }
    };

    const cert = result?.certificate;
    const students = result?.students || [];
    const sessionName = result?.session || '';
    const settings = result?.settings || {};

    const previewBlocks = useMemo(() => {
        if (!cert || !students.length) return [];
        return students.map((row) => {
            const { first, rest } = splitTitle(cert.title);
            const html = buildDescriptionHtml(cert, row, sessionName, settings);
            return { row, first, rest, html };
        });
    }, [cert, students, sessionName, settings]);

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Generate certificate'}</h1>
                    <p className="text-sm text-slate-500">Filter class, section, and certificate.</p>
                </div>
                <Link to="/certificate" className="text-sm font-medium text-blue-600 hover:text-blue-800">
                    Back to list
                </Link>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={search} className="grid gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-2">
                <label className="text-sm font-medium text-slate-700">
                    Class
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.class}
                        onChange={(e) => setForm({ ...form, class: e.target.value, section: '', student: '' })}
                        required
                    >
                        <option value="">Select class</option>
                        {classes.map((cs) => (
                            <option key={cs.id} value={cs.class?.id}>
                                {cs.class?.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Section
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.section}
                        onChange={(e) => setForm({ ...form, section: e.target.value, student: '' })}
                        required
                    >
                        <option value="">Select section</option>
                        {sections.map((s) => (
                            <option key={s.id} value={s.id}>
                                {s.name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Student (optional)
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.student}
                        onChange={(e) => setForm({ ...form, student: e.target.value })}
                    >
                        <option value="">All students in section</option>
                        {sessionStudents.map((s) => (
                            <option key={s.id} value={s.student_id}>
                                {s.student?.first_name} {s.student?.last_name}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="text-sm font-medium text-slate-700">
                    Certificate template
                    <select
                        className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                        value={form.certificate}
                        onChange={(e) => setForm({ ...form, certificate: e.target.value })}
                        required
                    >
                        <option value="">Select</option>
                        {certificates.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.title}
                            </option>
                        ))}
                    </select>
                </label>
                <div className="md:col-span-2">
                    <button
                        type="submit"
                        disabled={busy}
                        className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                    >
                        {busy ? 'Loading…' : 'Search'}
                    </button>
                </div>
            </form>

            {previewBlocks.length ? (
                <div className="space-y-6">
                    <div className="flex justify-end">
                        <button
                            type="button"
                            className="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                            onClick={() => window.print()}
                        >
                            Print
                        </button>
                    </div>
                    {previewBlocks.map(({ row, first, rest, html }) => (
                        <div
                            key={row.id}
                            className="certificate-print relative mx-auto flex min-h-[420px] w-full max-w-[900px] justify-center rounded-xl bg-slate-100 p-6"
                        >
                            <div className="relative w-full max-w-[565px] overflow-hidden rounded-lg bg-white shadow-lg">
                                <img src={cert.bg_image_url} alt="" className="absolute inset-0 h-full w-full object-cover" />
                                {cert.logo && cert.school_logo_url ? (
                                    <img src={cert.school_logo_url} alt="" className="absolute right-8 top-8 h-14 w-auto" />
                                ) : null}
                                <div className="relative z-10 px-8 pb-24 pt-12 text-center">
                                    <h3 className="text-3xl font-medium text-[#392C7D]">{first}</h3>
                                    {rest ? <p className="text-[11px] text-[#392C7D]">{rest}</p> : null}
                                    <p className="mt-1 text-[8px] text-[#392C7D]">{cert.top_text}</p>
                                    {cert.name ? (
                                        <h2 className="mt-4 text-3xl font-medium text-[#15344D]">
                                            {row.student?.first_name} {row.student?.last_name}
                                        </h2>
                                    ) : null}
                                    <div
                                        className="certificate_description mt-3 text-[10px] leading-relaxed text-slate-500"
                                        dangerouslySetInnerHTML={{ __html: html }}
                                    />
                                </div>
                                <div className="absolute bottom-10 left-0 right-0 z-10 flex justify-center gap-16">
                                    <div className="text-center">
                                        {cert.bottom_left_signature_url ? (
                                            <img src={cert.bottom_left_signature_url} alt="" className="mx-auto h-8 object-contain" />
                                        ) : null}
                                        <div className="mx-auto mt-1 w-24 border-b border-[#392C7D]" />
                                        <span className="mt-1 block text-[6px] uppercase text-[#15344D]">{cert.bottom_left_text}</span>
                                    </div>
                                    <div className="text-center">
                                        {cert.bottom_right_signature_url ? (
                                            <img src={cert.bottom_right_signature_url} alt="" className="mx-auto h-8 object-contain" />
                                        ) : null}
                                        <div className="mx-auto mt-1 w-24 border-b border-[#392C7D]" />
                                        <span className="mt-1 block text-[6px] uppercase text-[#15344D]">{cert.bottom_right_text}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            ) : null}
        </Shell>
    );
}

