import React, { useState } from 'react';
import axios from 'axios';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { AuthButton, AuthField, AuthInput, AuthLayout } from '../AuthLayout';
import { spaNavigatePath } from '../spaNavigate';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export default function LoginPage() {
    const nav = useNavigate();
    const location = useLocation();
    const query = new URLSearchParams(location.search);
    const notice = query.get('notice');
    const noticeType = query.get('noticeType');

    const [form, setForm] = useState({ email: '', password: '' });
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.post('/login', form, { headers: xhrJson });
            nav(spaNavigatePath(data?.redirect || '/dashboard'));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Login failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <AuthLayout title="Sign in" subtitle="School administration">
            {notice ? (
                <p className={`mb-4 rounded-md px-3 py-2 text-sm ${noticeType === 'error' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-800'}`}>
                    {notice}
                </p>
            ) : null}
            {err ? <p className="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
            <form className="space-y-4" onSubmit={submit}>
                <AuthField label="Email or phone">
                    <AuthInput
                        autoComplete="username"
                        value={form.email}
                        onChange={(e) => setForm({ ...form, email: e.target.value })}
                        required
                    />
                </AuthField>
                <AuthField label="Password">
                    <AuthInput
                        type="password"
                        autoComplete="current-password"
                        value={form.password}
                        onChange={(e) => setForm({ ...form, password: e.target.value })}
                        required
                    />
                </AuthField>
                <AuthButton busy={busy}>{busy ? 'Signing in…' : 'Sign in'}</AuthButton>
            </form>
            <div className="mt-6 flex justify-between text-sm text-slate-600">
                <Link className="font-medium text-blue-600 hover:text-blue-800" to="/register">
                    Create account
                </Link>
                <Link className="font-medium text-blue-600 hover:text-blue-800" to="/forgot-password">
                    Forgot password?
                </Link>
            </div>
        </AuthLayout>
    );
}
