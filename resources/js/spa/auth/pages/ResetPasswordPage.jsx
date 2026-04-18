import React, { useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { AuthButton, AuthField, AuthInput, AuthLayout } from '../AuthLayout';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export default function ResetPasswordPage() {
    const { email = '', token = '' } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({ email, token, password: '', password_confirmation: '' });
    const [msg, setMsg] = useState('');
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const { data } = await axios.post('/reset-password', form, { headers: xhrJson });
            setMsg(data?.message || 'Password updated.');
            setTimeout(() => nav('/login'), 1500);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Reset failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <AuthLayout title="New password" subtitle="Choose a strong password">
            {msg ? <p className="mb-4 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{msg}</p> : null}
            {err ? <p className="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
            <form className="space-y-4" onSubmit={submit}>
                <AuthField label="Email">
                    <AuthInput value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
                </AuthField>
                <input type="hidden" name="token" value={form.token} readOnly />
                <AuthField label="New password">
                    <AuthInput type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} required />
                </AuthField>
                <AuthField label="Confirm password">
                    <AuthInput
                        type="password"
                        value={form.password_confirmation}
                        onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                        required
                    />
                </AuthField>
                <AuthButton busy={busy}>{busy ? 'Saving…' : 'Update password'}</AuthButton>
            </form>
            <p className="mt-6 text-center text-sm text-slate-600">
                <Link className="font-medium text-blue-600 hover:text-blue-800" to="/login">
                    Back to sign in
                </Link>
            </p>
        </AuthLayout>
    );
}
