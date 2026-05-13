import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';
import { UiPageLoader } from '../ui/UiKit';

function displayStudentName(d) {
    if (!d) return '—';
    if (d.full_name && String(d.full_name).trim()) return String(d.full_name).trim();
    const t = `${d.first_name || ''} ${d.last_name || ''}`.trim();
    if (t) return t;
    return d.name || d.student_name || '—';
}

function formatDateTime(s) {
    if (!s) return '—';
    const d = new Date(s);
    return Number.isNaN(d.getTime()) ? String(s) : d.toLocaleString();
}

function formatDate(s) {
    if (!s) return '—';
    const d = new Date(s);
    return Number.isNaN(d.getTime()) ? String(s) : d.toLocaleDateString();
}

const rowCls = 'flex flex-col gap-0.5 border-b border-gray-100 py-2.5 last:border-b-0 sm:grid sm:grid-cols-[200px,1fr] sm:items-start sm:gap-4 sm:py-2';
const dtCls = 'text-xs font-semibold uppercase tracking-wide text-gray-500';
const ddCls = 'text-sm text-gray-800';

function Field({ label, value }) {
    return (
        <div className={rowCls}>
            <dt className={dtCls}>{label}</dt>
            <dd className={ddCls}>{value ?? '—'}</dd>
        </div>
    );
}

export function DeletedHistoryShowPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        setErr('');
        setData(null);
        axios
            .get(`/student-deleted-history/show/${id}`, { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data || null);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load record.'))
            .finally(() => setLoading(false));
    }, [id]);

    const deleter = data?.deleted_by_user;
    const deleterName =
        typeof deleter?.name === 'string' && deleter.name.trim() !== '' ? deleter.name : '—';
    const assignCount = Array.isArray(data?.fees_assign_history) ? data.fees_assign_history.length : 0;
    const collectCount = Array.isArray(data?.fees_collect_history) ? data.fees_collect_history.length : 0;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'View deleted student'}</h1>
                        <Link
                            to="/deleted-history"
                            className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Back
                        </Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading record…" /> : null}
                {!loading && !err && data ? (
                    <div className="space-y-5">
                        <section className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-3 text-sm font-semibold text-gray-900">Student</h2>
                            <dl>
                                <Field label="Name" value={displayStudentName(data)} />
                                <Field
                                    label="Original student id"
                                    value={data.original_student_id != null ? String(data.original_student_id) : '—'}
                                />
                                <Field label="Admission no" value={data.admission_no} />
                                <Field label="Roll no" value={data.roll_no != null ? String(data.roll_no) : '—'} />
                            </dl>
                        </section>

                        <section className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-3 text-sm font-semibold text-gray-900">Contact & profile</h2>
                            <dl>
                                <Field label="Mobile" value={data.mobile} />
                                <Field label="Email" value={data.email} />
                                <Field label="Date of birth" value={formatDate(data.dob)} />
                                <Field label="Admission date" value={formatDate(data.admission_date)} />
                                <Field label="Residence" value={data.residance_address} />
                                <Field label="Place of birth" value={data.place_of_birth} />
                                <Field label="Nationality" value={data.nationality} />
                                <Field label="CPR no" value={data.cpr_no} />
                            </dl>
                        </section>

                        <section className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-3 text-sm font-semibold text-gray-900">Deletion</h2>
                            <dl>
                                <Field label="Deleted at" value={formatDateTime(data.deleted_at)} />
                                <Field label="Deleted by" value={deleterName} />
                            </dl>
                        </section>

                        <section className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-3 text-sm font-semibold text-gray-900">Stored fee snapshots</h2>
                            <p className="text-sm text-gray-600">
                                This record includes <span className="font-medium text-gray-800">{assignCount}</span>{' '}
                                fee assign snapshot{assignCount === 1 ? '' : 's'} and{' '}
                                <span className="font-medium text-gray-800">{collectCount}</span> collection snapshot
                                {collectCount === 1 ? '' : 's'} from when the student was deleted.
                            </p>
                        </section>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}
