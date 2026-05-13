import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { ContentRail, PublicCard, PublicError, PublicLoading } from '../PublicUi';
import { excerpt, formatDate, mediaUrl, mergeSchoolMeta, sidebarRows } from '../utils';

export default function PublicNewsDetailPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get(`/news-detail/${id}`, { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Not found.'))
            .finally(() => setLoading(false));
    }, [id]);

    const school = mergeSchoolMeta(meta);
    const article = data?.news;
    const allNewsRows = sidebarRows(data?.allNews);
    const sidebar = allNewsRows.filter((n) => String(n.id) !== String(id)).slice(0, 6);

    return (
        <PublicLayout title={article?.title || 'News'} subtitle={article ? formatDate(article.publish_date) : ''} school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : article ? (
                <ContentRail
                    main={
                        <article>
                            {article.upload?.path || article.image ? (
                                <img
                                    src={mediaUrl(article.upload?.path || article.image)}
                                    alt=""
                                    className="max-h-[420px] w-full rounded-2xl object-cover shadow-lg ring-1 ring-slate-900/5"
                                />
                            ) : null}
                            <div
                                className="prose prose-slate prose-lg mt-8 max-w-none"
                                dangerouslySetInnerHTML={{ __html: article.description || article.content || '' }}
                            />
                        </article>
                    }
                    aside={
                        <>
                            <PublicCard>
                                <h4 className="font-semibold text-slate-900">On this topic</h4>
                                <p className="mt-2 text-sm text-slate-600">Browse related stories from our newsroom.</p>
                            </PublicCard>
                            <div>
                                <h4 className="font-semibold text-slate-900">More news</h4>
                                <div className="mt-4 space-y-3">
                                    {sidebar.map((n) => (
                                        <Link
                                            key={n.id}
                                            to={`/news-detail/${n.id}`}
                                            className="block rounded-xl border border-slate-200 bg-white p-4 text-sm shadow-sm transition hover:border-blue-200"
                                        >
                                            <p className="font-medium text-slate-900">{n.title}</p>
                                            <p className="mt-1 text-xs text-slate-500">{excerpt(n.description || n.content, 90)}</p>
                                        </Link>
                                    ))}
                                </div>
                                <Link to="/news" className="mt-4 inline-block text-sm font-semibold text-blue-700 hover:underline">
                                    ← All news
                                </Link>
                            </div>
                        </>
                    }
                />
            ) : (
                <p className="text-slate-600">Article not found.</p>
            )}
        </PublicLayout>
    );
}
