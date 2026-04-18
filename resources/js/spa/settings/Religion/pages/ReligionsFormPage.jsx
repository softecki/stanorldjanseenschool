import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell } from '../../ReligionModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function ReligionsFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({ name: '', status: 1 });
    const [err, setErr] = useState('');

    useEffect(() => {
        const url = edit ? `/religions/edit/${id}` : '/religions/create';
        axios.get(url, { headers: xhrJson }).then((r) => {
            const rel = r.data?.data?.religion || r.data?.data || {};
            if (edit) setForm({ name: rel.name || '', status: rel.status ? 1 : 0 });
        }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'));
    }, [edit, id]);

    const submit = async (e) => {
        e.preventDefault();
        if (edit) await axios.put(`/religions/update/${id}`, form, { headers: xhrJson });
        else await axios.post('/religions/store', form, { headers: xhrJson });
        nav('/settings/religions');
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold">{edit ? 'Edit religion' : 'Create religion'}</h1>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid max-w-xl gap-3 rounded border bg-white p-4">
                <input className="rounded border px-3 py-2" placeholder="Name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
                <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={!!form.status} onChange={(e) => setForm({ ...form, status: e.target.checked ? 1 : 0 })} /> Active</label>
                <button className="rounded bg-blue-600 px-3 py-2 text-white">{edit ? 'Update' : 'Create'}</button>
            </form>
        </Shell>
    );
}

