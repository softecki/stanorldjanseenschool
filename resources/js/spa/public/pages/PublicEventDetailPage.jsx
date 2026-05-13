import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { ContentRail, PublicCard, PublicError, PublicLoading } from '../PublicUi';
import { excerpt, formatDate, mediaUrl, mergeSchoolMeta, sidebarRows } from '../utils';

export default function PublicEventDetailPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get(`/event-detail/${id}`, { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Not found.'))
            .finally(() => setLoading(false));
    }, [id]);

    const school = mergeSchoolMeta(meta);
    const ev = data?.event;
    const allEv = sidebarRows(data?.allEvent);
    const more = allEv.filter((e) => String(e.id) !== String(id)).slice(0, 6);

    return (
        <PublicLayout title={ev?.title || 'Event'} subtitle={ev ? formatDate(ev.date) : ''} school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : ev ? (
                <ContentRail
                    main={
                        <>
                            <img src={mediaUrl(ev.upload?.path || ev.image)} alt="" className="max-h-[420px] w-full rounded-2xl object-cover shadow-lg ring-1 ring-slate-900/5" />
                            <div className="prose prose-slate prose-lg mt-8 max-w-none" dangerouslySetInnerHTML={{ __html: ev.description || '' }} />
                        </>
                    }
                    aside={
                        <>
                            <PublicCard>
                                <p className="text-xs font-semibold uppercase text-slate-500">Date</p>
                                <p className="mt-1 text-lg font-bold text-slate-900">{formatDate(ev.date)}</p>
                                <p className="mt-4 text-sm text-slate-600">Add this date to your calendar and arrive 15 minutes early for seating.</p>
                            </PublicCard>
                            <div>
                                <h4 className="font-semibold text-slate-900">Other events</h4>
                                <div className="mt-4 space-y-3">
                                    {more.map((e) => (
                                        <Link key={e.id} to={`/event-detail/${e.id}`} className="block rounded-xl border border-slate-200 p-4 text-sm transition hover:bg-slate-50">
                                            <p className="text-xs text-slate-500">{formatDate(e.date)}</p>
                                            <p className="font-medium text-slate-900">{e.title}</p>
                                            <p className="mt-1 text-xs text-slate-600">{excerpt(e.description, 80)}</p>
                                        </Link>
                                    ))}
                                </div>
                                <Link to="/events" className="mt-4 inline-block text-sm font-semibold text-blue-700 hover:underline">
                                    ← All events
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
