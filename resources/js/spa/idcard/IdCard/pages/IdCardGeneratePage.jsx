import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';

import { xhrJson } from '../../../api/xhrJson';

export function IdCardGeneratePage({ Layout }) {
  const [meta, setMeta] = useState({});
  const [err, setErr] = useState('');
  const [result, setResult] = useState(null);
  const [form, setForm] = useState({ id_card: '', class: '', section: '', student: '' });

  useEffect(() => {
    axios.get('/idcard/generate', { headers: xhrJson }).then((r) => setMeta(r.data?.meta || {})).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
  }, []);

  const submit = async (e) => {
    e.preventDefault();
    try {
      const r = await axios.post('/idcard/generate', form, { headers: xhrJson });
      setResult(r.data?.data || null);
    } catch (ex) { setErr(ex.response?.data?.message || 'Generate failed.'); }
  };

  return <Layout><div className="mx-auto max-w-6xl space-y-4 p-6">
    <h1 className="text-2xl font-bold text-slate-900">{meta.title || 'Generate ID cards'}</h1>
    {err ? <p className="text-sm text-red-600">{err}</p> : null}
    <form onSubmit={submit} className="grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-4">
      <select className="rounded border px-3 py-2" value={form.id_card} onChange={(e)=>setForm({ ...form, id_card: e.target.value })} required><option value="">ID card template</option>{(meta.id_cards || []).map((x)=><option key={x.id} value={x.id}>{x.title}</option>)}</select>
      <select className="rounded border px-3 py-2" value={form.class} onChange={(e)=>setForm({ ...form, class: e.target.value })} required><option value="">Class</option>{(meta.classes || []).map((c)=>{const v=c.class?.id||c.id; const n=c.class?.name||c.name; return <option key={v} value={v}>{n}</option>;})}</select>
      <input className="rounded border px-3 py-2" placeholder="Section id" value={form.section} onChange={(e)=>setForm({ ...form, section: e.target.value })} required />
      <input className="rounded border px-3 py-2" placeholder="Student id (optional)" value={form.student} onChange={(e)=>setForm({ ...form, student: e.target.value })} />
      <button className="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Search</button>
    </form>
    {result ? <div className="rounded-xl border bg-white p-4"><h2 className="mb-2 text-lg font-semibold">Results</h2><p className="text-sm text-slate-600">Template: {result.idcard?.title}</p><div className="mt-2 divide-y">{(result.students || []).map((s)=><div key={s.id} className="py-2 text-sm">Student #{s.student_id}</div>)}</div></div> : null}
  </div></Layout>;
}

