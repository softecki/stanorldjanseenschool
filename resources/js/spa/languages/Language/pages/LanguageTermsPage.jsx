import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';

import { xhrJson } from '../../../api/xhrJson';

export function LanguageTermsPage({ Layout }) {
  const { id } = useParams();
  const nav = useNavigate();
  const [language, setLanguage] = useState(null);
  const [moduleName, setModuleName] = useState('');
  const [terms, setTerms] = useState({});
  const [err, setErr] = useState('');
  const modules = [
    'common','dashboard','student_info','language','settings','attendance','accounts','examination','parent','teacher','report','website_setup'
  ];

  useEffect(() => {
    axios.get(`/languages/terms/${id}`, { headers: xhrJson }).then((r) => {
      setLanguage(r.data?.data?.language || null);
    }).catch((ex) => setErr(ex.response?.data?.message || 'Failed to load terms page.'));
  }, [id]);

  const loadModuleTerms = async (mod) => {
    if (!language?.code || !mod) return;
    setModuleName(mod);
    try {
      const r = await axios.get('/languages/change-module', { headers: xhrJson, params: { code: language.code, module: mod } });
      setTerms(r.data?.data?.terms || {});
    } catch (ex) { setErr(ex.response?.data?.message || 'Failed to load module terms.'); }
  };

  const submit = async (e) => {
    e.preventDefault();
    try {
      await axios.put(`/languages/update/terms/${language.code}`, { lang_module: moduleName, ...terms }, { headers: xhrJson });
      nav('/languages');
    } catch (ex) { setErr(ex.response?.data?.message || 'Save failed.'); }
  };

  return <Layout><div className="mx-auto max-w-6xl space-y-4 p-6">
    <h1 className="text-2xl font-bold text-slate-900">Language terms</h1>
    {err ? <p className="text-sm text-red-600">{err}</p> : null}
    <form onSubmit={submit} className="space-y-4 rounded-xl border bg-white p-6">
      <select className="w-full max-w-xs rounded border px-3 py-2" value={moduleName} onChange={(e)=>loadModuleTerms(e.target.value)} required>
        <option value="">Select module</option>
        {modules.map((m)=><option key={m} value={m}>{m}</option>)}
      </select>
      <div className="space-y-2">{Object.keys(terms).map((k)=><div key={k} className="grid gap-2 md:grid-cols-2"><input className="rounded border px-3 py-2 text-slate-500" value={k} disabled /><input className="rounded border px-3 py-2" value={terms[k] ?? ''} onChange={(e)=>setTerms({ ...terms, [k]: e.target.value })} /></div>)}</div>
      <div className="flex gap-2"><button className="rounded bg-blue-600 px-4 py-2 text-white" disabled={!moduleName}>Save terms</button><Link to="/languages" className="rounded border px-4 py-2">Cancel</Link></div>
    </form>
  </div></Layout>;
}

