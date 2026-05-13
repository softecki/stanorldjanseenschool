import React, { useCallback, useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';
import {
    AccountCard,
    AccountEmptyState,
    AccountFullPageLoader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../accounts/components/AccountUi';

function rowsFrom(value) {
    if (Array.isArray(value)) return value;
    if (Array.isArray(value?.data)) return value.data;
    return [];
}

function paginationFrom(value) {
    if (!value || Array.isArray(value)) return null;
    return value.current_page ? value : null;
}

function classOptionsFrom(classes = []) {
    return (classes || [])
        .map((item) => ({
            id: String(item?.class?.id ?? item?.classes_id ?? item?.id ?? ''),
            name: item?.class?.name ?? item?.name ?? '—',
        }))
        .filter((item) => item.id);
}

function sectionOptionsFrom(sections = []) {
    return (sections || [])
        .map((item) => ({
            id: String(item?.section?.id ?? item?.section_id ?? item?.id ?? ''),
            name: item?.section?.name ?? item?.name ?? '—',
        }))
        .filter((item) => item.id);
}

function studentOptionsFrom(students = []) {
    return (students || [])
        .map((item) => {
            const student = item?.student || item;
            return {
                id: String(student?.id ?? item?.student_id ?? ''),
                name: `${student?.first_name ?? ''} ${student?.last_name ?? ''}`.trim() || student?.name || '—',
                admission: student?.admission_no || '',
            };
        })
        .filter((item) => item.id);
}

function examTypeOptionsFrom(types = []) {
    return (types || [])
        .map((item) => ({ id: String(item?.id ?? ''), name: item?.name ?? item?.title ?? '—' }))
        .filter((item) => item.id);
}

function money(value) {
    const number = Number(value ?? 0);
    if (!Number.isFinite(number)) return '0.00';
    return number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function makeDateRange(filters) {
    return filters.date_from && filters.date_to ? `${filters.date_from} - ${filters.date_to}` : '';
}

function queryString(params) {
    const search = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') search.set(key, value);
    });
    return search.toString();
}

function StatCard({ label, value, tone = 'border-blue-100 bg-blue-50 text-blue-900' }) {
    return (
        <div className={`rounded-2xl border p-5 shadow-sm ${tone}`}>
            <p className="text-sm font-medium opacity-80">{label}</p>
            <p className="mt-2 text-2xl font-bold tracking-tight">{value}</p>
        </div>
    );
}

function ReportHero({ title, kicker, actions, statLabel, statValue }) {
    return (
        <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.3em] text-blue-200">{kicker}</p>
                    <h1 className="mt-3 text-2xl font-bold tracking-tight">{title}</h1>
                    <div className="mt-5 flex flex-wrap gap-2">
                        {actions}
                        <Link to="/reports" className="rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15">
                            All reports
                        </Link>
                    </div>
                </div>
                <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                    <p className="text-3xl font-bold">{statValue}</p>
                    <p className="mt-1 text-xs font-medium uppercase tracking-wide text-blue-100">{statLabel}</p>
                </div>
            </div>
        </div>
    );
}

function ExportButton({ href, children, primary = false }) {
    if (!href) return null;
    return (
        <a
            href={href}
            className={primary
                ? 'rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-blue-50'
                : 'rounded-lg border border-white/20 bg-white/10 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-white/15'}
        >
            {children}
        </a>
    );
}

function Field({ label, children, className = '' }) {
    return (
        <label className={`block text-sm font-medium text-slate-700 ${className}`}>
            {label}
            {children}
        </label>
    );
}

function selectClassName(extra = '') {
    return `mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 ${extra}`;
}

function Pagination({ pagination, onPage }) {
    if (!pagination || pagination.last_page <= 1) return null;
    return (
        <div className="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 p-4 text-sm">
            <button type="button" onClick={() => onPage(pagination.current_page - 1)} disabled={pagination.current_page <= 1} className="rounded-lg border border-slate-200 px-3 py-1.5 disabled:opacity-50">
                Previous
            </button>
            <span className="text-slate-500">Page {pagination.current_page} of {pagination.last_page}</span>
            <button type="button" onClick={() => onPage(pagination.current_page + 1)} disabled={pagination.current_page >= pagination.last_page} className="rounded-lg border border-slate-200 px-3 py-1.5 disabled:opacity-50">
                Next
            </button>
        </div>
    );
}

export function AccountReportPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [pagination, setPagination] = useState(null);
    const [filters, setFilters] = useState({ type: '1', head: '', date_from: '', date_to: '' });
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');
    const [searched, setSearched] = useState(false);

    const heads = useMemo(() => meta.account_head || [], [meta.account_head]);
    const dateRange = makeDateRange(filters);
    const exportQuery = queryString({ type: filters.type, head: filters.head, date: dateRange });
    const pdfUrl = meta.pdf_download_url || (searched ? `/report-account/pdf-generate?${exportQuery}` : '');
    const excelUrl = meta.excel_download_url || (searched ? `/report-account/excel-generate?${exportQuery}` : '');

    const applyResponse = (payload) => {
        setRows(rowsFrom(payload?.data));
        setPagination(paginationFrom(payload?.data));
        setMeta(payload?.meta || {});
    };

    const loadIndex = useCallback(async () => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-account', { headers: xhrJson });
            setMeta(data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load account report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadIndex();
    }, [loadIndex]);

    const changeType = async (type) => {
        setFilters((current) => ({ ...current, type, head: '' }));
        try {
            const { data } = await axios.get('/report-account/get-account-types', { headers: xhrJson, params: { id: type } });
            setMeta((current) => ({ ...current, account_head: data || [] }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load account heads.');
        }
    };

    const search = async (e, page = 1) => {
        if (e?.preventDefault) e.preventDefault();
        setBusy(true);
        setErr('');
        try {
            const payload = { type: filters.type, head: filters.head, dates: dateRange };
            const { data } = await axios.post(`/report-account/search?page=${page}`, payload, { headers: xhrJson });
            applyResponse(data);
            setSearched(true);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search account report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <ReportHero
                    title="Account Report"
                    kicker="Accounting report"
                    statLabel="Records"
                    statValue={pagination?.total ?? rows.length}
                    actions={<><ExportButton href={pdfUrl} primary>Print PDF</ExportButton><ExportButton href={excelUrl}>Export Excel</ExportButton></>}
                />
                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}
                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard label="Total amount" value={money(meta.sum)} />
                    <StatCard label="Bank total" value={money(meta.bank)} tone="border-indigo-100 bg-indigo-50 text-indigo-900" />
                    <StatCard label="Cash total" value={money(meta.cash)} tone="border-emerald-100 bg-emerald-50 text-emerald-900" />
                </div>
                <AccountCard>
                    <form onSubmit={search} className="space-y-5 border-b border-slate-100 p-5">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <Field label="Account type">
                                <select className={selectClassName()} value={filters.type} onChange={(e) => changeType(e.target.value)}>
                                    <option value="1">Income</option>
                                    <option value="2">Expense</option>
                                </select>
                            </Field>
                            <Field label="Account head">
                                <select className={selectClassName()} value={filters.head} onChange={(e) => setFilters((current) => ({ ...current, head: e.target.value }))}>
                                    <option value="">All heads</option>
                                    {heads.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                </select>
                            </Field>
                            <Field label="From">
                                <input type="date" className={selectClassName()} value={filters.date_from} onChange={(e) => setFilters((current) => ({ ...current, date_from: e.target.value }))} />
                            </Field>
                            <Field label="To">
                                <input type="date" className={selectClassName()} value={filters.date_to} onChange={(e) => setFilters((current) => ({ ...current, date_to: e.target.value }))} />
                            </Field>
                        </div>
                        <div className="flex justify-end">
                            <button type="submit" disabled={busy} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                                {busy ? 'Working...' : 'Apply filters'}
                            </button>
                        </div>
                    </form>
                    <div className="p-2">
                        {loading ? <AccountFullPageLoader text="Loading account report..." /> : rows.length === 0 ? <div className="p-4"><AccountEmptyState message="Apply filters to view account records." /></div> : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR><AccountTH>#</AccountTH><AccountTH>Date</AccountTH><AccountTH>Name</AccountTH><AccountTH>Head</AccountTH><AccountTH>Description</AccountTH><AccountTH className="text-right">Amount</AccountTH></AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={`${row.id}-${index}`}>
                                            <AccountTD>{(pagination?.per_page || rows.length) * ((pagination?.current_page || 1) - 1) + index + 1}</AccountTD>
                                            <AccountTD>{row.date || '—'}</AccountTD>
                                            <AccountTD>{row.name || '—'}</AccountTD>
                                            <AccountTD>{row.head?.name || '—'}</AccountTD>
                                            <AccountTD>{row.description || '—'}</AccountTD>
                                            <AccountTD className="text-right font-semibold">{money(row.amount)}</AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>
                    <Pagination pagination={pagination} onPage={(page) => search(null, page)} />
                </AccountCard>
            </div>
        </AdminLayout>
    );
}

export function MarksheetReportPage() {
    const [result, setResult] = useState(null);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState({ class: '', section: '', student: '', exam_type: '' });
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');

    const classes = useMemo(() => classOptionsFrom(meta.classes || []), [meta.classes]);
    const sections = useMemo(() => sectionOptionsFrom(meta.sections || []), [meta.sections]);
    const students = useMemo(() => studentOptionsFrom(meta.students || []), [meta.students]);
    const examTypes = useMemo(() => examTypeOptionsFrom(meta.exam_types || []), [meta.exam_types]);
    const subjects = rowsFrom(result?.marks_registers);

    const loadIndex = useCallback(async () => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-marksheet', { headers: xhrJson });
            setMeta(data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load marksheet report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadIndex();
    }, [loadIndex]);

    const loadSections = async (classId) => {
        setFilters((current) => ({ ...current, class: classId, section: '', student: '' }));
        if (!classId) {
            setMeta((current) => ({ ...current, sections: [], students: [] }));
            return;
        }
        const { data } = await axios.get(`/report-marksheet/sections/${classId}`, { headers: xhrJson });
        setMeta((current) => ({ ...current, sections: data?.data || [], students: [] }));
    };

    const loadStudents = async (sectionId) => {
        setFilters((current) => ({ ...current, section: sectionId, student: '' }));
        if (!filters.class || !sectionId) {
            setMeta((current) => ({ ...current, students: [] }));
            return;
        }
        const { data } = await axios.get('/report-marksheet/get-students', { headers: xhrJson, params: { class: filters.class, section: sectionId } });
        setMeta((current) => ({ ...current, students: rowsFrom(data) }));
    };

    const search = async (e) => {
        if (e?.preventDefault) e.preventDefault();
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.post('/report-marksheet/search', filters, { headers: xhrJson });
            setResult(data?.data || {});
            setMeta(data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Please select class, section, student, and exam type.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <ReportHero
                    title="Marksheet Report"
                    kicker="Academic report"
                    statLabel="Total marks"
                    statValue={result ? money(result.total_marks).replace('.00', '') : 0}
                    actions={<><ExportButton href={meta.pdf_download_url} primary>Print PDF</ExportButton><ExportButton href={meta.excel_download_url}>Export Excel</ExportButton></>}
                />
                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}
                <AccountCard>
                    <form onSubmit={search} className="space-y-5 border-b border-slate-100 p-5">
                        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <Field label="Class">
                                <select className={selectClassName()} value={filters.class} onChange={(e) => loadSections(e.target.value)}>
                                    <option value="">Select class</option>
                                    {classes.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                </select>
                            </Field>
                            <Field label="Section">
                                <select className={selectClassName()} value={filters.section} onChange={(e) => loadStudents(e.target.value)} disabled={!filters.class}>
                                    <option value="">Select section</option>
                                    {sections.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                </select>
                            </Field>
                            <Field label="Student">
                                <select className={selectClassName()} value={filters.student} onChange={(e) => setFilters((current) => ({ ...current, student: e.target.value }))} disabled={!filters.section}>
                                    <option value="">Select student</option>
                                    {students.map((item) => <option key={item.id} value={item.id}>{item.name}{item.admission ? ` (${item.admission})` : ''}</option>)}
                                </select>
                            </Field>
                            <Field label="Exam type">
                                <select className={selectClassName()} value={filters.exam_type} onChange={(e) => setFilters((current) => ({ ...current, exam_type: e.target.value }))}>
                                    <option value="">Select exam type</option>
                                    {examTypes.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                </select>
                            </Field>
                        </div>
                        <div className="flex justify-end">
                            <button type="submit" disabled={busy} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                                {busy ? 'Working...' : 'Generate marksheet'}
                            </button>
                        </div>
                    </form>
                    <div className="space-y-4 p-5">
                        {loading ? <AccountFullPageLoader text="Loading marksheet report..." /> : !result ? <AccountEmptyState message="Use the filters to generate a marksheet." /> : (
                            <>
                                <div className="grid gap-4 md:grid-cols-4">
                                    <StatCard label="Average marks" value={money(result.avg_marks).replace('.00', '')} />
                                    <StatCard label="Result" value={result.result || '—'} tone="border-emerald-100 bg-emerald-50 text-emerald-900" />
                                    <StatCard label="GPA" value={result.gpa || '—'} tone="border-indigo-100 bg-indigo-50 text-indigo-900" />
                                    <StatCard label="Position" value={`${result.position || 0} / ${result.max_position || 0}`} tone="border-violet-100 bg-violet-50 text-violet-900" />
                                </div>
                                <AccountTable>
                                    <AccountTHead>
                                        <AccountTR><AccountTH>#</AccountTH><AccountTH>Subject</AccountTH><AccountTH className="text-right">Marks</AccountTH></AccountTR>
                                    </AccountTHead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {subjects.map((item, index) => (
                                            <AccountTR key={`${item.id}-${index}`}>
                                                <AccountTD>{index + 1}</AccountTD>
                                                <AccountTD>{item.subject?.name || '—'}</AccountTD>
                                                <AccountTD className="text-right font-semibold">{rowsFrom(item.marks_register_childs || item.marksRegisterChilds).reduce((sum, child) => sum + Number(child.mark || 0), 0)}</AccountTD>
                                            </AccountTR>
                                        ))}
                                    </tbody>
                                </AccountTable>
                            </>
                        )}
                    </div>
                </AccountCard>
            </div>
        </AdminLayout>
    );
}

export function MeritListReportPage() {
    const [rows, setRows] = useState([]);
    const [subjects, setSubjects] = useState([]);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState({ class: '', section: '0', exam_type: '' });
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');

    const classes = useMemo(() => classOptionsFrom(meta.classes || []), [meta.classes]);
    const sections = useMemo(() => sectionOptionsFrom(meta.sections || []), [meta.sections]);
    const examTypes = useMemo(() => examTypeOptionsFrom(meta.exam_types || []), [meta.exam_types]);

    const loadIndex = useCallback(async () => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-merit-list', { headers: xhrJson });
            setMeta(data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load merit list report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadIndex();
    }, [loadIndex]);

    const loadSections = async (classId) => {
        setFilters((current) => ({ ...current, class: classId, section: '0' }));
        if (!classId) {
            setMeta((current) => ({ ...current, sections: [] }));
            return;
        }
        const { data } = await axios.get(`/report-merit-list/sections/${classId}`, { headers: xhrJson });
        setMeta((current) => ({ ...current, sections: data?.data || [] }));
    };

    const search = async (e) => {
        if (e?.preventDefault) e.preventDefault();
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.post('/report-merit-list/search', filters, { headers: xhrJson });
            setRows(rowsFrom(data?.data?.results));
            setSubjects(data?.data?.subjects || []);
            setMeta(data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Please select class and exam type.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <ReportHero
                    title="Merit List Report"
                    kicker="Academic report"
                    statLabel="Students"
                    statValue={rows.length}
                    actions={<><ExportButton href={meta.pdf_download_url} primary>Print PDF</ExportButton><ExportButton href={meta.excel_download_url}>Export Excel</ExportButton></>}
                />
                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}
                <AccountCard>
                    <form onSubmit={search} className="space-y-5 border-b border-slate-100 p-5">
                        <div className="grid gap-4 md:grid-cols-3">
                            <Field label="Class">
                                <select className={selectClassName()} value={filters.class} onChange={(e) => loadSections(e.target.value)}>
                                    <option value="">Select class</option>
                                    {classes.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                </select>
                            </Field>
                            <Field label="Section">
                                <select className={selectClassName()} value={filters.section} onChange={(e) => setFilters((current) => ({ ...current, section: e.target.value }))} disabled={!filters.class}>
                                    <option value="0">All sections</option>
                                    {sections.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                </select>
                            </Field>
                            <Field label="Exam type">
                                <select className={selectClassName()} value={filters.exam_type} onChange={(e) => setFilters((current) => ({ ...current, exam_type: e.target.value }))}>
                                    <option value="">Select exam type</option>
                                    {examTypes.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                                </select>
                            </Field>
                        </div>
                        <div className="flex justify-end">
                            <button type="submit" disabled={busy} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                                {busy ? 'Working...' : 'Generate merit list'}
                            </button>
                        </div>
                    </form>
                    <div className="p-2">
                        {loading ? <AccountFullPageLoader text="Loading merit list report..." /> : rows.length === 0 ? <div className="p-4"><AccountEmptyState message="Use the filters to generate a merit list." /></div> : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR>
                                        <AccountTH>#</AccountTH>
                                        <AccountTH>Student</AccountTH>
                                        <AccountTH>Position</AccountTH>
                                        {subjects.map((subject) => <AccountTH key={subject} className="text-right">{subject}</AccountTH>)}
                                        <AccountTH className="text-right">Total</AccountTH>
                                        <AccountTH className="text-right">Average</AccountTH>
                                        <AccountTH>Grade</AccountTH>
                                    </AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={`${row.student_id}-${index}`}>
                                            <AccountTD>{index + 1}</AccountTD>
                                            <AccountTD className="font-semibold text-slate-900">{row.name}</AccountTD>
                                            <AccountTD>{row.position || '—'}</AccountTD>
                                            {subjects.map((subject) => <AccountTD key={subject} className="text-right">{row.subjects?.[subject] ?? '—'}</AccountTD>)}
                                            <AccountTD className="text-right font-semibold">{row.total ?? 0}</AccountTD>
                                            <AccountTD className="text-right">{row.average ?? 0}</AccountTD>
                                            <AccountTD>{row.grade || '—'}</AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>
                </AccountCard>
            </div>
        </AdminLayout>
    );
}

export function DuplicateStudentsReportPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');

    const loadIndex = useCallback(async () => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-duplicate-students', { headers: xhrJson });
            setRows(rowsFrom(data?.data));
            setMeta(data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load duplicate students report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadIndex();
    }, [loadIndex]);

    const search = async () => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.post('/report-duplicate-students/search', {}, { headers: xhrJson });
            setRows(rowsFrom(data?.data));
            setMeta(data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search duplicate students.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <ReportHero
                    title="Duplicate Students Report"
                    kicker="Student report"
                    statLabel="Duplicates"
                    statValue={meta.total ?? rows.length}
                    actions={<><ExportButton href={meta.pdf_download_url} primary>Print PDF</ExportButton><ExportButton href={meta.excel_download_url}>Export Excel</ExportButton></>}
                />
                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}
                <AccountCard>
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                        <p className="text-sm text-slate-600">Find students with matching names or phone numbers in the same class.</p>
                        <button type="button" disabled={busy} onClick={search} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                            {busy ? 'Searching...' : 'Search duplicate students'}
                        </button>
                    </div>
                    <div className="p-2">
                        {loading ? <AccountFullPageLoader text="Loading duplicate students report..." /> : rows.length === 0 ? <div className="p-4"><AccountEmptyState message="No duplicate students found." /></div> : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR><AccountTH>#</AccountTH><AccountTH>Type</AccountTH><AccountTH>Class</AccountTH><AccountTH>Student 1</AccountTH><AccountTH>Student 2</AccountTH><AccountTH>Actions</AccountTH></AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={`${row.student_1?.id}-${row.student_2?.id}-${index}`}>
                                            <AccountTD>{index + 1}</AccountTD>
                                            <AccountTD>
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${row.type === 'name' ? 'bg-amber-50 text-amber-700 ring-amber-100' : 'bg-red-50 text-red-700 ring-red-100'}`}>
                                                    {row.type === 'name' ? 'Same Name' : 'Same Phone'}
                                                </span>
                                            </AccountTD>
                                            <AccountTD>{row.class || '—'} ({row.section || '—'})</AccountTD>
                                            <AccountTD><div className="font-semibold">{row.student_1?.name || '—'}</div><div className="text-xs text-slate-500">ID: {row.student_1?.id || '—'} | Phone: {row.student_1?.mobile || '—'}</div></AccountTD>
                                            <AccountTD><div className="font-semibold">{row.student_2?.name || '—'}</div><div className="text-xs text-slate-500">ID: {row.student_2?.id || '—'} | Phone: {row.student_2?.mobile || '—'}</div></AccountTD>
                                            <AccountTD>
                                                <div className="flex flex-wrap gap-2">
                                                    {row.student_1?.id ? <Link to={`/students/${row.student_1.id}`} className="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">View 1</Link> : null}
                                                    {row.student_2?.id ? <Link to={`/students/${row.student_2.id}`} className="rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">View 2</Link> : null}
                                                </div>
                                            </AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>
                </AccountCard>
            </div>
        </AdminLayout>
    );
}

export function BoardingStudentsReportPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState({ year: String(new Date().getFullYear()) });
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState(false);
    const [err, setErr] = useState('');

    const years = meta.years || [];
    const totals = meta.totals || {};

    const applyResponse = (payload) => {
        setRows(rowsFrom(payload?.data));
        setMeta(payload?.meta || {});
        if (payload?.meta?.selected_year) setFilters({ year: String(payload.meta.selected_year) });
    };

    const loadIndex = useCallback(async () => {
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.get('/report-boarding-students', { headers: xhrJson });
            applyResponse(data);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load boarding students report.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        loadIndex();
    }, [loadIndex]);

    const search = async (e) => {
        if (e?.preventDefault) e.preventDefault();
        setBusy(true);
        setErr('');
        try {
            const { data } = await axios.post('/report-boarding-students/search', filters, { headers: xhrJson });
            applyResponse(data);
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to search boarding students.');
        } finally {
            setBusy(false);
            setLoading(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                <ReportHero
                    title="Boarding Students Report"
                    kicker="Fees report"
                    statLabel="Records"
                    statValue={totals.students_count ?? rows.length}
                    actions={<><ExportButton href={meta.pdf_download_url} primary>Print PDF</ExportButton><ExportButton href={meta.excel_download_url}>Export Excel</ExportButton></>}
                />
                {err ? <p className="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{err}</p> : null}
                <div className="grid gap-4 md:grid-cols-4">
                    <StatCard label="School fees" value={money(totals.school_fees_amount)} />
                    <StatCard label="Paid" value={money(totals.school_fees_paid)} tone="border-emerald-100 bg-emerald-50 text-emerald-900" />
                    <StatCard label="Remained" value={money(totals.school_fees_remained)} tone="border-red-100 bg-red-50 text-red-900" />
                    <StatCard label="Outstanding" value={money(totals.school_fees_outstanding)} tone="border-violet-100 bg-violet-50 text-violet-900" />
                </div>
                <AccountCard>
                    <form onSubmit={search} className="flex flex-wrap items-end justify-between gap-4 border-b border-slate-100 p-5">
                        <Field label="Year" className="min-w-[220px]">
                            <select className={selectClassName()} value={filters.year} onChange={(e) => setFilters({ year: e.target.value })}>
                                {years.length === 0 ? <option value={filters.year}>{filters.year}</option> : null}
                                {years.map((item) => <option key={item.year} value={item.year}>{item.year}</option>)}
                            </select>
                        </Field>
                        <div className="flex flex-wrap gap-2">
                            <Link to="/reports/boarding-students/missing-2026" className="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Missing 2026 fees</Link>
                            <button type="submit" disabled={busy} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60">
                                {busy ? 'Working...' : 'Apply filters'}
                            </button>
                        </div>
                    </form>
                    <div className="p-2">
                        {loading ? <AccountFullPageLoader text="Loading boarding students report..." /> : rows.length === 0 ? <div className="p-4"><AccountEmptyState message="No boarding students found for this year." /></div> : (
                            <AccountTable>
                                <AccountTHead>
                                    <AccountTR><AccountTH>#</AccountTH><AccountTH>Student</AccountTH><AccountTH>Year</AccountTH><AccountTH>Class</AccountTH><AccountTH>Section</AccountTH><AccountTH className="text-right">School Fees</AccountTH><AccountTH className="text-right">Paid</AccountTH><AccountTH className="text-right">Remained</AccountTH><AccountTH className="text-right">Outstanding</AccountTH></AccountTR>
                                </AccountTHead>
                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {rows.map((row, index) => (
                                        <AccountTR key={`${row.student_id}-${row.year}-${index}`}>
                                            <AccountTD>{index + 1}</AccountTD>
                                            <AccountTD><div className="font-semibold text-slate-900">{row.first_name} {row.last_name}</div><div className="text-xs text-slate-500">{row.admission_no || 'No admission no'}</div></AccountTD>
                                            <AccountTD>{row.year || '—'}</AccountTD>
                                            <AccountTD>{row.class_name || '—'}</AccountTD>
                                            <AccountTD>{row.section_name || '—'}</AccountTD>
                                            <AccountTD className="text-right font-semibold">{money(row.school_fees_amount)}</AccountTD>
                                            <AccountTD className="text-right">{money(row.school_fees_paid)}</AccountTD>
                                            <AccountTD className="text-right">{money(row.school_fees_remained)}</AccountTD>
                                            <AccountTD className="text-right">{money(row.school_fees_outstanding)}</AccountTD>
                                        </AccountTR>
                                    ))}
                                </tbody>
                            </AccountTable>
                        )}
                    </div>
                </AccountCard>
            </div>
        </AdminLayout>
    );
}
