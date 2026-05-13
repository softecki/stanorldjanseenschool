import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { mappedClassOptions } from '../StudentModuleShared';

export function StudentUploadPage() {
    const [form, setForm] = useState({
        document_format: '1',
        document_files: null,
        class_id: '',
        section_id: '',
        fee_group_id: '',
        fee_type_id: '',
    });
    const [meta, setMeta] = useState({ classes: [], categories: [], fee_types: [], fee_groups: [] });
    const [sections, setSections] = useState([]);
    const [pendingTransport, setPendingTransport] = useState([]);
    const [transportCategories, setTransportCategories] = useState({});
    const [err, setErr] = useState('');
    const [msg, setMsg] = useState('');
    const [busy, setBusy] = useState(false);
    const [assigningTransport, setAssigningTransport] = useState(false);

    const classOptions = mappedClassOptions(meta.classes || []);
    // CRDB always resolves class/section from Excel columns: Class + Stream/Combination.
    const requiresClassSection = form.document_format === '2';
    const feeGroupId = Number(form.fee_group_id || 0);
    // Transport group (3) is assigned by per-student category after upload, not by a single fee type here.
    const needsFeeType = feeGroupId !== 0 && feeGroupId !== 2 && feeGroupId !== 1 && feeGroupId !== 3;
    const formatLabel = form.document_format === '1' ? 'Normal Excel' : form.document_format === '2' ? 'Quick Books' : 'CRDB';
    const feeTypesByGroup = (meta.fee_types || []).filter((t) => Number(t.fees_group_id || 0) === feeGroupId);

    useEffect(() => {
        axios.get('/student/upload', { headers: xhrJson })
            .then((r) => {
                const m = r.data?.meta || {};
                setMeta(m);
                const classes = mappedClassOptions(m.classes || []);
                setForm((f) => ({
                    ...f,
                    class_id: f.class_id || String(classes[0]?.id || ''),
                    fee_group_id: f.fee_group_id || String(m.fee_groups?.[0]?.id || ''),
                    fee_type_id: f.fee_type_id || String(m.fee_types?.[0]?.id || ''),
                }));
            })
            .catch(() => setErr('Failed to load upload options.'));
    }, []);

    useEffect(() => {
        if (!form.class_id) {
            setSections([]);
            return;
        }
        axios.get('/class-setup/get-sections', { headers: xhrJson, params: { id: form.class_id } })
            .then((r) => {
                const list = Array.isArray(r.data) ? r.data : (Array.isArray(r.data?.data) ? r.data.data : []);
                setSections(list);
                setForm((f) => ({ ...f, section_id: f.section_id || String(list?.[0]?.section?.id || list?.[0]?.id || '') }));
            })
            .catch(() => setSections([]));
    }, [form.class_id]);

    const submit = async (e) => {
        e.preventDefault();
        setPendingTransport([]);
        setTransportCategories({});
        if (!form.document_files) {
            setErr('Please select a file to upload.');
            return;
        }
        if (requiresClassSection && (!form.class_id || !form.section_id)) {
            setErr('Please select class and section.');
            return;
        }
        if (!form.fee_group_id) {
            setErr('Please select fee group.');
            return;
        }
        if (needsFeeType && !form.fee_type_id) {
            setErr('Please select fee type.');
            return;
        }

        setErr('');
        setMsg('');
        setBusy(true);
        const fd = new FormData();
        fd.append('document_format', form.document_format);
        fd.append('document_files', form.document_files);
        fd.append('fee_group_id', form.fee_group_id);
        if (form.class_id) fd.append('class_id', form.class_id);
        if (form.section_id) fd.append('section_id', form.section_id);
        if (form.fee_type_id) fd.append('fee_type_id', form.fee_type_id);
        try {
            const res = await axios.post('/student/uploadStudent', fd, { headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' } });
            setMsg(res.data?.message || 'Upload completed successfully.');
            const pending = Array.isArray(res.data?.pending_transport) ? res.data.pending_transport : [];
            setPendingTransport(pending);
            if (pending.length > 0) {
                const defaultCategoryId = String(meta.categories?.[0]?.id || '');
                const defaults = {};
                pending.forEach((p) => { defaults[String(p.student_id)] = defaultCategoryId; });
                setTransportCategories(defaults);
            }
            setForm((f) => ({ ...f, document_files: null }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Upload failed.');
        } finally {
            setBusy(false);
        }
    };

    const submitTransportAssignments = async () => {
        const payloadStudents = pendingTransport.map((s) => ({
            student_id: Number(s.student_id),
            category_id: Number(transportCategories[String(s.student_id)] || 0),
        })).filter((s) => s.student_id > 0 && s.category_id > 0);
        if (payloadStudents.length !== pendingTransport.length) {
            setErr('Please select category for every uploaded student.');
            return;
        }
        setErr('');
        setAssigningTransport(true);
        try {
            const res = await axios.post('/student/assign-transport-from-upload', { students: payloadStudents }, { headers: xhrJson });
            setMsg(res.data?.message || 'Transport fees assigned.');
            setPendingTransport([]);
            setTransportCategories({});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to assign transport fees.');
        } finally {
            setAssigningTransport(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl p-6">
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {msg ? <p className="mb-3 text-sm text-emerald-600">{msg}</p> : null}
                <form onSubmit={submit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div className="grid gap-3 md:grid-cols-3">
                        <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.document_format} onChange={(e) => setForm((f) => ({ ...f, document_format: e.target.value }))}>
                            <option value="1">Normal Excel</option>
                            <option value="2">Quick Books</option>
                            <option value="3">CRDB</option>
                        </select>
                        <select
                            className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                            value={form.fee_group_id}
                            onChange={(e) => setForm((f) => ({ ...f, fee_group_id: e.target.value, fee_type_id: '' }))}
                        >
                            <option value="">Select fee group</option>
                            {(meta.fee_groups || []).map((g) => <option key={String(g.id)} value={String(g.id)}>{g.name}</option>)}
                        </select>
                        <input type="file" accept=".xlsx,.xls,.csv" className="rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-2" onChange={(e) => setForm((f) => ({ ...f, document_files: e.target.files?.[0] || null }))} />
                    </div>

                    {requiresClassSection ? (
                        <div className="grid gap-3 md:grid-cols-2">
                            <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.class_id} onChange={(e) => setForm((f) => ({ ...f, class_id: e.target.value, section_id: '' }))}>
                                <option value="">Select class</option>
                                {classOptions.map((c) => <option key={String(c.id)} value={String(c.id)}>{c.name}</option>)}
                            </select>
                            <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.section_id} onChange={(e) => setForm((f) => ({ ...f, section_id: e.target.value }))}>
                                <option value="">Select section</option>
                                {sections.map((s) => (
                                    <option key={String(s?.section?.id || s.id)} value={String(s?.section?.id || s.id)}>
                                        {s?.section?.name || s?.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    ) : null}

                    {needsFeeType ? (
                        <div className="grid gap-3 md:grid-cols-1">
                            <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.fee_type_id} onChange={(e) => setForm((f) => ({ ...f, fee_type_id: e.target.value }))}>
                                <option value="">Select fee type</option>
                                {feeTypesByGroup.map((t) => <option key={String(t.id)} value={String(t.id)}>{t.name}</option>)}
                            </select>
                        </div>
                    ) : null}

                    <div className="flex justify-end">
                        <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60" disabled={busy}>
                            {busy ? 'Uploading...' : 'Submit'}
                        </button>
                    </div>
                </form>
                <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    <p><strong>Normal Excel:</strong> includes school, transport, and outstanding fee amount columns plus up to 5 outstanding description+amount lines.</p>
                    <p><strong>Quick Books / CRDB:</strong> pick fee group first. For school group the system uses class mapping; for transport group you assign category per uploaded student after upload.</p>
                    {form.document_format === '1' ? (
                        <p className="mt-2 rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs text-amber-900">
                            Header hints (Normal Excel): `student_name`, `class`, `section`, `school_fees_amount`, `school_paid_amount`, `school_remained_amount`,
                            `transport_fees_amount`, `transport_paid_amount`, `transport_remained_amount`, `outstanding_fees_amount`, `outstanding_paid_amount`,
                            `outstanding_remained_amount`, then `outstanding_description_1..5` + `outstanding_amount_1..5`.
                        </p>
                    ) : null}
                    <a href={`/student/download-template?format=${form.document_format}`} className="mt-2 inline-flex rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-medium text-amber-700 transition hover:bg-amber-100">
                        Download {formatLabel} Template
                    </a>
                </div>

                {pendingTransport.length > 0 ? (
                    <div className="mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 className="text-sm font-semibold text-gray-800">Assign Transport Category Per Uploaded Student</h3>
                        <div className="mt-3 overflow-x-auto">
                            <table className="min-w-full text-sm">
                                <thead>
                                    <tr className="border-b border-gray-200 text-left text-gray-600">
                                        <th className="px-2 py-2">Student</th>
                                        <th className="px-2 py-2">Class</th>
                                        <th className="px-2 py-2">Section</th>
                                        <th className="px-2 py-2">Student Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {pendingTransport.map((row) => (
                                        <tr key={String(row.student_id)} className="border-b border-gray-100">
                                            <td className="px-2 py-2">{row.student_name}</td>
                                            <td className="px-2 py-2">{row.class_name}</td>
                                            <td className="px-2 py-2">{row.section_name}</td>
                                            <td className="px-2 py-2">
                                                <select
                                                    className="rounded border border-gray-200 px-2 py-1"
                                                    value={transportCategories[String(row.student_id)] || ''}
                                                    onChange={(e) => setTransportCategories((p) => ({ ...p, [String(row.student_id)]: e.target.value }))}
                                                >
                                                    <option value="">Select student category</option>
                                                    {(meta.categories || []).map((c) => <option key={String(c.id)} value={String(c.id)}>{c.title || c.name}</option>)}
                                                </select>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="mt-3 flex justify-end">
                            <button
                                type="button"
                                onClick={submitTransportAssignments}
                                disabled={assigningTransport}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60"
                            >
                                {assigningTransport ? 'Assigning...' : 'Assign Transport Fees'}
                            </button>
                        </div>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}

