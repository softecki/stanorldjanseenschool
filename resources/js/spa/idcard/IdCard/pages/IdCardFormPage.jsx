import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';

import { xhrJson } from '../../../api/xhrJson';

export function IdCardFormPage({ Layout, edit = false }) {
  const { id } = useParams();
  const nav = useNavigate();
  const [meta, setMeta] = useState({});
  const [form, setForm] = useState({ title: '', expired_date: '', backside_description: '', admission_no: false, roll_no: false, student_name: true, class_name: true, section_name: true, blood_group: false, dob: false });
  const [files, setFiles] = useState({ frontside_bg_image: null, backside_bg_image: null, signature: null, qr_code: null });
  const [err, setErr] = useState('');

  useEffect(() => {
    const url = edit ? `/idcard/edit/${id}` : '/idcard/create';
    axios.get(url, { headers: xhrJson }).then((r) => {
      setMeta(r.data?.meta || {});
      if (edit) {
        const v = r.data?.data?.id_card || {};
        setForm({ title: v.title || '', expired_date: v.expired_date || '', backside_description: v.backside_description || '', admission_no: !!v.admission_no, roll_no: !!v.roll_no, student_name: !!v.student_name, class_name: !!v.class_name, section_name: !!v.section_name, blood_group: !!v.blood_group, dob: !!v.dob });
      }
    }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
  }, [edit, id]);

  const submit = async (e) => {
    e.preventDefault();
    try {
      const fd = new FormData();
      fd.append('title', form.title);
      fd.append('expired_date', form.expired_date || '');
      fd.append('backside_description', form.backside_description || '');
      ['admission_no','roll_no','student_name','class_name','section_name','blood_group','dob'].forEach((k) => { if (form[k]) fd.append(k, 'on'); });
      Object.keys(files).forEach((k) => { if (files[k]) fd.append(k, files[k]); });
      if (edit) { fd.append('_method', 'PUT'); await axios.post(`/idcard/update/${id}`, fd, { headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' } }); }
      else await axios.post('/idcard/store', fd, { headers: { ...xhrJson, 'Content-Type': 'multipart/form-data' } });
      nav('/idcard');
    } catch (ex) { setErr(ex.response?.data?.message || 'Save failed.'); }
  };

  return <Layout><div className="mx-auto max-w-4xl space-y-4 p-6">
    <h1 className="text-2xl font-bold text-slate-900">{meta.title || (edit ? 'Edit ID card' : 'Create ID card')}</h1>
    {err ? <p className="text-sm text-red-600">{err}</p> : null}
    <form onSubmit={submit} className="grid gap-3 rounded-xl border bg-white p-6">
      <input className="rounded border px-3 py-2" placeholder="Title" value={form.title} onChange={(e)=>setForm({ ...form, title: e.target.value })} required />
      <input type="date" className="rounded border px-3 py-2" value={form.expired_date} onChange={(e)=>setForm({ ...form, expired_date: e.target.value })} />
      <textarea className="rounded border px-3 py-2" placeholder="Backside description" value={form.backside_description} onChange={(e)=>setForm({ ...form, backside_description: e.target.value })} />
      <div className="grid gap-3 md:grid-cols-2">
        <label className="text-sm">Front background<input type="file" className="mt-1 block w-full" onChange={(e)=>setFiles({ ...files, frontside_bg_image: e.target.files?.[0] || null })} /></label>
        <label className="text-sm">Back background<input type="file" className="mt-1 block w-full" onChange={(e)=>setFiles({ ...files, backside_bg_image: e.target.files?.[0] || null })} /></label>
        <label className="text-sm">Signature<input type="file" className="mt-1 block w-full" onChange={(e)=>setFiles({ ...files, signature: e.target.files?.[0] || null })} /></label>
        <label className="text-sm">QR code<input type="file" className="mt-1 block w-full" onChange={(e)=>setFiles({ ...files, qr_code: e.target.files?.[0] || null })} /></label>
      </div>
      <div className="grid gap-2 md:grid-cols-3">
        {['admission_no','roll_no','student_name','class_name','section_name','blood_group','dob'].map((k)=><label key={k} className="flex items-center gap-2 text-sm"><input type="checkbox" checked={!!form[k]} onChange={(e)=>setForm({ ...form, [k]: e.target.checked })} /> {k.replace(/_/g,' ')}</label>)}
      </div>
      <div className="flex gap-2"><button className="rounded bg-blue-600 px-4 py-2 text-white">Save</button><Link to="/idcard" className="rounded border px-4 py-2">Cancel</Link></div>
    </form>
  </div></Layout>;
}

