import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

function isoToDateInput(v) {
    if (v == null || v === '') return '';
    if (typeof v !== 'string') return '';
    return v.length >= 10 ? v.slice(0, 10) : String(v);
}

function FieldIcon({ children }) {
    return <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">{children}</span>;
}

function FloatingInput({ icon, label, className = '', value, ...props }) {
    const floated = value !== undefined && value !== null && String(value) !== '';
    return (
        <div className="relative group">
            <FieldIcon>{icon}</FieldIcon>
            <input
                {...props}
                value={value}
                placeholder=" "
                className={`w-full rounded-xl border border-gray-200 bg-white px-10 pb-2 pt-6 text-sm shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 ${className}`}
            />
            <span className={`pointer-events-none absolute left-10 text-gray-500 transition-all duration-150 ${floated ? 'top-2 text-[11px]' : 'top-1/2 -translate-y-1/2 text-sm group-focus-within:top-2 group-focus-within:translate-y-0 group-focus-within:text-[11px]'}`}>
                {label}
            </span>
        </div>
    );
}

function FloatingSelect({ icon, label, className = '', value, children, ...props }) {
    const floated = value !== undefined && value !== null && String(value) !== '';
    return (
        <div className="relative group">
            <FieldIcon>{icon}</FieldIcon>
            <select
                {...props}
                value={value}
                className={`w-full rounded-xl border border-gray-200 bg-white px-10 pb-2 pt-6 text-sm shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 ${className}`}
            >
                {children}
            </select>
            <span className={`pointer-events-none absolute left-10 text-gray-500 transition-all duration-150 ${floated ? 'top-2 text-[11px]' : 'top-1/2 -translate-y-1/2 text-sm group-focus-within:top-2 group-focus-within:translate-y-0 group-focus-within:text-[11px]'}`}>
                {label}
            </span>
        </div>
    );
}

export function StudentEditPage() {
    const { id } = useParams();
    const nav = useNavigate();
    const [form, setForm] = useState({});
    const [meta, setMeta] = useState({});
    const [sections, setSections] = useState([]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        setLoading(true);
        axios
            .get(`/student/edit/${id}`, { headers: xhrJson })
            .then((r) => {
                const payload = r.data?.data || {};
                const m = r.data?.meta || {};
                const scs = m?.session_class_student || {};
                setMeta(m);
                setForm({
                    ...payload,
                    class: String(scs?.classes_id ?? ''),
                    section: String(scs?.section_id ?? ''),
                    shift_id: scs?.shift_id != null ? String(scs.shift_id) : '',
                    status: String(
                        payload?.status === 2 || payload?.status === '2' || payload?.status === 0 || payload?.status === '0'
                            ? '0'
                            : (payload?.status ?? '1'),
                    ),
                    date_of_birth: isoToDateInput(payload?.dob),
                    admission_date: isoToDateInput(payload?.admission_date),
                    religion: payload?.religion_id != null && payload.religion_id !== '' ? String(payload.religion_id) : '',
                    gender: payload?.gender_id != null && payload.gender_id !== '' ? String(payload.gender_id) : '',
                    blood_group: payload?.blood_group_id != null && payload.blood_group_id !== '' ? String(payload.blood_group_id) : '',
                    category: payload?.student_category_id != null && payload.student_category_id !== '' ? String(payload.student_category_id) : '',
                    roll_no: payload?.roll_no != null ? String(payload.roll_no) : '',
                    previous_school: String(payload?.previous_school ?? '0'),
                    previous_school_info: payload?.previous_school_info ?? '',
                    residance_address: payload?.residance_address ?? '',
                    place_of_birth: payload?.place_of_birth ?? '',
                    nationality: payload?.nationality ?? '',
                    cpr_no: payload?.cpr_no ?? '',
                    spoken_lang_at_home: payload?.spoken_lang_at_home ?? '',
                    sms_send: String(payload?.sms_send ?? '0'),
                    sms_send_description: payload?.sms_send_description ?? '',
                    control_number: payload?.control_number ?? '',
                });
                const secList = Array.isArray(m?.sections) ? m.sections : [];
                setSections(secList);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'))
            .finally(() => setLoading(false));
    }, [id]);

    useEffect(() => {
        if (!form.class) {
            setSections([]);
            return;
        }
        axios
            .get('/class-setup/get-sections', { headers: xhrJson, params: { id: form.class } })
            .then((r) => {
                const list = Array.isArray(r.data) ? r.data : Array.isArray(r.data?.data) ? r.data.data : [];
                setSections(list);
            })
            .catch(() => setSections([]));
    }, [form.class]);

    const submit = async (e) => {
        e.preventDefault();
        setErr('');
        setSaving(true);
        try {
            await axios.put(
                '/student/update',
                {
                    id,
                    first_name: form.first_name,
                    last_name: form.last_name,
                    admission_no: form.admission_no,
                    roll_no: form.roll_no,
                    mobile: form.mobile,
                    email: form.email,
                    date_of_birth: form.date_of_birth,
                    admission_date: form.admission_date,
                    religion: form.religion,
                    gender: form.gender,
                    blood_group: form.blood_group,
                    category: form.category,
                    class: form.class,
                    section: form.section,
                    shift_id: form.shift_id,
                    status: form.status,
                    previous_school: form.previous_school,
                    previous_school_info: form.previous_school_info,
                    residance_address: form.residance_address,
                    place_of_birth: form.place_of_birth,
                    nationality: form.nationality,
                    cpr_no: form.cpr_no,
                    spoken_lang_at_home: form.spoken_lang_at_home,
                    sms_send: form.sms_send,
                    sms_send_description: form.sms_send_description,
                    control_number: form.control_number,
                },
                { headers: xhrJson },
            );
            nav('/students');
        } catch (ex) {
            const d = ex.response?.data;
            const msg =
                (typeof d?.message === 'string' && d.message) ||
                (d?.errors && typeof d.errors === 'object' && Object.values(d.errors).flat()[0]) ||
                'Update failed.';
            setErr(msg);
        } finally {
            setSaving(false);
        }
    };

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white p-5 shadow-sm">
                    <div className="flex items-center justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Edit Student'}</h1>
                        <Link
                            to="/students"
                            className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Back
                        </Link>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <FullPageLoader text="Loading student data…" /> : null}
                {!loading ? (
                    <form onSubmit={submit} className="space-y-6">
                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Core profile</h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput icon="🆔" label="Admission No" value={form.admission_no || ''} onChange={(e) => setForm({ ...form, admission_no: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput icon="🔢" label="Roll No" value={form.roll_no || ''} onChange={(e) => setForm({ ...form, roll_no: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput icon="🧾" label="Control number" value={form.control_number || ''} disabled onChange={(e) => setForm({ ...form, control_number: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput
                                        icon="👤"
                                        label="First name *"
                                        value={form.first_name || ''}
                                        onChange={(e) => setForm({ ...form, first_name: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput
                                        icon="👤"
                                        label="Last name *"
                                        value={form.last_name || ''}
                                        onChange={(e) => setForm({ ...form, last_name: e.target.value })}
                                        required
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput
                                        icon="📅"
                                        label="Date of birth"
                                        type="date"
                                        value={form.date_of_birth || ''}
                                        onChange={(e) => setForm({ ...form, date_of_birth: e.target.value })}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput
                                        icon="📌"
                                        label="Admission date"
                                        type="date"
                                        value={form.admission_date || ''}
                                        onChange={(e) => setForm({ ...form, admission_date: e.target.value })}
                                    />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="⚧" label="Gender" value={form.gender || ''} onChange={(e) => setForm({ ...form, gender: e.target.value })}>
                                        <option value="">Select gender</option>
                                        {(meta.genders || []).map((g) => (
                                            <option key={g.id} value={g.id}>
                                                {g.name}
                                            </option>
                                        ))}
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="🛐" label="Religion" value={form.religion || ''} onChange={(e) => setForm({ ...form, religion: e.target.value })}>
                                        <option value="">Select religion</option>
                                        {(meta.religions || []).map((g) => (
                                            <option key={g.id} value={g.id}>
                                                {g.name}
                                            </option>
                                        ))}
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="🩸" label="Blood group" value={form.blood_group || ''} onChange={(e) => setForm({ ...form, blood_group: e.target.value })}>
                                        <option value="">Select blood group</option>
                                        {(meta.bloods || []).map((g) => (
                                            <option key={g.id} value={g.id}>
                                                {g.name}
                                            </option>
                                        ))}
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="🏷️" label="Student category" value={form.category || ''} onChange={(e) => setForm({ ...form, category: e.target.value })}>
                                        <option value="">Select category</option>
                                        {(meta.categories || []).map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.title || c.name}
                                            </option>
                                        ))}
                                    </FloatingSelect>
                                </label>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Class & guardian</h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect
                                        icon="🏫"
                                        label="Class *"
                                        value={form.class || ''}
                                        onChange={(e) => setForm({ ...form, class: e.target.value, section: '' })}
                                        required
                                    >
                                        <option value="">Select class</option>
                                        {mappedClassOptions(meta.classes || []).map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="📚" label="Section *" value={form.section || ''} onChange={(e) => setForm({ ...form, section: e.target.value })} required>
                                        <option value="">Select section</option>
                                        {sections.map((s) => (
                                            <option key={s?.section?.id || s.id} value={String(s?.section?.id || s.section_id || s.id)}>
                                                {s?.section?.name || s.name}
                                            </option>
                                        ))}
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="🕘" label="Shift" value={form.shift_id || ''} onChange={(e) => setForm({ ...form, shift_id: e.target.value })}>
                                        <option value="">None</option>
                                        {(meta.shifts || []).map((s) => (
                                            <option key={s.id} value={s.id}>
                                                {s.name}
                                            </option>
                                        ))}
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    <FloatingInput icon="📱" label="Parent mobile *" value={form.mobile || ''} onChange={(e) => setForm({ ...form, mobile: e.target.value })} required />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="✅" label="Status *" value={String(form.status || '1')} onChange={(e) => setForm({ ...form, status: e.target.value })} required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </FloatingSelect>
                                </label>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Contact</h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput icon="📱" label="Mobile" value={form.mobile || ''} onChange={(e) => setForm({ ...form, mobile: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    <FloatingInput icon="✉️" label="Email" type="email" value={form.email || ''} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                                </label>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Address & background</h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-3">
                                    <FloatingInput icon="📍" label="Residence address" value={form.residance_address || ''} onChange={(e) => setForm({ ...form, residance_address: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput icon="🌍" label="Place of birth" value={form.place_of_birth || ''} onChange={(e) => setForm({ ...form, place_of_birth: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput icon="🗺️" label="Nationality" value={form.nationality || ''} onChange={(e) => setForm({ ...form, nationality: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingInput icon="🪪" label="CPR No" value={form.cpr_no || ''} onChange={(e) => setForm({ ...form, cpr_no: e.target.value })} />
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    <FloatingInput icon="💬" label="Languages spoken at home" value={form.spoken_lang_at_home || ''} onChange={(e) => setForm({ ...form, spoken_lang_at_home: e.target.value })} />
                                </label>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">Previous school</h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect
                                        icon="🏫"
                                        label="Had previous school"
                                        value={form.previous_school || '0'}
                                        onChange={(e) => setForm({ ...form, previous_school: e.target.value })}
                                    >
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    <FloatingInput icon="📝" label="Previous school details" value={form.previous_school_info || ''} onChange={(e) => setForm({ ...form, previous_school_info: e.target.value })} />
                                </label>
                            </div>
                        </div>

                        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <h2 className="mb-4 text-sm font-semibold text-gray-800">SMS</h2>
                            <div className="grid gap-4 md:grid-cols-3">
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600">
                                    <FloatingSelect icon="📨" label="Send SMS" value={form.sms_send || '0'} onChange={(e) => setForm({ ...form, sms_send: e.target.value })}>
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </FloatingSelect>
                                </label>
                                <label className="flex flex-col gap-1 text-xs font-medium text-gray-600 md:col-span-2">
                                    <FloatingInput icon="✍️" label="SMS description" value={form.sms_send_description || ''} onChange={(e) => setForm({ ...form, sms_send_description: e.target.value })} />
                                </label>
                            </div>
                        </div>

                        <div className="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Link
                                to="/students"
                                className="rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={saving}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60"
                            >
                                {saving ? 'Saving…' : 'Save changes'}
                            </button>
                        </div>
                    </form>
                ) : null}
            </div>
        </AdminLayout>
    );
}
