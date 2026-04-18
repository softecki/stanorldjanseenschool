import React, { useEffect, useState } from 'react';
import axios from 'axios';

const xhrJson = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

export function AttendanceReportPage({ Layout }) {
    const [meta, setMeta] = useState({ classes: [] });
    const [sections, setSections] = useState([]);
    const [filters, setFilters] = useState({ class: '', section: '', month: '', date: '', roll: '', view: '0' });
    const [result, setResult] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        axios.get('/attendance/report', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {}));
    }, []);

    useEffect(() => {
        if (!filters.class) {
            setSections([]);
            return;
        }
        axios.get('/class-setup/get-sections', { params: { id: filters.class }, headers: xhrJson }).then((res) => {
            const raw = Array.isArray(res.data) ? res.data : res.data?.data || [];
            setSections(
                raw
                    .map((row) => ({
                        id: row.section_id ?? row.section?.id,
                        name: row.section?.name ?? row.name ?? `Section ${row.section_id}`,
                    }))
                    .filter((s) => s.id)
            );
        });
    }, [filters.class]);

    const runSearch = async (e) => {
        e.preventDefault();
        setErr('');
        setLoading(true);
        try {
            const { data } = await axios.post('/attendance/report-search', filters, { headers: xhrJson });
            setResult(data);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Report failed.');
        } finally {
            setLoading(false);
        }
    };

    const classes = meta.classes || [];
    const students = result?.data?.students?.data || result?.meta?.students?.data || [];
    const days = result?.data?.days || result?.meta?.days || [];

    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">Attendance report</h1>
                    <p className="text-sm text-slate-500">Filter by class, section, and period.</p>
                </div>
                {err ? <p className="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{err}</p> : null}

                <form onSubmit={runSearch} className="grid gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-3 lg:grid-cols-4">
                    <label className="text-sm font-medium text-slate-700">
                        Class *
                        <select
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            value={filters.class}
                            onChange={(e) => setFilters({ ...filters, class: e.target.value, section: '' })}
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
                    <label className="text-sm font-medium text-slate-700">
                        Section *
                        <select
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            value={filters.section}
                            onChange={(e) => setFilters({ ...filters, section: e.target.value })}
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
                    <label className="text-sm font-medium text-slate-700">
                        Month (YYYY-MM)
                        <input
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            placeholder="2026-03"
                            value={filters.month}
                            onChange={(e) => setFilters({ ...filters, month: e.target.value })}
                        />
                    </label>
                    <label className="text-sm font-medium text-slate-700">
                        Date
                        <input
                            type="date"
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            value={filters.date}
                            onChange={(e) => setFilters({ ...filters, date: e.target.value })}
                        />
                    </label>
                    <label className="text-sm font-medium text-slate-700">
                        Roll
                        <input
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            value={filters.roll}
                            onChange={(e) => setFilters({ ...filters, roll: e.target.value })}
                        />
                    </label>
                    <label className="text-sm font-medium text-slate-700">
                        View
                        <select
                            className="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"
                            value={filters.view}
                            onChange={(e) => setFilters({ ...filters, view: e.target.value })}
                        >
                            <option value="0">Calendar</option>
                            <option value="1">List</option>
                        </select>
                    </label>
                    <div className="flex items-end md:col-span-2">
                        <button type="submit" disabled={loading} className="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                            {loading ? 'Loading…' : 'Run report'}
                        </button>
                    </div>
                </form>

                {result ? (
                    <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h2 className="mb-3 text-lg font-semibold text-slate-800">Results</h2>
                        <p className="mb-2 text-xs text-slate-500">Days in month: {Array.isArray(days) ? days.length : '—'}</p>
                        <div className="max-h-96 overflow-auto text-xs">
                            <pre className="whitespace-pre-wrap break-all text-slate-700">{JSON.stringify(result?.data || result?.meta, null, 2)}</pre>
                        </div>
                        {Array.isArray(students) && students.length > 0 ? (
                            <p className="mt-2 text-sm text-slate-600">{students.length} student row(s) returned.</p>
                        ) : null}
                    </div>
                ) : null}
            </div>
        </Layout>
    );
}
