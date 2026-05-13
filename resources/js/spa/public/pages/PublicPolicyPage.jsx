import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import { PublicError, PublicLoading } from '../PublicUi';
import { mergeSchoolMeta } from '../utils';

export default function PublicPolicyPage() {
    const [html, setHtml] = useState('');
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get('/policy', { headers: xhrJson })
            .then((r) => {
                setHtml(r.data?.data?.html || '');
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load policy.'))
            .finally(() => setLoading(false));
    }, []);

    const school = mergeSchoolMeta(meta);

    return (
        <PublicLayout title="Privacy policy" subtitle="Transparency for families, staff, and visitors." school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : (
                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg ring-1 ring-slate-900/5">
                    <iframe title="Privacy policy" srcDoc={html} className="min-h-[70vh] w-full border-0" sandbox="allow-same-origin allow-popups-to-escape-sandbox" />
                </div>
            )}
        </PublicLayout>
    );
}
