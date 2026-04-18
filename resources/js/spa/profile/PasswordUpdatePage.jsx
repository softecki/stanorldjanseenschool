import React, { useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';

export function PasswordUpdatePage() {
    const nav = useNavigate();
    const [form, setForm] = useState({ current_password: '', password: '', password_confirmation: '' });
    const [err, setErr] = useState('');
    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        try {
            await axios.put('/my/password/update/store', form, { headers: xhrJson });
            nav('/my/profile');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Update failed.');
        }
    };
    return (
        <AdminLayout>
            <div className="mx-auto max-w-xl p-6">
                <h1 className="text-2xl font-bold">Update Password</h1>
                {err ? <p className="mt-2 text-sm text-red-600">{err}</p> : null}
                <form className="mt-4 grid gap-3 rounded border bg-white p-4" onSubmit={submit}>
                    <input
                        type="password"
                        className="rounded border px-3 py-2"
                        placeholder="Current password"
                        value={form.current_password}
                        onChange={(e) => setForm({ ...form, current_password: e.target.value })}
                    />
                    <input
                        type="password"
                        className="rounded border px-3 py-2"
                        placeholder="New password"
                        value={form.password}
                        onChange={(e) => setForm({ ...form, password: e.target.value })}
                    />
                    <input
                        type="password"
                        className="rounded border px-3 py-2"
                        placeholder="Confirm password"
                        value={form.password_confirmation}
                        onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                    />
                    <button type="submit" className="rounded bg-blue-600 px-3 py-2 text-white">
                        Save
                    </button>
                </form>
            </div>
        </AdminLayout>
    );
}
