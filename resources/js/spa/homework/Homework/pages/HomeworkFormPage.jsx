import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';

import { xhrJson } from '../../../api/xhrJson';

export function HomeworkFormPage({ Layout, edit = false }) {
  const { id } = useParams();
  const nav = useNavigate();
  const [meta, setMeta] = useState({});
  const [form, setForm] = useState({ class: '', section: '', subject: '', date: '', submission_date: '', marks: '', status: 1, description: '' });
  const [file, setFile] = useState(null);
  const [err, setErr] = useState('');

  useEffect(() => {
    const url = edit ? `/homework/edit/${id}` : '/homework/create';
    axios.get(url, { headers: xhrJson }).then((r) => {
      setMeta(r.data?.meta || {});
      if (edit) {
        const h = r.data?.data?.homework || {};
        setForm({ class: h.classes_id || '', section: h.section_id || '', subject: h.subject_id || '', date: h.date || '', submission_date: h.submission_date || '', marks: h.marks || '', status: Number(h.status ?? 1), description: h.description || '' });
      }
    }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
  }, [edit, id]);

  const submit = async (e) => {
    e.preventDefault();
    try {
      const fd = new FormData();
      Object.entries(form).forEach(([k, v]) => fd.append(k, v ?? ''));
      if (file) fd.append('document', file);
      if (edit) { fd.append('_method', 'PUT'); await axios.post(`/homework/update/${id}`, fd, { headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' } }); }
      else { await axios.post('/homework/store', fd, { headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' } }); }
      nav('/homework');
    } catch (ex) { setErr(ex.response?.data?.message || 'Save failed.'); }
  };

  return <Layout><div className="mx-auto max-w-3xl space-y-4 p-6">
    <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit homework' : 'Create homework')}</h1>
    {err ? <p className="text-sm text-red-600">{err}</p> : null}
    <form onSubmit={submit} className="grid gap-3 rounded-xl border bg-white p-6">
      <select className="rounded border px-3 py-2" value={form.class} onChange={(e)=>setForm({ ...form, class: e.target.value })} required><option value="">Select class</option>{(meta.classes || []).map((c)=>{const v=c.class?.id || c.id; const n=c.class?.name || c.name; return <option key={v} value={v}>{n}</option>;})}</select>
      <input className="rounded border px-3 py-2" placeholder="Section id" value={form.section} onChange={(e)=>setForm({ ...form, section: e.target.value })} required />
      <input className="rounded border px-3 py-2" placeholder="Subject id" value={form.subject} onChange={(e)=>setForm({ ...form, subject: e.target.value })} required />
      <div className="grid gap-3 md:grid-cols-2"><input type="date" className="rounded border px-3 py-2" value={form.date} onChange={(e)=>setForm({ ...form, date: e.target.value })} required /><input type="date" className="rounded border px-3 py-2" value={form.submission_date} onChange={(e)=>setForm({ ...form, submission_date: e.target.value })} /></div>
      <input className="rounded border px-3 py-2" placeholder="Marks" value={form.marks} onChange={(e)=>setForm({ ...form, marks: e.target.value })} required />
      <input type="file" className="rounded border px-3 py-2" onChange={(e)=>setFile(e.target.files?.[0] || null)} />
      <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={!!Number(form.status)} onChange={(e)=>setForm({ ...form, status: e.target.checked ? 1 : 0 })} /> Active</label>
      <textarea className="rounded border px-3 py-2" placeholder="Description" value={form.description} onChange={(e)=>setForm({ ...form, description: e.target.value })} />
      <div className="flex gap-2"><button className="rounded bg-blue-600 px-4 py-2 text-white">Save</button><Link to="/homework" className="rounded border px-4 py-2">Cancel</Link></div>
    </form>
  </div></Layout>;
}

