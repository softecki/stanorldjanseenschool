import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader } from '../StudentModuleShared';
import {
    IconBookOpen,
    IconCalendar,
    IconClipboardCheck,
    IconEnvelope,
    IconGlobe,
    IconHash,
    IconList,
    IconMapPin,
    IconPhone,
    IconTag,
    IconUser,
    IconUsers,
    IconX,
    UiButtonLink,
} from '../../ui/UiKit';

function formatDate(raw) {
    if (raw == null || raw === '') return '—';
    const d = typeof raw === 'string' ? new Date(raw) : raw;
    if (d instanceof Date && !Number.isNaN(d.getTime())) {
        return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
    }
    return String(raw);
}

function formatDateTime(raw) {
    if (raw == null || raw === '') return '—';
    const d = typeof raw === 'string' ? new Date(raw) : raw;
    if (d instanceof Date && !Number.isNaN(d.getTime())) {
        return d.toLocaleString(undefined, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    return String(raw);
}

function displayText(v) {
    if (v === null || v === undefined || v === '') return '—';
    return String(v);
}

function yesNoFromFlag(v) {
    if (v === null || v === undefined || v === '') return '—';
    const s = String(v);
    if (s === '1' || s === 'true') return 'Yes';
    if (s === '0' || s === 'false') return 'No';
    return s;
}

function statusBadge(status) {
    const s = String(status ?? '');
    const active = s === '1' || s.toLowerCase() === 'active';
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                active ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200' : 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
            }`}
        >
            {active ? 'Active' : 'Inactive'}
        </span>
    );
}

function humanizeKey(key) {
    return String(key).replaceAll('_', ' ');
}

function DetailRow({ icon: Icon, label, children, iconClass = 'text-blue-600' }) {
    return (
        <div className="flex gap-3 rounded-xl border border-gray-100 bg-white p-3 shadow-sm ring-1 ring-gray-50">
            <div
                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 ${iconClass}`}
                aria-hidden
            >
                <Icon className="h-5 w-5" />
            </div>
            <div className="min-w-0 flex-1">
                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</p>
                <div className="mt-0.5 text-sm font-semibold text-gray-900">{children}</div>
            </div>
        </div>
    );
}

function SectionCard({ icon: Icon, title, subtitle, iconWrapClass, children }) {
    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
            <div className="flex items-start gap-3 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-4 py-3">
                <div className={`mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-lg shadow-sm ring-1 ring-gray-200/80 ${iconWrapClass}`}>
                    <Icon className="h-5 w-5 text-white" />
                </div>
                <div>
                    <h2 className="text-sm font-semibold text-gray-900">{title}</h2>
                    {subtitle ? <p className="mt-0.5 text-xs text-gray-500">{subtitle}</p> : null}
                </div>
            </div>
            <div className="p-4">{children}</div>
        </div>
    );
}

/** Keys rendered in curated sections — remainder shown under “Other fields”. */
const CURATED_KEYS = new Set([
    'id',
    'full_name',
    'first_name',
    'last_name',
    'admission_no',
    'roll_no',
    'control_number',
    'status',
    'admission_date',
    'dob',
    'student_category_id',
    'student_category_name',
    'gender_id',
    'gender_name',
    'category_id',
    'religion_id',
    'blood_group_id',
    'mobile',
    'email',
    'parent_guardian_id',
    'residance_address',
    'nationality',
    'place_of_birth',
    'cpr_no',
    'spoken_lang_at_home',
    'previous_school',
    'previous_school_info',
    'previous_school_image_id',
    'sms_send',
    'sms_send_description',
    'upload_documents',
    'image_id',
    'user_id',
    'created_at',
    'updated_at',
]);

function documentsSummary(doc) {
    if (doc == null || doc === '') return '—';
    if (Array.isArray(doc)) {
        if (doc.length === 0) return <span className="font-normal text-gray-500">No documents uploaded</span>;
        return (
            <span>
                {doc.length} file{doc.length === 1 ? '' : 's'} on record
            </span>
        );
    }
    if (typeof doc === 'object') return <span className="font-normal text-gray-600">Document metadata on file</span>;
    return displayText(doc);
}

/** Supports `{ data: student }`, `{ data: { student } }`, or a bare student object. */
function extractStudentFromApiResponse(res) {
    const body = res?.data;
    if (!body || typeof body !== 'object') return null;
    const inner = body.data;
    if (inner != null && typeof inner === 'object' && !Array.isArray(inner)) {
        if (inner.student && typeof inner.student === 'object' && inner.student.id != null) {
            return inner.student;
        }
        if (inner.id != null) {
            return inner;
        }
    }
    if (body.id != null) {
        return body;
    }
    return null;
}

export function StudentShowPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');

    useEffect(() => {
        if (!id) {
            setErr('Missing student id.');
            setLoading(false);
            return;
        }
        setLoading(true);
        setErr('');
        axios
            .get(`/student/show/${id}`, { headers: xhrJson })
            .then((r) => {
                const row = extractStudentFromApiResponse(r);
                setData(row);
                if (!row) setErr('Student not found or unexpected response format.');
            })
            .catch((ex) => {
                setData(null);
                setErr(ex.response?.data?.message || ex.message || 'Failed to load student.');
            })
            .finally(() => setLoading(false));
    }, [id]);

    const displayName =
        data?.full_name ||
        `${data?.first_name || ''} ${data?.last_name || ''}`.trim() ||
        (data?.id != null ? `Student #${data.id}` : 'Student');

    const extraRows =
        data && typeof data === 'object'
            ? Object.entries(data).filter(([k, v]) => {
                  if (CURATED_KEYS.has(k)) return false;
                  if (v === null || v === undefined || v === '') return false;
                  if (typeof v === 'object' && !Array.isArray(v)) return false;
                  if (Array.isArray(v) && v.length === 0) return false;
                  return true;
              })
            : [];

    const parentId = data?.parent_guardian_id;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 flex flex-wrap items-start justify-between gap-4">
                    <UiButtonLink to="/students" variant="secondary">
                        ← Back to students
                    </UiButtonLink>
                    {data ? <UiButtonLink to={`/students/${id}/edit`}>Edit student</UiButtonLink> : null}
                </div>

                <div className="mb-6 flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-md">
                        <IconUser className="h-8 w-8" aria-hidden />
                    </div>
                    <div className="min-w-0 flex-1">
                        <h1 className="text-2xl font-semibold tracking-tight text-gray-900">{displayName}</h1>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            {data ? statusBadge(data.status) : null}
                            {data?.roll_no ? (
                                <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                    <IconHash className="h-3.5 w-3.5 text-gray-500" aria-hidden />
                                    Roll {data.roll_no}
                                </span>
                            ) : null}
                            {data?.control_number ? (
                                <span className="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-800 ring-1 ring-sky-100">
                                    Control {data.control_number}
                                </span>
                            ) : null}
                        </div>
                    </div>
                </div>

                {loading ? <FullPageLoader text="Loading student details…" /> : null}

                {err && !loading ? (
                    <div className="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <span className="mt-0.5 inline-flex rounded-full bg-red-100 p-1 text-red-600" aria-hidden>
                            <IconX className="h-4 w-4" />
                        </span>
                        <span>{err}</span>
                    </div>
                ) : null}

                {!loading && data ? (
                    <div className="space-y-6">
                        <SectionCard
                            icon={IconClipboardCheck}
                            title="Identity & enrollment"
                            subtitle="Official names, numbers, and school dates"
                            iconWrapClass="bg-gradient-to-br from-blue-600 to-indigo-700"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconHash} label="Student ID" iconClass="text-slate-700">
                                    {displayText(data.id)}
                                </DetailRow>
                                <DetailRow icon={IconUser} label="First name" iconClass="text-blue-700">
                                    {displayText(data.first_name)}
                                </DetailRow>
                                <DetailRow icon={IconUser} label="Last name" iconClass="text-blue-700">
                                    {displayText(data.last_name)}
                                </DetailRow>
                                <DetailRow icon={IconTag} label="Admission number" iconClass="text-violet-700">
                                    {displayText(data.admission_no)}
                                </DetailRow>
                                <DetailRow icon={IconHash} label="Roll number" iconClass="text-slate-700">
                                    {displayText(data.roll_no)}
                                </DetailRow>
                                <DetailRow icon={IconHash} label="Control number" iconClass="text-sky-700">
                                    {displayText(data.control_number)}
                                </DetailRow>
                                <DetailRow icon={IconCalendar} label="Admission date" iconClass="text-emerald-700">
                                    {formatDate(data.admission_date)}
                                </DetailRow>
                                <DetailRow icon={IconCalendar} label="Date of birth" iconClass="text-emerald-700">
                                    {formatDate(data.dob)}
                                </DetailRow>
                                <DetailRow icon={IconTag} label="Student category" iconClass="text-amber-700">
                                    {displayText(data.student_category_name ?? data.student_category_id)}
                                </DetailRow>
                                <DetailRow icon={IconTag} label="Gender" iconClass="text-amber-700">
                                    {displayText(data.gender_name ?? data.gender_id)}
                                </DetailRow>
                                <DetailRow icon={IconTag} label="Category" iconClass="text-gray-700">
                                    {displayText(data.category_id)}
                                </DetailRow>
                                <DetailRow icon={IconTag} label="Religion" iconClass="text-gray-700">
                                    {displayText(data.religion_id)}
                                </DetailRow>
                                <DetailRow icon={IconTag} label="Blood group" iconClass="text-rose-700">
                                    {displayText(data.blood_group_id)}
                                </DetailRow>
                                <div className="sm:col-span-2">
                                    <DetailRow icon={IconClipboardCheck} label="Account status" iconClass="text-emerald-700">
                                        {statusBadge(data.status)}
                                    </DetailRow>
                                </div>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconPhone}
                            title="Contact"
                            subtitle="How to reach the student or family"
                            iconWrapClass="bg-gradient-to-br from-teal-500 to-cyan-700"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconPhone} label="Mobile" iconClass="text-teal-700">
                                    {displayText(data.mobile)}
                                </DetailRow>
                                <DetailRow icon={IconEnvelope} label="Email" iconClass="text-cyan-700">
                                    {displayText(data.email)}
                                </DetailRow>
                                <div className="sm:col-span-2">
                                    <DetailRow icon={IconUsers} label="Parent / guardian" iconClass="text-indigo-700">
                                        {parentId ? (
                                            <Link
                                                to={`/parents/${parentId}/edit`}
                                                className="text-indigo-600 underline decoration-indigo-200 underline-offset-2 hover:text-indigo-800"
                                            >
                                                Open guardian record (#{parentId})
                                            </Link>
                                        ) : (
                                            '—'
                                        )}
                                    </DetailRow>
                                </div>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconMapPin}
                            title="Address & background"
                            iconWrapClass="bg-gradient-to-br from-amber-500 to-orange-600"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <div className="sm:col-span-2">
                                    <DetailRow icon={IconMapPin} label="Residence address" iconClass="text-amber-800">
                                        {displayText(data.residance_address)}
                                    </DetailRow>
                                </div>
                                <DetailRow icon={IconGlobe} label="Nationality" iconClass="text-orange-800">
                                    {displayText(data.nationality)}
                                </DetailRow>
                                <DetailRow icon={IconMapPin} label="Place of birth" iconClass="text-amber-800">
                                    {displayText(data.place_of_birth)}
                                </DetailRow>
                                <DetailRow icon={IconHash} label="CPR number" iconClass="text-gray-700">
                                    {displayText(data.cpr_no)}
                                </DetailRow>
                                <DetailRow icon={IconGlobe} label="Languages spoken at home" iconClass="text-orange-800">
                                    {displayText(data.spoken_lang_at_home)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconBookOpen}
                            title="Previous school"
                            iconWrapClass="bg-gradient-to-br from-violet-500 to-purple-700"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconBookOpen} label="Previous school" iconClass="text-violet-700">
                                    {displayText(data.previous_school)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="Previous school details" iconClass="text-purple-700">
                                    {displayText(data.previous_school_info)}
                                </DetailRow>
                                <DetailRow icon={IconHash} label="Previous school image ID" iconClass="text-gray-600">
                                    {displayText(data.previous_school_image_id)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconList}
                            title="Documents & notifications"
                            iconWrapClass="bg-gradient-to-br from-slate-600 to-slate-800"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <div className="sm:col-span-2">
                                    <DetailRow icon={IconClipboardCheck} label="Uploaded documents" iconClass="text-slate-700">
                                        {documentsSummary(data.upload_documents)}
                                    </DetailRow>
                                </div>
                                <DetailRow icon={IconList} label="SMS send" iconClass="text-slate-700">
                                    {yesNoFromFlag(data.sms_send)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="SMS description" iconClass="text-slate-700">
                                    {displayText(data.sms_send_description)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconHash}
                            title="System"
                            subtitle="Internal references and audit timestamps"
                            iconWrapClass="bg-gradient-to-br from-gray-600 to-gray-900"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconHash} label="User ID" iconClass="text-gray-700">
                                    {displayText(data.user_id)}
                                </DetailRow>
                                <DetailRow icon={IconHash} label="Image ID" iconClass="text-gray-700">
                                    {displayText(data.image_id)}
                                </DetailRow>
                                <DetailRow icon={IconCalendar} label="Created" iconClass="text-gray-700">
                                    {formatDateTime(data.created_at)}
                                </DetailRow>
                                <DetailRow icon={IconCalendar} label="Last updated" iconClass="text-gray-700">
                                    {formatDateTime(data.updated_at)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        {extraRows.length > 0 ? (
                            <SectionCard
                                icon={IconList}
                                title="Other fields"
                                subtitle="Additional values returned by the server"
                                iconWrapClass="bg-gradient-to-br from-emerald-600 to-teal-800"
                            >
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {extraRows.map(([k, v]) => (
                                        <DetailRow key={k} icon={IconList} label={humanizeKey(k)} iconClass="text-emerald-800">
                                            {Array.isArray(v) ? documentsSummary(v) : displayText(v)}
                                        </DetailRow>
                                    ))}
                                </div>
                            </SectionCard>
                        ) : null}

                        <div className="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
                            <UiButtonLink to="/students" variant="secondary">
                                Back to list
                            </UiButtonLink>
                            <UiButtonLink to={`/students/${id}/edit`} variant="primary">
                                Edit student
                            </UiButtonLink>
                        </div>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}
