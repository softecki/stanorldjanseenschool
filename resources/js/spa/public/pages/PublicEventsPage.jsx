import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useSearchParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicEmpty, PublicError, PublicLoading, SectionHeader } from '../PublicUi';
import { excerpt, formatDate, mediaUrl, mergeSchoolMeta, normalizePaginator } from '../utils';

export default function PublicEventsPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const page = Number(searchParams.get('page') || 1);
    const [paginator, setPaginator] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        axios
            .get('/events', { headers: xhrJson, params: { page } })
            .then((r) => {
                setPaginator(normalizePaginator(r.data?.data?.events));
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load events.'))
            .finally(() => setLoading(false));
    }, [page]);

    const school = mergeSchoolMeta(meta);
    const items = paginator?.data || [];

    return (
        <PublicLayout title="Events" subtitle="Ceremonies, workshops, sports, and community gatherings." school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : items.length ? (
                <>
                    <SectionHeader eyebrow="Calendar" title="Upcoming & recent events" />
                    <div className="mt-8 grid gap-6 md:grid-cols-2">
                        {items.map((ev) => (
                            <Link key={ev.id} to={`/event-detail/${ev.id}`} className="group block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-900/5 transition hover:border-blue-200 hover:shadow-md">
                                <img src={mediaUrl(ev.upload?.path || ev.image)} alt="" className="h-48 w-full object-cover transition group-hover:opacity-95" />
                                <div className="p-5">
                                    <p className="text-xs font-bold uppercase tracking-wide text-blue-700">{formatDate(ev.date)}</p>
                                    <p className="mt-2 text-lg font-bold text-slate-900">{ev.title}</p>
                                    <p className="mt-2 text-sm text-slate-600">{excerpt(ev.description, 150)}</p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </>
            ) : (
                <PublicEmpty title="No events listed" hint="Check notices for schedule updates." />
            )}
            {paginator?.last_page > 1 ? (
                <div className="mt-10 flex flex-wrap items-center justify-between gap-3">
                    <button
                        type="button"
                        disabled={page <= 1}
                        onClick={() => setSearchParams({ page: String(page - 1) })}
                        className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium disabled:opacity-40"
                    >
                        Previous
                    </button>
                    <span className="text-sm text-slate-600">
                        Page {paginator.current_page} of {paginator.last_page}
                    </span>
                    <button
                        type="button"
                        disabled={page >= paginator.last_page}
                        onClick={() => setSearchParams({ page: String(page + 1) })}
                        className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium disabled:opacity-40"
                    >
                        Next
                    </button>
                </div>
            ) : null}
        </PublicLayout>
    );
}
