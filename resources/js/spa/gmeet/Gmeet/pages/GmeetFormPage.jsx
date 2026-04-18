import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';

import { xhrJson } from '../../../api/xhrJson';

export function GmeetFormPage({ Layout, edit = false }) {
  const { id } = useParams();
  const nav = useNavigate();
  const [meta, setMeta] = useState({});
  const [form, setForm] = useState({ title: '', gmeet_link: '', class: '', section: '', subject: '', start: '', end: '', status: 1, description: '' });
  const [err, setErr] = useState('');

  useEffect(() => {
    const url = edit ? `/liveclass/gmeet/edit/${id}` : '/liveclass/gmeet/create';
    axios.get(url, { headers: xhrJson }).then((r) => {
      setMeta(r.data?.meta || {});
      if (edit) {
        const g = r.data?.data?.gmeet || {};
        setForm({ title: g.title || '', gmeet_link: g.gmeet_link || '', class: g.classes_id || '', section: g.section_id || '', subject: g.subject_id || '', start: g.start || '', end: g.end || '', status: Number(g.status ?? 1), description: g.description || '' });
      }
    }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
  }, [edit, id]);

  const submit = async (e) => {
    e.preventDefault();
    try {
      if (edit) await axios.put(`/liveclass/gmeet/update/${id}`, form, { headers: xhrJson });
      else await axios.post('/liveclass/gmeet/store', form, { headers: xhrJson });
      nav('/liveclass/gmeet');
    } catch (ex) { setErr(ex.response?.data?.message || 'Save failed.'); }
  };

  return <Layout><div className="mx-auto max-w-3xl space-y-4 p-6">
    <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit gmeet' : 'Create gmeet')}</h1>
    {err ? <p className="text-sm text-red-600">{err}</p> : null}
    <form onSubmit={submit} className="grid gap-3 rounded-xl border bg-white p-6">
      <input className="rounded border px-3 py-2" placeholder="Title" value={form.title} onChange={(e)=>setForm({ ...form, title: e.target.value })} required />
      <input className="rounded border px-3 py-2" placeholder="Gmeet link" value={form.gmeet_link} onChange={(e)=>setForm({ ...form, gmeet_link: e.target.value })} required />
      <select className="rounded border px-3 py-2" value={form.class} onChange={(e)=>setForm({ ...form, class: e.target.value })} required>
        <option value="">Select class</option>
        {(meta.classes || []).map((c) => { const v = c.class?.id || c.id; const n = c.class?.name || c.name; return <option key={v} value={v}>{n}</option>; })}
      </select>
      <input className="rounded border px-3 py-2" placeholder="Section id" value={form.section} onChange={(e)=>setForm({ ...form, section: e.target.value })} required />
      <input className="rounded border px-3 py-2" placeholder="Subject id (optional)" value={form.subject} onChange={(e)=>setForm({ ...form, subject: e.target.value })} />
      <div className="grid gap-3 md:grid-cols-2"><input type="datetime-local" className="rounded border px-3 py-2" value={form.start} onChange={(e)=>setForm({ ...form, start: e.target.value })} required /><input type="datetime-local" className="rounded border px-3 py-2" value={form.end} onChange={(e)=>setForm({ ...form, end: e.target.value })} required /></div>
      <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={!!Number(form.status)} onChange={(e)=>setForm({ ...form, status: e.target.checked ? 1 : 0 })} /> Active</label>
      <textarea className="rounded border px-3 py-2" placeholder="Description" value={form.description} onChange={(e)=>setForm({ ...form, description: e.target.value })} />
      <div className="flex gap-2"><button className="rounded bg-blue-600 px-4 py-2 text-white">Save</button><Link to="/liveclass/gmeet" className="rounded border px-4 py-2">Cancel</Link></div>
    </form>
  </div></Layout>;
}

