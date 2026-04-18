import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';

import { UiButton, UiHeadRow, UiTable, UiTableWrap, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../../ui/UiKit';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

function studentKey(row) {
    if (row.student_id != null) return row.student_id;
    if (row.student?.id != null) return row.student.id;
    return row.id;
}

export function AttendanceIndexPage({ Layout }) {
    const [meta, setMeta] = useState({ classes: [] });
    const [sections, setSections] = useState([]);
    const [date, setDate] = useState(() => new Date().toISOString().slice(0, 10));
    const [classId, setClassId] = useState('');
    const [sectionId, setSectionId] = useState('');
    const [status, setStatus] = useState(0);
    const [rows, setRows] = useState([]);
    const [attendance, setAttendance] = useState({});
    const [note, setNote] = useState({});
    const [items, setItems] = useState({});
    const [rollMap, setRollMap] = useState({});
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        axios.get('/attendance', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
    }, []);

    useEffect(() => {
        if (!classId) {
            setSections([]);
            setSectionId('');
            return;
        }
        axios.get('/class-setup/get-sections', { params: { id: classId }, headers: xhrJson }).then((res) => {
            const raw = Array.isArray(res.data) ? res.data : res.data?.data || [];
            const list = raw.map((row) => ({
                id: row.section_id ?? row.section?.id,
                name: row.section?.name ?? row.name ?? `Section ${row.section_id}`,
            }));
            setSections(list.filter((s) => s.id));
            setSectionId('');
        });
    }, [classId]);

    const loadStudents = async (e) => {
        e?.preventDefault();
        setErr('');
        setMsg('');
        setLoading(true);
        try {
            const { data } = await axios.post(
                '/attendance/search',
                { date, class: classId, section: sectionId },
                { headers: xhrJson }
            );
            const list = data?.data?.students || data?.meta?.students || [];
            const st = data?.data?.status ?? data?.meta?.status ?? 0;
            setStatus(Number(st));
            const normalized = Array.isArray(list) ? list : [];
            setRows(normalized);
            const nextAtt = {};
            const nextNote = {};
            const nextItems = {};
            const nextRoll = {};
            normalized.forEach((row, idx) => {
                const sid = studentKey(row);
                nextRoll[idx] = row.roll ?? row.student?.roll_no ?? '';
                if (row.attendance != null) nextAtt[sid] = String(row.attendance);
                else nextAtt[sid] = '1';
                nextNote[idx] = row.note ?? '';
                if (row.id) nextItems[idx] = row.id;
            });
            setAttendance(nextAtt);
            setNote(nextNote);
            setItems(nextItems);
            setRollMap(nextRoll);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Could not load students.');
        } finally {
            setLoading(false);
        }
    };

    const studentIds = useMemo(() => rows.map((row) => studentKey(row)), [rows]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setMsg('');
        try {
            const fd = new FormData();
            fd.append('date', date);
            fd.append('class', classId);
            fd.append('section', sectionId);
            fd.append('status', String(status));
            studentIds.forEach((id) => fd.append('students[]', id));
            studentIds.forEach((id) => fd.append(`attendance[${id}]`, attendance[id] ?? '1'));
            rows.forEach((_, idx) => {
                fd.append('studentsRoll[]', rollMap[idx] ?? '');
                fd.append('note[]', note[idx] ?? '');
                if (status === 1 && items[idx]) fd.append('items[]', items[idx]);
            });
            await axios.post('/attendance/store', fd, {
                headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' },
            });
            setMsg('Attendance saved.');
            loadStudents();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Save failed.');
        }
    };

    const classes = meta.classes || [];

    return (
        <Layout>
            <div className="mx-auto max-w-6xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Attendance'}</h1>
                    <p className="text-sm text-gray-500">Select class, section, and date, then load students.</p>
                </div>
                {err ? <p className="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}
                {msg ? <p className="rounded-md bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{msg}</p> : null}

                <form onSubmit={loadStudents} className="grid gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm md:grid-cols-4">
                    <label className="text-sm font-medium text-gray-700">
                        Date
                        <input type="date" className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2" value={date} onChange={(e) => setDate(e.target.value)} required />
                    </label>
                    <label className="text-sm font-medium text-gray-700">
                        Class
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"
                            value={classId}
                            onChange={(e) => setClassId(e.target.value)}
                            required
                        >
                            <option value="">Choose…</option>
                            {classes.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className="text-sm font-medium text-gray-700">
                        Section
                        <select
                            className="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2"
                            value={sectionId}
                            onChange={(e) => setSectionId(e.target.value)}
                            required
                        >
                            <option value="">Choose…</option>
                            {sections.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.name}
                                </option>
                            ))}
                        </select>
                    </label>
                    <div className="flex items-end">
                        <UiButton type="submit" disabled={loading} className="w-full">
                            {loading ? 'Loading…' : 'Load students'}
                        </UiButton>
                    </div>
                </form>

                {rows.length > 0 ? (
                    <form onSubmit={submit} className="space-y-4">
                        <UiTableWrap>
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH>Student</UiTH>
                                        <UiTH>Roll</UiTH>
                                        <UiTH>Status</UiTH>
                                        <UiTH>Note</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.map((row, idx) => {
                                        const sid = studentKey(row);
                                        const name = row.student
                                            ? `${row.student.first_name || ''} ${row.student.last_name || ''}`.trim()
                                            : row.name || `Student ${sid}`;
                                        return (
                                            <UiTR key={`${sid}-${idx}`}>
                                                <UiTD>{name}</UiTD>
                                                <UiTD>
                                                    <input
                                                        className="w-24 rounded-lg border border-gray-200 px-2 py-1"
                                                        value={rollMap[idx] ?? ''}
                                                        onChange={(e) => setRollMap((m) => ({ ...m, [idx]: e.target.value }))}
                                                    />
                                                </UiTD>
                                                <UiTD>
                                                    <select
                                                        className="rounded-lg border border-gray-200 px-2 py-1"
                                                        value={attendance[sid] ?? '1'}
                                                        onChange={(e) => setAttendance((a) => ({ ...a, [sid]: e.target.value }))}
                                                    >
                                                        <option value="1">Present</option>
                                                        <option value="0">Absent</option>
                                                        <option value="2">Late</option>
                                                    </select>
                                                </UiTD>
                                                <UiTD>
                                                    <input
                                                        className="w-full max-w-xs rounded-lg border border-gray-200 px-2 py-1"
                                                        value={note[idx] ?? ''}
                                                        onChange={(e) => setNote((n) => ({ ...n, [idx]: e.target.value }))}
                                                    />
                                                </UiTD>
                                            </UiTR>
                                        );
                                    })}
                                </UiTBody>
                            </UiTable>
                        </UiTableWrap>
                        <UiButton
                            type="submit"
                            variant="primary"
                            className="bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500"
                        >
                            Save attendance
                        </UiButton>
                    </form>
                ) : null}
            </div>
        </Layout>
    );
}
