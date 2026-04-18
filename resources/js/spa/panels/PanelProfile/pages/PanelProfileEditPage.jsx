import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../PanelProfileModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function PanelProfileEditPage({ Layout, title, loadEndpoint, saveEndpoint }) {
    const [form, setForm] = useState({ first_name: '', last_name: '', phone: '', email: '' });
    const [msg, setMsg] = useState('');
    const [err, setErr] = useState('');

    useEffect(() => {
        axios.get(loadEndpoint, { headers: xhrJson })
            .then((r) => {
                const user = r.data?.data?.user || {};
                setForm({
                    first_name: user.first_name || '',
                    last_name: user.last_name || '',
                    phone: user.phone || '',
                    email: user.email || '',
                });
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load form.'));
    }, [loadEndpoint]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setMsg('');
        try {
            const { data } = await axios.put(saveEndpoint, form, { headers: xhrJson });
            setMsg(data?.message || 'Saved successfully.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        }
    };

    return (
        <Shell Layout={Layout} title={title}>
            {msg ? <p className="text-sm text-emerald-700">{msg}</p> : null}
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid gap-3 rounded border bg-white p-4">
                <input className="rounded border px-3 py-2" placeholder="First name" value={form.first_name} onChange={(e) => setForm({ ...form, first_name: e.target.value })} />
                <input className="rounded border px-3 py-2" placeholder="Last name" value={form.last_name} onChange={(e) => setForm({ ...form, last_name: e.target.value })} />
                <input className="rounded border px-3 py-2" placeholder="Phone" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                <input className="rounded border px-3 py-2" placeholder="Email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                <button className="w-fit rounded bg-blue-600 px-4 py-2 text-sm text-white">Save</button>
            </form>
        </Shell>
    );
}

