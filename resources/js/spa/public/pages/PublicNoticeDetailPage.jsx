import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { ContentRail, PublicCard, PublicError, PublicLoading } from '../PublicUi';
import { excerpt, formatDate, mergeSchoolMeta, sidebarRows } from '../utils';

export default function PublicNoticeDetailPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get(`/notice-detail/${id}`, { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Not found.'))
            .finally(() => setLoading(false));
    }, [id]);

    const school = mergeSchoolMeta(meta);
    const notice = data?.['notice-board'];
    const allNoticeRows = sidebarRows(data?.allNotice);
    const more = allNoticeRows.filter((n) => String(n.id) !== String(id)).slice(0, 8);

    return (
        <PublicLayout title={notice?.title || 'Notice'} subtitle={notice ? formatDate(notice.publish_date) : ''} school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : notice ? (
                <ContentRail
                    main={
                        <article className="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm ring-1 ring-slate-900/5">
                            <div className="prose prose-slate max-w-none" dangerouslySetInnerHTML={{ __html: notice.description || notice.message || '' }} />
                        </article>
                    }
                    aside={
                        <>
                            <PublicCard>
                                <p className="text-xs font-semibold uppercase text-slate-500">Published</p>
                                <p className="mt-1 font-semibold text-slate-900">{formatDate(notice.publish_date)}</p>
                                <p className="mt-3 text-sm text-slate-600">Keep this notice for your records. Contact the office if you need a printed copy.</p>
                            </PublicCard>
                            <div>
                                <h4 className="font-semibold text-slate-900">More notices</h4>
                                <div className="mt-4 space-y-3">
                                    {more.map((n) => (
                                        <Link key={n.id} to={`/notice-detail/${n.id}`} className="block rounded-xl border border-slate-200 p-3 text-sm transition hover:bg-slate-50">
                                            <p className="font-medium text-slate-900">{n.title}</p>
                                            <p className="mt-1 text-xs text-slate-500">{excerpt(n.description || n.message, 80)}</p>
                                        </Link>
                                    ))}
                                </div>
                                <Link to="/notices" className="mt-4 inline-block text-sm font-semibold text-blue-700 hover:underline">
                                    ← All notices
                                </Link>
                            </div>
                        </>
                    }
                />
            ) : (
                <p className="text-slate-600">Not found.</p>
            )}
        </PublicLayout>
    );
}
