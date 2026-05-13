import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicCard, PublicError, PublicLoading } from '../PublicUi';
import { mergeSchoolMeta, safeArray } from '../utils';

export default function PublicResultPage() {
    const [boot, setBoot] = useState(null);
    const [meta, setMeta] = useState(null);
    const [session, setSession] = useState('');
    const [cls, setCls] = useState('');
    const [section, setSection] = useState('');
    const [exam, setExam] = useState('');
    const [admissionNo, setAdmissionNo] = useState('');
    const [classes, setClasses] = useState([]);
    const [sections, setSections] = useState([]);
    const [exams, setExams] = useState([]);
    const [result, setResult] = useState(null);
    const [msg, setMsg] = useState('');
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [searching, setSearching] = useState(false);

    useEffect(() => {
        axios
            .get('/result', { headers: xhrJson })
            .then((r) => {
                setBoot(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        if (!session) {
            setClasses([]);
            setCls('');
            return;
        }
        axios.get('/get-classes', { params: { session }, headers: xhrJson }).then((r) => setClasses(Array.isArray(r.data) ? r.data : []));
    }, [session]);

    useEffect(() => {
        if (!session || !cls) {
            setSections([]);
            setSection('');
            return;
        }
        axios.get('/get-sections', { params: { session, class: cls }, headers: xhrJson }).then((r) => setSections(Array.isArray(r.data) ? r.data : []));
    }, [session, cls]);

    useEffect(() => {
        if (!session || !cls || !section) {
            setExams([]);
            setExam('');
            return;
        }
        axios
            .get('/get-exam-type', { params: { session, class: cls, section }, headers: xhrJson })
            .then((r) => setExams(Array.isArray(r.data) ? r.data : []));
    }, [session, cls, section]);

    const search = async (e) => {
        e.preventDefault();
        setSearching(true);
        setMsg('');
        setResult(null);
        try {
            const { data } = await axios.post(
                '/result',
                { session, class: cls, section, exam, admission_no: admissionNo },
                { headers: xhrJson },
            );
            setResult(data?.data || null);
            setMsg(data?.message || '');
        } catch (ex) {
            const d = ex.response?.data?.data;
            if (d?.result === 'Result not found!') {
                setMsg(ex.response?.data?.message || 'Result not found.');
            } else {
                setMsg(ex.response?.data?.message || 'Search failed.');
            }
        } finally {
            setSearching(false);
        }
    };

    const school = mergeSchoolMeta(meta);
    const sessions = safeArray(boot?.sessions);
    const pdfHref =
        result?.classSection?.student_id && exam && cls && section
            ? `/pdf-download/${result.classSection.student_id}/${exam}/${cls}/${section}`
            : null;

    const input = 'mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none';

    return (
        <PublicLayout
            title="Exam results"
            subtitle="Verify session, class, and admission number exactly as issued on your card."
            school={school}
        >
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : (
                <>
                    <div className="grid gap-6 lg:grid-cols-3">
                        <PublicCard className="lg:col-span-2">
                            <h3 className="font-semibold text-slate-900">How lookup works</h3>
                            <ol className="mt-3 list-inside list-decimal space-y-2 text-sm text-slate-600">
                                <li>Select the academic session when you sat the exam.</li>
                                <li>Pick class and section to load the correct exam types.</li>
                                <li>Enter your admission number exactly — no extra spaces.</li>
                            </ol>
                            <p className="mt-4 text-sm text-slate-600">
                                Need help? Call <span className="font-semibold text-slate-800">{school.phone || 'the school office'}</span> or email{' '}
                                <span className="font-semibold text-slate-800">{school.email || 'registrar'}</span>.
                            </p>
                        </PublicCard>
                        <PublicCard>
                            <h3 className="font-semibold text-slate-900">Downloads</h3>
                            <p className="mt-2 text-sm text-slate-600">After a successful lookup, use “Download PDF” for an official marksheet when enabled.</p>
                        </PublicCard>
                    </div>

                    <form onSubmit={search} className="mx-auto mt-10 max-w-xl rounded-2xl border border-slate-200 bg-white p-8 shadow-sm ring-1 ring-slate-900/5">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="block sm:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Session</span>
                                <select className={input} value={session} onChange={(e) => setSession(e.target.value)} required>
                                    <option value="">Select session</option>
                                    {sessions.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.defaultTranslate?.name || s.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Class</span>
                                <select className={input} value={cls} onChange={(e) => setCls(e.target.value)} required disabled={!session}>
                                    <option value="">Select class</option>
                                    {classes.map((row) => (
                                        <option key={row.id} value={row.classes_id}>
                                            {row.class?.name || row.classes_id}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block">
                                <span className="text-sm font-medium text-slate-700">Section</span>
                                <select className={input} value={section} onChange={(e) => setSection(e.target.value)} required disabled={!cls}>
                                    <option value="">Select section</option>
                                    {sections.map((row) => (
                                        <option key={row.section_id} value={row.section_id}>
                                            {row.section?.name || row.section_id}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block sm:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Exam type</span>
                                <select className={input} value={exam} onChange={(e) => setExam(e.target.value)} required disabled={!section}>
                                    <option value="">Select exam</option>
                                    {exams.map((row) => (
                                        <option key={row.exam_type_id || row.id} value={row.exam_type_id}>
                                            {row.exam_type?.name || row.exam_type?.title || 'Exam'}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="block sm:col-span-2">
                                <span className="text-sm font-medium text-slate-700">Admission number</span>
                                <input className={input} value={admissionNo} onChange={(e) => setAdmissionNo(e.target.value)} required autoComplete="off" />
                            </label>
                        </div>
                        <button
                            type="submit"
                            disabled={searching}
                            className="mt-6 w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow hover:bg-blue-700 disabled:opacity-60"
                        >
                            {searching ? 'Searching…' : 'View result'}
                        </button>
                    </form>

                    {msg ? (
                        <p
                            className={`mx-auto mt-6 max-w-xl rounded-lg px-4 py-3 text-sm ${
                                result?.result ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-900'
                            }`}
                            role="status"
                        >
                            {msg}
                        </p>
                    ) : null}

                    {result?.result ? (
                        <div className="mx-auto mt-8 max-w-2xl rounded-2xl border border-slate-200 bg-white p-8 shadow-lg ring-1 ring-slate-900/5">
                            <h3 className="text-lg font-bold text-slate-900">Outcome summary</h3>
                            <p className="mt-2 text-2xl font-semibold text-blue-700">{result.result}</p>
                            {result.gpa !== undefined && result.gpa !== '' ? (
                                <p className="mt-2 text-slate-700">
                                    GPA: <strong>{result.gpa}</strong>
                                </p>
                            ) : null}
                            {result.avg_marks !== undefined ? (
                                <p className="text-slate-700">
                                    Average marks: <strong>{Number(result.avg_marks).toFixed(2)}</strong>
                                </p>
                            ) : null}
                            {pdfHref ? (
                                <a
                                    href={pdfHref}
                                    className="mt-6 inline-flex rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-slate-800"
                                >
                                    Download PDF marksheet
                                </a>
                            ) : (
                                <p className="mt-4 text-sm text-slate-500">PDF download will appear when available for this exam.</p>
                            )}
                        </div>
                    ) : null}
                </>
            )}
        </PublicLayout>
    );
}
