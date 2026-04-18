import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';

import { xhrJson } from '../../../api/xhrJson';

export function LanguageFormPage({ Layout, edit = false }) {
  const { id } = useParams();
  const nav = useNavigate();
  const [meta, setMeta] = useState({});
  const [form, setForm] = useState({ name: '', code: '', flagIcon: '', direction: 'LTR' });
  const [err, setErr] = useState('');

  useEffect(() => {
    const url = edit ? `/languages/edit/${id}` : '/languages/create';
    axios.get(url, { headers: xhrJson }).then((r) => {
      setMeta(r.data?.meta || {});
      if (edit) {
        const l = r.data?.data?.language || {};
        setForm({ name: l.name || '', code: l.code || '', flagIcon: l.icon_class || '', direction: l.direction || 'LTR' });
      }
    }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
  }, [edit, id]);

  const submit = async (e) => {
    e.preventDefault();
    try {
      if (edit) await axios.put(`/languages/update/${id}`, form, { headers: xhrJson });
      else await axios.post('/languages/store', form, { headers: xhrJson });
      nav('/languages');
    } catch (ex) { setErr(ex.response?.data?.message || 'Save failed.'); }
  };

  return <Layout><div className="mx-auto max-w-3xl space-y-4 p-6">
    <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit language' : 'Create language')}</h1>
    {err ? <p className="text-sm text-red-600">{err}</p> : null}
    <form onSubmit={submit} className="grid gap-3 rounded-xl border bg-white p-6">
      <input className="rounded border px-3 py-2" placeholder="Name" value={form.name} onChange={(e)=>setForm({ ...form, name: e.target.value })} required />
      <input className="rounded border px-3 py-2" placeholder="Code" value={form.code} onChange={(e)=>setForm({ ...form, code: e.target.value })} required />
      <select className="rounded border px-3 py-2" value={form.flagIcon} onChange={(e)=>setForm({ ...form, flagIcon: e.target.value })} required>
        <option value="">Select flag icon</option>
        {(meta.flagIcons || []).map((f)=><option key={f.icon_class} value={f.icon_class}>{f.title}</option>)}
      </select>
      <div className="flex gap-4 text-sm"><label className="flex items-center gap-2"><input type="radio" name="direction" checked={String(form.direction).toUpperCase()==='RTL'} onChange={()=>setForm({ ...form, direction: 'RTL' })} /> RTL</label><label className="flex items-center gap-2"><input type="radio" name="direction" checked={String(form.direction).toUpperCase()!=='RTL'} onChange={()=>setForm({ ...form, direction: 'LTR' })} /> LTR</label></div>
      <div className="flex gap-2"><button className="rounded bg-blue-600 px-4 py-2 text-white">Save</button><Link to="/languages" className="rounded border px-4 py-2">Cancel</Link></div>
    </form>
  </div></Layout>;
}

