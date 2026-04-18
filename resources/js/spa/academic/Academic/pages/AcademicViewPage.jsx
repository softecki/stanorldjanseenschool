import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { FullPageLoader, Panel, firstValue, normalizeRows, optionFrom } from '../../AcademicModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function AcademicViewPage({ Layout, title, loadEndpoint, backTo, editBase }) {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let mounted = true;
        const load = async () => {
            setLoading(true);
            try {
                const r = await axios.get(`${loadEndpoint}/edit/${id}`, { headers: xhrJson });
                if (!mounted) return;
                setData(r.data?.data || null);
                setErr('');
            } catch (ex) {
                if (!mounted) return;
                setErr(ex.response?.data?.message || 'Failed to load record.');
            } finally {
                if (mounted) setLoading(false);
            }
        };
        load();
        return () => { mounted = false; };
    }, [id, loadEndpoint]);

    return (
        <Panel Layout={Layout} title={title}>
            {loading ? <FullPageLoader text="Loading record..." /> : null}
            {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
            <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <dl className="grid gap-3 text-sm md:grid-cols-2">
                    {Object.entries(data || {}).map(([k, v]) => (
                        <div key={k}>
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">{k.replace(/_/g, ' ')}</dt>
                            <dd className="mt-1 text-gray-800">{typeof v === 'object' ? JSON.stringify(v) : String(v ?? '-')}</dd>
                        </div>
                    ))}
                </dl>
                <div className="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-3">
                    <Link to={backTo} className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Back</Link>
                    <Link to={`${editBase}/${id}/edit`} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">Edit</Link>
                </div>
            </div>
        </Panel>
    );
}

