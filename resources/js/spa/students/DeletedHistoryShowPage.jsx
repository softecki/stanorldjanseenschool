import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';

export function DeletedHistoryShowPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    useEffect(() => {
        axios
            .get(`/student-deleted-history/show/${id}`, { headers: xhrJson })
            .then((r) => setData(r.data?.data || null))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load record.'));
    }, [id]);
    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">Deleted Student Record</h1>
                        <Link to="/deleted-history" className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Back
                        </Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <dl className="grid gap-3 text-sm md:grid-cols-2">
                        <div>
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">Student</dt>
                            <dd className="mt-1 text-gray-800">{data?.student_name || data?.name || '-'}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">Admission No</dt>
                            <dd className="mt-1 text-gray-800">{data?.admission_no || '-'}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">Deleted At</dt>
                            <dd className="mt-1 text-gray-800">{data?.deleted_at || '-'}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">Reason</dt>
                            <dd className="mt-1 text-gray-800">{data?.reason || data?.note || '-'}</dd>
                        </div>
                        <div className="md:col-span-2">
                            <dt className="text-xs font-semibold uppercase tracking-wide text-gray-500">Raw Payload</dt>
                            <pre className="mt-2 overflow-auto rounded-lg border border-gray-100 bg-gray-50 p-3 text-xs text-gray-700">{JSON.stringify(data, null, 2)}</pre>
                        </div>
                    </dl>
                </div>
            </div>
        </AdminLayout>
    );
}
