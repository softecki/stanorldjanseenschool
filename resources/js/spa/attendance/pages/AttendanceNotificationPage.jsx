import React, { useEffect, useState } from 'react';
import axios from 'axios';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export function AttendanceNotificationPage({ Layout }) {
    const [meta, setMeta] = useState({ roles: [], shifts: [], setting: null });
    const [shiftIds, setShiftIds] = useState([]);
    const [sendingTimes, setSendingTimes] = useState({});
    const [notifyStudent, setNotifyStudent] = useState(0);
    const [notifyGuardian, setNotifyGuardian] = useState(0);
    const [activeStatus, setActiveStatus] = useState(0);
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        axios.get('/attendance-notification', { headers: xhrJson }).then((r) => {
            const m = r.data?.meta || {};
            setMeta(m);
            const shifts = m.shifts?.data || m.shifts || [];
            const arr = Array.isArray(shifts) ? shifts : [];
            setShiftIds(arr.map((s) => String(s.id)));
            const st = m.setting?.sending_time || {};
            const next = {};
            arr.forEach((s) => {
                next[s.id] = st[s.id] ?? st[String(s.id)] ?? '';
            });
            setSendingTimes(next);
            setNotifyStudent(Number(m.setting?.notify_student ?? 0));
            setNotifyGuardian(Number(m.setting?.notify_gurdian ?? m.setting?.notify_guardian ?? 0));
            setActiveStatus(Number(m.setting?.active_status ?? 0));
        });
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setMsg('');
        setBusy(true);
        try {
            const fd = new FormData();
            shiftIds.forEach((id) => fd.append('shift_ids[]', id));
            shiftIds.forEach((id) => fd.append('sending_times[]', sendingTimes[id] ?? ''));
            fd.append('notify_student', String(notifyStudent));
            fd.append('notify_gurdian', String(notifyGuardian));
            fd.append('active_status', String(activeStatus));
            fd.append('notification_message', 'Absent notification');
            await axios.post('/attendance-notification/update', fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
            setMsg('Settings saved.');
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Update failed.');
        } finally {
            setBusy(false);
        }
    };

    const shifts = meta.shifts?.data || meta.shifts || [];
    const shiftList = Array.isArray(shifts) ? shifts : [];

    return (
        <Layout>
            <div className="mx-auto max-w-3xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">{meta.pt || 'Absent notification'}</h1>
                    <p className="text-sm text-slate-500">Configure absence alerts per shift.</p>
                </div>
                {err ? <p className="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {msg ? <p className="rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{msg}</p> : null}

                <form onSubmit={submit} className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={!!notifyStudent} onChange={(e) => setNotifyStudent(e.target.checked ? 1 : 0)} className="rounded border-slate-300" />
                            Notify student
                        </label>
                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={!!notifyGuardian} onChange={(e) => setNotifyGuardian(e.target.checked ? 1 : 0)} className="rounded border-slate-300" />
                            Notify guardian
                        </label>
                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={!!activeStatus} onChange={(e) => setActiveStatus(e.target.checked ? 1 : 0)} className="rounded border-slate-300" />
                            Active
                        </label>
                    </div>

                    <div className="space-y-3">
                        <h2 className="text-sm font-semibold text-slate-800">Sending times by shift</h2>
                        {shiftList.map((s) => (
                            <div key={s.id} className="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
                                <span className="w-40 text-sm text-slate-600">{s.name || `Shift ${s.id}`}</span>
                                <input
                                    type="time"
                                    className="rounded-md border border-slate-300 px-3 py-2 text-sm"
                                    value={sendingTimes[s.id] ?? ''}
                                    onChange={(e) => setSendingTimes((prev) => ({ ...prev, [s.id]: e.target.value }))}
                                />
                            </div>
                        ))}
                    </div>

                    <button type="submit" disabled={busy} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        {busy ? 'Saving…' : 'Save settings'}
                    </button>
                </form>
            </div>
        </Layout>
    );
}
