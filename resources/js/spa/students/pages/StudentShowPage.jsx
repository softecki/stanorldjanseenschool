import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

export function StudentShowPage() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    useEffect(() => {
        setLoading(true);
        axios.get(`/student/show/${id}`, { headers: xhrJson })
            .then((r) => setData(r.data?.data || null))
            .finally(() => setLoading(false));
    }, [id]);
    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                <h1 className="text-2xl font-bold">Student Details</h1>
                {loading ? <FullPageLoader text="Loading student details..." /> : null}
                {!loading ? <pre className="mt-4 overflow-auto rounded bg-white p-4 text-xs">{JSON.stringify(data, null, 2)}</pre> : null}
                <div className="mt-3"><Link to={`/students/${id}/edit`} className="text-blue-700">Edit</Link></div>
            </div>
        </AdminLayout>
    );
}

