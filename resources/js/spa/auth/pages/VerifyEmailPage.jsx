import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useParams } from 'react-router-dom';
import { AuthLayout } from '../AuthLayout';
import { xhrJson } from '../../api/xhrJson';

export default function VerifyEmailPage() {
    const { email, token } = useParams();
    const [state, setState] = useState({ loading: true, status: '', message: '' });

    useEffect(() => {
        let active = true;

        const verify = async () => {
            try {
                const { data } = await axios.get(`/verify-email/${encodeURIComponent(email || '')}/${encodeURIComponent(token || '')}`, {
                    headers: xhrJson,
                });
                if (!active) return;
                setState({
                    loading: false,
                    status: data?.status || 'success',
                    message: data?.message || 'Your email has been verified.',
                });
            } catch (ex) {
                if (!active) return;
                setState({
                    loading: false,
                    status: 'error',
                    message: ex.response?.data?.message || 'Email verification failed. Please request a new link.',
                });
            }
        };

        verify();
        return () => {
            active = false;
        };
    }, [email, token]);

    const noticeClass = useMemo(() => {
        if (state.status === 'success') return 'bg-emerald-50 text-emerald-800';
        return 'bg-red-50 text-red-700';
    }, [state.status]);

    return (
        <AuthLayout title="Email verification" subtitle="Account activation">
            {state.loading ? (
                <p className="rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-700">Verifying your email, please wait...</p>
            ) : (
                <p className={`rounded-md px-3 py-2 text-sm ${noticeClass}`}>{state.message}</p>
            )}

            <div className="mt-6 text-center text-sm text-slate-600">
                <Link className="font-medium text-blue-600 hover:text-blue-800" to="/login">
                    Back to login
                </Link>
            </div>
        </AuthLayout>
    );
}
