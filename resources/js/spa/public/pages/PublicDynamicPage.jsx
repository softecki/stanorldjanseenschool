import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicError, PublicLoading } from '../PublicUi';
import { mergeSchoolMeta } from '../utils';

export default function PublicDynamicPage() {
    const { slug } = useParams();
    const [page, setPage] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get(`/page/${encodeURIComponent(slug)}`, { headers: xhrJson })
            .then((r) => {
                setPage(r.data?.data?.page);
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Page not found.'))
            .finally(() => setLoading(false));
    }, [slug]);

    const school = mergeSchoolMeta(meta);

    return (
        <PublicLayout title={page?.title || 'Page'} subtitle="" school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : page ? (
                <article className="prose prose-slate prose-lg max-w-none rounded-2xl border border-slate-200 bg-white p-8 shadow-sm ring-1 ring-slate-900/5">
                    <div dangerouslySetInnerHTML={{ __html: page.content || '' }} />
                </article>
            ) : (
                <p className="text-slate-600">Not found.</p>
            )}
        </PublicLayout>
    );
}
