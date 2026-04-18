import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell } from '../../SessionModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function SessionsFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({ title: '', status: 1 });

    useEffect(() => {
        const url = edit ? `/sessions/edit/${id}` : '/sessions/create';
        axios.get(url, { headers: xhrJson }).then((r) => {
            const item = r.data?.data?.session || r.data?.data || {};
            if (edit) setForm({ title: item.title || item.name || '', status: item.status ? 1 : 0 });
        });
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        if (edit) await axios.put(`/sessions/update/${id}`, form, { headers: xhrJson });
        else await axios.post('/sessions/store', form, { headers: xhrJson });
        nav('/settings/sessions');
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold">{edit ? 'Edit session' : 'Create session'}</h1>
            <form onSubmit={submit} className="grid max-w-xl gap-3 rounded border bg-white p-4">
                <input className="rounded border px-3 py-2" placeholder="Title" value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} required />
                <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={!!form.status} onChange={(e) => setForm({ ...form, status: e.target.checked ? 1 : 0 })} /> Active</label>
                <button className="rounded bg-blue-600 px-3 py-2 text-white">{edit ? 'Update' : 'Create'}</button>
            </form>
        </Shell>
    );
}

