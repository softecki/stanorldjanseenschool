import React, { useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AuthButton, AuthField, AuthInput, AuthLayout } from '../AuthLayout';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export default function RegisterPage() {
    const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
    const [msg, setMsg] = useState('');
    const [err, setErr] = useState('');
    const [busy, setBusy] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setBusy(true);
        try {
            const { data } = await axios.post('/register', form, { headers: xhrJson });
            setMsg(data?.message || 'Check your email to verify your account.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Registration failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <AuthLayout title="Create account" subtitle="Register for access">
            {msg ? <p className="mb-4 rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{msg}</p> : null}
            {err ? <p className="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
            <form className="space-y-4" onSubmit={submit}>
                <AuthField label="Full name">
                    <AuthInput value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
                </AuthField>
                <AuthField label="Email">
                    <AuthInput type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} required />
                </AuthField>
                <AuthField label="Password">
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
                <AuthButton busy={busy}>{busy ? 'Submitting…' : 'Register'}</AuthButton>
            </form>
            <p className="mt-6 text-center text-sm text-slate-600">
                Already have an account?{' '}
                <Link className="font-medium text-blue-600 hover:text-blue-800" to="/login">
                    Sign in
                </Link>
            </p>
        </AuthLayout>
    );
}
