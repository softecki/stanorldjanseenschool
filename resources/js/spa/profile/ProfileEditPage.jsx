import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';

export function ProfileEditPage() {
    const nav = useNavigate();
    const [form, setForm] = useState({});
    const [err, setErr] = useState('');
    useEffect(() => {
        axios.get('/my/profile/edit', { headers: xhrJson }).then((r) => setForm(r.data?.data || {}));
    }, []);
    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        try {
            await axios.put('/my/profile/update', form, { headers: xhrJson });
            nav('/my/profile');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Update failed.');
        }
    };
    return (
        <AdminLayout>
            <div className="mx-auto max-w-3xl p-6">
                <h1 className="text-2xl font-bold">Edit Profile</h1>
                {err ? <p className="mt-2 text-sm text-red-600">{err}</p> : null}
                <form className="mt-4 grid gap-3 rounded border bg-white p-4" onSubmit={submit}>
                    <input
                        className="rounded border px-3 py-2"
                        placeholder="First name"
                        value={form.first_name || ''}
                        onChange={(e) => setForm({ ...form, first_name: e.target.value })}
                    />
                    <input
                        className="rounded border px-3 py-2"
                        placeholder="Last name"
                        value={form.last_name || ''}
                        onChange={(e) => setForm({ ...form, last_name: e.target.value })}
                    />
                    <input
                        className="rounded border px-3 py-2"
                        placeholder="Email"
                        value={form.email || ''}
                        onChange={(e) => setForm({ ...form, email: e.target.value })}
                    />
                    <button type="submit" className="rounded bg-blue-600 px-3 py-2 text-white">
                        Save
                    </button>
                </form>
            </div>
        </AdminLayout>
    );
}
