import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { FullPageLoader, Panel } from '../../AcademicModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import {
    IconBookOpen,
    IconCalendar,
    IconGlobe,
    IconHash,
    IconList,
    IconTag,
} from '../../../ui/UiKit';

function formatDateTime(s) {
    if (s == null || s === '') return '—';
    const d = new Date(s);
    return Number.isNaN(d.getTime()) ? String(s) : d.toLocaleString();
}

function isActive(s) {
    return String(s) === '1' || s === 1;
}

function statusBadge(s) {
    if (s === undefined || s === null || s === '') return { label: '—', className: 'bg-gray-100 text-gray-600' };
    if (isActive(s)) return { label: 'Active', className: 'bg-emerald-50 text-emerald-800' };
    if (String(s) === '0' || s === 0) return { label: 'Inactive', className: 'bg-gray-100 text-gray-800' };
    return { label: 'Inactive', className: 'bg-amber-50 text-amber-900' };
}

function DetailLine({ icon: Icon, label, value }) {
    return (
        <div className="flex gap-3 rounded-lg border border-gray-100 bg-white/50 px-3 py-2.5">
            {Icon ? (
                <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-700" aria-hidden>
                    <Icon className="h-4 w-4" />
                </div>
            ) : null}
            <div className="min-w-0 flex-1">
                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</p>
                <p className="mt-0.5 text-sm text-gray-900">{value}</p>
            </div>
        </div>
    );
}

function ClassOrSectionView({ data, isClass }) {
    const tran = isClass ? data?.class_tran : data?.section_tran;
    const { label, className } = statusBadge(data?.status);
    return (
        <div className="space-y-3">
            <DetailLine icon={IconTag} label={isClass ? 'Class name' : 'Section name'} value={data?.name || '—'} />
            {tran && String(tran) !== String(data?.name) ? <DetailLine icon={IconGlobe} label="Localized display" value={String(tran)} /> : null}
            <div className="flex items-center gap-2 rounded-lg border border-gray-100 bg-white/50 px-3 py-2.5">
                <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-700" aria-hidden>
                    <IconList className="h-4 w-4" />
                </div>
                <div className="min-w-0 flex-1">
                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</p>
                    <p className="mt-1">
                        <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${className}`}>{label}</span>
                    </p>
                </div>
            </div>
            <DetailLine icon={IconHash} label="ID" value={data?.id != null ? String(data.id) : '—'} />
            {isClass && data?.orders != null && data?.orders !== '' ? (
                <DetailLine icon={IconList} label="Orders" value={String(data.orders)} />
            ) : null}
            <DetailLine icon={IconBookOpen} label="Record type" value={isClass ? 'Academic class' : 'Academic section'} />
            <DetailLine icon={IconCalendar} label="Created" value={formatDateTime(data?.created_at)} />
            <DetailLine icon={IconCalendar} label="Last updated" value={formatDateTime(data?.updated_at)} />
        </div>
    );
}

function GenericKeyValueView({ data }) {
    if (!data || typeof data !== 'object') {
        return <p className="text-sm text-gray-500">No data.</p>;
    }
    return (
        <dl className="grid gap-3 text-sm md:grid-cols-2">
            {Object.entries(data).map(([k, v]) => {
                const label = k.replace(/_/g, ' ');
                if (v != null && typeof v === 'object' && !Array.isArray(v)) {
                    return (
                        <div key={k} className="md:col-span-2">
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</dt>
                            <dd className="mt-1 break-all font-mono text-xs text-gray-800">{JSON.stringify(v)}</dd>
                        </div>
                    );
                }
                if (Array.isArray(v)) {
                    return (
                        <div key={k} className="md:col-span-2">
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</dt>
                            <dd className="mt-1 text-gray-800">
                                {v.length} {v.length === 1 ? 'item' : 'items'}
                            </dd>
                        </div>
                    );
                }
                if (k === 'status') {
                    const b = statusBadge(v);
                    return (
                        <div key={k} className="md:col-span-1">
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</dt>
                            <dd className="mt-1">
                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${b.className}`}>{b.label}</span>
                            </dd>
                        </div>
                    );
                }
                return (
                    <div key={k} className="min-w-0">
                        <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{label}</dt>
                        <dd className="mt-1 text-gray-800">
                            {k.includes('_at') && typeof v === 'string' ? formatDateTime(v) : String(v === undefined || v === null ? '—' : v)}
                        </dd>
                    </div>
                );
            })}
        </dl>
    );
}

export function AcademicViewPage({ Layout, title, loadEndpoint, backTo, editBase }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const isClass = loadEndpoint === '/classes';
    const isSection = loadEndpoint === '/section';
    const structured = isClass || isSection;

    useEffect(() => {
        let mounted = true;
        const load = async () => {
            setLoading(true);
            setErr('');
            setData(null);
            try {
                const r = await axios.get(`${loadEndpoint}/edit/${id}`, { headers: xhrJson });
                if (!mounted) return;
                setData(r.data?.data || null);
            } catch (ex) {
                if (!mounted) return;
                setErr(ex.response?.data?.message || 'Failed to load record.');
            } finally {
                if (mounted) setLoading(false);
            }
        };
        load();
        return () => {
            mounted = false;
        };
    }, [id, loadEndpoint]);

    return (
        <Panel Layout={Layout} title={title}>
            {loading ? <FullPageLoader text="Loading record…" /> : null}
            {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
            {!loading && !err ? (
                <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    {data && structured ? <ClassOrSectionView data={data} isClass={isClass} /> : null}
                    {data && !structured ? <GenericKeyValueView data={data} /> : null}
                    {!data ? <p className="text-sm text-gray-500">No data found for this record.</p> : null}
                    <div className="mt-6 flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
                        <Link
                            to={backTo}
                            className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Back
                        </Link>
                        {data?.id != null ? (
                            <Link
                                to={`${editBase}/${id}/edit`}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                            >
                                Edit
                            </Link>
                        ) : null}
                    </div>
                </div>
            ) : null}
        </Panel>
    );
}
