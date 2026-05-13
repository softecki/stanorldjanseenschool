import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useSearchParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicCard, PublicEmpty, PublicError, PublicLoading, SectionHeader } from '../PublicUi';
import { excerpt, formatDate, mediaUrl, mergeSchoolMeta, normalizePaginator } from '../utils';

export default function PublicNewsPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const page = Number(searchParams.get('page') || 1);
    const [paginator, setPaginator] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        axios
            .get('/news', { headers: xhrJson, params: { page } })
            .then((r) => {
                setPaginator(normalizePaginator(r.data?.data?.news));
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load news.'))
            .finally(() => setLoading(false));
    }, [page]);

    const school = mergeSchoolMeta(meta);
    const items = paginator?.data || [];

    return (
        <PublicLayout title="News" subtitle="Stories, achievements, and campus highlights." school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : items.length ? (
                <>
                    <SectionHeader eyebrow="Newsroom" title="Latest articles" />
                    <div className="mt-8 grid gap-6 md:grid-cols-2">
                        {items.map((n) => (
                            <Link key={n.id} to={`/news-detail/${n.id}`} className="group block">
                                <PublicCard padding="p-0 overflow-hidden transition group-hover:border-blue-200 group-hover:shadow-md">
                                    <div className="flex flex-col sm:flex-row">
                                        <img
                                            src={mediaUrl(n.upload?.path || n.image)}
                                            alt=""
                                            className="h-44 w-full shrink-0 object-cover sm:h-auto sm:w-40"
                                        />
                                        <div className="flex flex-col p-5">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{formatDate(n.publish_date)}</p>
                                            <p className="mt-2 font-semibold text-slate-900 group-hover:text-blue-800">{n.title}</p>
                                            <p className="mt-2 flex-1 text-sm text-slate-600">{excerpt(n.description || n.content, 150)}</p>
                                            <span className="mt-3 text-sm font-semibold text-blue-700">Read more →</span>
                                        </div>
                                    </div>
                                </PublicCard>
                            </Link>
                        ))}
                    </div>
                </>
            ) : (
                <PublicEmpty title="No news yet" hint="New articles will appear here when published." />
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
