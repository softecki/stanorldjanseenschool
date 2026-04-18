import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';

export function UserFormPage({ edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [meta, setMeta] = useState({});
    const [form, setForm] = useState({});
    const [err, setErr] = useState('');

    useEffect(() => {
        const url = edit ? `/users/edit/${id}` : '/users/create';
        axios
            .get(url, { headers: xhrJson })
            .then((res) => {
                if (edit) {
                    setMeta(res.data?.meta || {});
                    setForm(res.data?.data || {});
                } else {
                    setMeta(res.data?.meta || {});
                    setForm({});
                }
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        try {
            if (edit) await axios.put(`/users/update/${id}`, form, { headers: xhrJson });
            else await axios.post('/users/store', form, { headers: xhrJson });
            nav('/users');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-3xl p-6">
                <h1 className="text-2xl font-bold">{edit ? 'Edit user' : 'Create user'}</h1>
                {err ? <p className="mt-2 text-sm text-red-600">{err}</p> : null}
                <form className="mt-4 grid grid-cols-1 gap-3 rounded border bg-white p-4" onSubmit={submit}>
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
                    {!edit ? (
                        <input
                            type="password"
                            className="rounded border px-3 py-2"
                            placeholder="Password"
                            value={form.password || ''}
                            onChange={(e) => setForm({ ...form, password: e.target.value })}
                        />
                    ) : null}
                    <input
                        className="rounded border px-3 py-2"
                        placeholder="Phone"
                        value={form.phone || form.phone_number || ''}
                        onChange={(e) => setForm({ ...form, phone: e.target.value, phone_number: e.target.value })}
                    />
                    <select className="rounded border px-3 py-2" value={form.role_id || ''} onChange={(e) => setForm({ ...form, role_id: e.target.value })}>
                        <option value="">Select role</option>
                        {(meta.roles || []).map((r) => (
                            <option key={r.id} value={r.id}>
                                {r.name}
                            </option>
                        ))}
                    </select>
                    <button className="rounded bg-blue-600 px-3 py-2 text-white">{edit ? 'Update' : 'Create'}</button>
                </form>
            </div>
        </AdminLayout>
    );
}
