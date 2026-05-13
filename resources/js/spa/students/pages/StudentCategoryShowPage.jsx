import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader } from '../StudentModuleShared';
import { IconBookOpen, IconCalendar, IconClipboardCheck, IconHash, IconList, IconTag, IconX, UiButtonLink } from '../../ui/UiKit';

function displayText(v) {
    if (v === null || v === undefined || v === '') return '—';
    return String(v);
}

function formatDateTime(raw) {
    if (raw == null || raw === '') return '—';
    const d = typeof raw === 'string' ? new Date(raw) : raw;
    if (d instanceof Date && !Number.isNaN(d.getTime())) {
        return d.toLocaleString(undefined, { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    return String(raw);
}

function statusLabel(status) {
    const s = String(status ?? '');
    if (s === '1' || s === 'true') {
        return <span className="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200">Active</span>;
    }
    return <span className="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700 ring-1 ring-gray-200">Inactive</span>;
}

function DetailRow({ icon: Icon, label, children, iconClass = 'text-indigo-600' }) {
    return (
        <div className="flex gap-3 rounded-xl border border-gray-100 bg-white p-3 shadow-sm ring-1 ring-gray-50">
            <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 ${iconClass}`} aria-hidden>
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

function extractData(res) {
    const b = res?.data;
    if (b?.data && typeof b.data === 'object' && !Array.isArray(b.data) && b.data.id != null) return b.data;
    if (b?.id != null) return b;
    return null;
}

export function StudentCategoryShowPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!id) {
            setErr('Missing category id.');
            setLoading(false);
            return;
        }
        setLoading(true);
        setErr('');
        axios
            .get(`/student/category/show/${id}`, { headers: xhrJson })
            .then((r) => {
                const row = extractData(r);
                setData(row);
                if (!row) setErr('Category not found.');
            })
            .catch((ex) => {
                setData(null);
                setErr(ex.response?.data?.message || ex.message || 'Failed to load category.');
            })
            .finally(() => setLoading(false));
    }, [id]);

    const name = data?.name || data?.title || (data?.id != null ? `Category #${data.id}` : 'Category');

    return (
        <AdminLayout>
            <div className="mx-auto max-w-3xl p-6">
                <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <UiButtonLink to="/categories" variant="secondary">
                        ← Back to categories
                    </UiButtonLink>
                    {data ? <UiButtonLink to={`/categories/${id}/edit`}>Edit category</UiButtonLink> : null}
                </div>

                <div className="mb-6 flex items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-800 text-white shadow-md">
                        <IconTag className="h-8 w-8" aria-hidden />
                    </div>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight text-gray-900">{name}</h1>
                        {data ? <div className="mt-2 flex flex-wrap gap-2">{statusLabel(data.status)}</div> : null}
                    </div>
                </div>

                {loading ? <FullPageLoader text="Loading category…" /> : null}
                {err && !loading ? (
                    <div className="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <span className="mt-0.5 inline-flex rounded-full bg-red-100 p-1 text-red-600" aria-hidden>
                            <IconX className="h-4 w-4" />
                        </span>
                        {err}
                    </div>
                ) : null}

                {!loading && data ? (
                    <div className="space-y-6">
                        <SectionCard
                            icon={IconClipboardCheck}
                            title="Details"
                            iconWrapClass="bg-gradient-to-br from-indigo-600 to-violet-800"
                        >
                            <div className="space-y-3">
                                <DetailRow icon={IconHash} label="ID" iconClass="text-slate-700">
                                    {displayText(data.id)}
                                </DetailRow>
                                <DetailRow icon={IconList} label="Name" iconClass="text-indigo-700">
                                    {displayText(data.name)}
                                </DetailRow>
                                <DetailRow icon={IconTag} label="Shortcode" iconClass="text-amber-700">
                                    {displayText(data.shortcode)}
                                </DetailRow>
                                <div className="md:col-span-2">
                                    <DetailRow icon={IconBookOpen} label="Description" iconClass="text-blue-700">
                                        <span className="whitespace-pre-wrap font-normal text-gray-800">{displayText(data.description)}</span>
                                    </DetailRow>
                                </div>
                                <DetailRow icon={IconClipboardCheck} label="Status" iconClass="text-emerald-700">
                                    {statusLabel(data.status)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <SectionCard
                            icon={IconCalendar}
                            title="Record"
                            subtitle="Audit"
                            iconWrapClass="bg-gradient-to-br from-slate-600 to-slate-900"
                        >
                            <div className="grid gap-3 sm:grid-cols-2">
                                <DetailRow icon={IconCalendar} label="Created" iconClass="text-gray-700">
                                    {formatDateTime(data.created_at)}
                                </DetailRow>
                                <DetailRow icon={IconCalendar} label="Last updated" iconClass="text-gray-700">
                                    {formatDateTime(data.updated_at)}
                                </DetailRow>
                            </div>
                        </SectionCard>

                        <div className="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
                            <UiButtonLink to="/categories" variant="secondary">
                                Back to list
                            </UiButtonLink>
                            <UiButtonLink to={`/categories/${id}/edit`} variant="primary">
                                Edit category
                            </UiButtonLink>
                        </div>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}
