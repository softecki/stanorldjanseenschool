import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useSearchParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicEmpty, PublicError, PublicLoading, SectionHeader } from '../PublicUi';
import { excerpt, formatDate, mergeSchoolMeta, normalizePaginator } from '../utils';

export default function PublicNoticesPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const page = Number(searchParams.get('page') || 1);
    const [paginator, setPaginator] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        axios
            .get('/notices', { headers: xhrJson, params: { page } })
            .then((r) => {
                setPaginator(normalizePaginator(r.data?.data?.notices));
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load notices.'))
            .finally(() => setLoading(false));
    }, [page]);

    const school = mergeSchoolMeta(meta);
    const items = paginator?.data || [];

    return (
        <PublicLayout title="Notices" subtitle="Official announcements, circulars, and urgent updates." school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : items.length ? (
                <>
                    <SectionHeader eyebrow="Bulletin" title="School notices" description="Most recent items appear first." />
                    <ul className="mt-8 space-y-4">
                        {items.map((n) => (
                            <li key={n.id}>
                                <Link
                                    to={`/notice-detail/${n.id}`}
                                    className="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:border-amber-300 hover:shadow-md"
                                >
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{formatDate(n.publish_date)}</p>
                                    <p className="mt-2 text-lg font-semibold text-slate-900">{n.title}</p>
                                    <p className="mt-2 text-sm text-slate-600">{excerpt(n.description || n.message, 180)}</p>
                                    <span className="mt-3 inline-block text-sm font-semibold text-amber-800">Open notice →</span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </>
            ) : (
                <PublicEmpty title="No notices" hint="There are no visible notices for the selected filters." />
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
