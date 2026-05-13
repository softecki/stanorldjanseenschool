import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader } from '../StudentModuleShared';
import {
    IconCalendar,
    IconClipboardCheck,
    IconEnvelope,
    IconHash,
    IconList,
    IconMapPin,
    IconPhone,
    IconUser,
    IconUsers,
    IconX,
    UiButtonLink,
} from '../../ui/UiKit';

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

function extractParentFromApiResponse(res) {
    const body = res?.data;
    if (!body || typeof body !== 'object') return null;
    const inner = body.data;
    if (inner != null && typeof inner === 'object' && !Array.isArray(inner) && inner.id != null) {
        return inner;
    }
    if (body.id != null) {
        return body;
    }
    return null;
}

export function ParentShowPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');

    useEffect(() => {
        if (!id) {
            setErr('Missing parent id.');
            setLoading(false);
            return;
        }
        setLoading(true);
        setErr('');
        axios
            .get(`/parent/show/${id}`, { headers: xhrJson })
            .then((r) => {
                const row = extractParentFromApiResponse(r);
                setData(row);
                if (!row) setErr('Parent not found.');
            })
            .catch((ex) => {
                setData(null);
                setErr(ex.response?.data?.message || ex.message || 'Failed to load parent.');
            })
            .finally(() => setLoading(false));
    }, [id]);

    const displayName =
        data?.guardian_name ||
        [data?.father_name, data?.mother_name].filter(Boolean).join(' · ') ||
        (data?.id != null ? `Parent #${data.id}` : 'Parent');

    const userEmail = data?.user?.email ?? data?.guardian_email;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 flex flex-wrap items-start justify-between gap-4">
                    <UiButtonLink to="/parents" variant="secondary">
                        ← Back to parents
                    </UiButtonLink>
                    {data ? <UiButtonLink to={`/parents/${id}/edit`}>Edit parent</UiButtonLink> : null}
                </div>

                <div className="mb-6 flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white shadow-md">
                        <IconUsers className="h-8 w-8" aria-hidden />
                    </div>
                    <div className="min-w-0 flex-1">
                        <h1 className="text-2xl font-semibold tracking-tight text-gray-900">{displayName}</h1>
                        <div className="mt-2 flex flex-wrap items-center gap-2">
                            {data ? statusBadge(data.status) : null}
                            {data?.guardian_mobile ? (
                                <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                    <IconPhone className="h-3.5 w-3.5 text-gray-500" aria-hidden />
                                    {data.guardian_mobile}
                                </span>
                            ) : null}
                        </div>
                    </div>
                </div>

                {loading ? <FullPageLoader text="Loading parent…" /> : null}

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
                            title="Primary guardian"
                            subtitle="Main contact on file for this family"
                            iconWrapClass="bg-gradient-to-br from-blue-600 to-indigo-700"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconHash} label="Parent ID" iconClass="text-slate-700">
                                    {displayText(data.id)}
                                </DetailRow>
                                <DetailRow icon={IconUser} label="Guardian name" iconClass="text-blue-700">
                                    {displayText(data.guardian_name)}
                                </DetailRow>
                                <DetailRow icon={IconPhone} label="Guardian mobile" iconClass="text-teal-700">
                                    {displayText(data.guardian_mobile)}
                                </DetailRow>
                                <DetailRow icon={IconEnvelope} label="Guardian email" iconClass="text-cyan-700">
                                    {displayText(data.guardian_email)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="Profession" iconClass="text-violet-700">
                                    {displayText(data.guardian_profession)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="Relation to student" iconClass="text-amber-700">
                                    {displayText(data.guardian_relation)}
                                </DetailRow>
                                <div className="sm:col-span-2">
                                    <DetailRow icon={IconMapPin} label="Guardian address" iconClass="text-orange-800">
                                        {displayText(data.guardian_address)}
                                    </DetailRow>
                                </div>
                                <div className="sm:col-span-2">
                                    <DetailRow icon={IconClipboardCheck} label="Account status" iconClass="text-emerald-700">
                                        {statusBadge(data.status)}
                                    </DetailRow>
                                </div>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconUser}
                            title="Father"
                            iconWrapClass="bg-gradient-to-br from-slate-600 to-slate-800"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconUser} label="Name" iconClass="text-slate-700">
                                    {displayText(data.father_name)}
                                </DetailRow>
                                <DetailRow icon={IconPhone} label="Mobile" iconClass="text-teal-700">
                                    {displayText(data.father_mobile)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="Profession" iconClass="text-slate-700">
                                    {displayText(data.father_profession)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="Nationality" iconClass="text-slate-700">
                                    {displayText(data.father_nationality)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconUser}
                            title="Mother"
                            iconWrapClass="bg-gradient-to-br from-rose-500 to-pink-700"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconUser} label="Name" iconClass="text-rose-800">
                                    {displayText(data.mother_name)}
                                </DetailRow>
                                <DetailRow icon={IconPhone} label="Mobile" iconClass="text-teal-700">
                                    {displayText(data.mother_mobile)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="Profession" iconClass="text-rose-800">
                                    {displayText(data.mother_profession)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconHash}
                            title="Linked account"
                            subtitle="User record tied to this guardian"
                            iconWrapClass="bg-gradient-to-br from-gray-600 to-gray-900"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconHash} label="User ID" iconClass="text-gray-700">
                                    {displayText(data.user_id)}
                                </DetailRow>
                                <DetailRow icon={IconEnvelope} label="Login email" iconClass="text-gray-700">
                                    {displayText(userEmail)}
                                </DetailRow>
                                <DetailRow icon={IconPhone} label="Login phone" iconClass="text-gray-700">
                                    {displayText(data.user?.phone)}
                                </DetailRow>
                                <DetailRow icon={IconCalendar} label="Record created" iconClass="text-gray-700">
                                    {formatDateTime(data.created_at)}
                                </DetailRow>
                                <DetailRow icon={IconCalendar} label="Last updated" iconClass="text-gray-700">
                                    {formatDateTime(data.updated_at)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <div className="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
                            <UiButtonLink to="/parents" variant="secondary">
                                Back to list
                            </UiButtonLink>
                            <UiButtonLink to={`/parents/${id}/edit`} variant="primary">
                                Edit parent
                            </UiButtonLink>
                        </div>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}
