import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../PanelProfileModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function PanelPasswordPage({ Layout, title, saveEndpoint }) {
    const [form, setForm] = useState({ current_password: '', password: '', password_confirmation: '' });
    const [msg, setMsg] = useState('');
    const [err, setErr] = useState('');

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setMsg('');
        try {
            const { data } = await axios.put(saveEndpoint, form, { headers: xhrJson });
            setMsg(data?.message || 'Password updated.');
            setForm({ current_password: '', password: '', password_confirmation: '' });
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Password update failed.');
        }
    };

    return (
        <Shell Layout={Layout} title={title}>
            {msg ? <p className="text-sm text-emerald-700">{msg}</p> : null}
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <form onSubmit={submit} className="grid gap-3 rounded border bg-white p-4">
                <input type="password" className="rounded border px-3 py-2" placeholder="Current password" value={form.current_password} onChange={(e) => setForm({ ...form, current_password: e.target.value })} />
                <input type="password" className="rounded border px-3 py-2" placeholder="New password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
                <input type="password" className="rounded border px-3 py-2" placeholder="Confirm password" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} />
                <button className="w-fit rounded bg-blue-600 px-4 py-2 text-sm text-white">Update password</button>
            </form>
        </Shell>
    );
}

