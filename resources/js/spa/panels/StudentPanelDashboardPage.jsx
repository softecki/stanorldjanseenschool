import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { xhrJson } from '../api/xhrJson';

function StudentPanelLayout({ children }) {
    return (
        <div className="min-h-screen bg-slate-50">
            <header className="border-b bg-white">
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-3">
                    <strong>Student</strong>
                    <button
                        type="button"
                        className="text-sm text-blue-700"
                        onClick={() => axios.post('/logout').then(() => { window.location.href = '/login'; })}
                    >
                        Logout
                    </button>
                </div>
            </header>
            {children}
        </div>
    );
}

export function StudentPanelDashboardPage() {
    const [data, setData] = useState(null);
    const [err, setErr] = useState('');
    useEffect(() => {
        axios
            .get('/student-panel-dashboard', { headers: xhrJson })
            .then((res) => setData(res.data?.data ?? null))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load dashboard.'));
    }, []);
    return (
        <StudentPanelLayout>
            <div className="mx-auto max-w-7xl p-6">
                <h1 className="text-2xl font-bold">Student dashboard</h1>
                {err ? <p className="mt-2 text-sm text-red-600">{err}</p> : null}
                <pre className="mt-4 overflow-auto rounded bg-white p-4 text-xs">{JSON.stringify(data, null, 2)}</pre>
            </div>
        </StudentPanelLayout>
    );
}
