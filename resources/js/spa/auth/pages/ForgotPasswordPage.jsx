import React, { useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AuthButton, AuthField, AuthInput, AuthLayout } from '../AuthLayout';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export default function ForgotPasswordPage() {
    const [email, setEmail] = useState('');
    const [msg, setMsg] = useState('');
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const { data } = await axios.post('/forgot-password', { email }, { headers: xhrJson });
            setMsg(data?.message || 'If the email exists, a reset link was sent.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Request failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <AuthLayout title="Reset password" subtitle="We will email you a link">
            {msg ? <p className="mb-4 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{msg}</p> : null}
            {err ? <p className="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
            <form className="space-y-4" onSubmit={submit}>
                <AuthField label="Email">
                    <AuthInput type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
                </AuthField>
                <AuthButton busy={busy}>{busy ? 'Sending…' : 'Send reset link'}</AuthButton>
            </form>
            <p className="mt-6 text-center text-sm text-slate-600">
                <Link className="font-medium text-blue-600 hover:text-blue-800" to="/login">
                    Back to sign in
                </Link>
            </p>
        </AuthLayout>
    );
}
