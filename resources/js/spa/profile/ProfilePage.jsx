import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';

export function ProfilePage() {
    const [data, setData] = useState(null);
    useEffect(() => {
        axios.get('/my/profile', { headers: xhrJson }).then((r) => setData(r.data?.data || null));
    }, []);
    return (
        <AdminLayout>
            <div className="mx-auto max-w-4xl p-6">
                <h1 className="text-2xl font-bold">My Profile</h1>
                <pre className="mt-4 overflow-auto rounded bg-white p-4 text-xs">{JSON.stringify(data, null, 2)}</pre>
                <div className="mt-3 flex gap-3">
                    <Link to="/my/profile/edit" className="text-blue-700">
                        Edit profile
                    </Link>
                    <Link to="/my/password/update" className="text-blue-700">
                        Update password
                    </Link>
                </div>
            </div>
        </AdminLayout>
    );
}
