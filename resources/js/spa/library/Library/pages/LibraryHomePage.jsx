import React from 'react';
import { Link } from 'react-router-dom';

export function LibraryHomePage({ Layout }) {
  return <Layout><div className="mx-auto max-w-5xl space-y-4 p-6"><h1 className="text-2xl font-bold text-slate-900">Library</h1><p className="text-sm text-slate-600">Hand-built SPA module scaffold (no backend/library blades found).</p><div className="grid gap-3 md:grid-cols-3"><Link to="/library" className="rounded-xl border bg-white p-4">Index</Link><Link to="/library/create" className="rounded-xl border bg-white p-4">Create</Link><Link to="/library/1/edit" className="rounded-xl border bg-white p-4">Edit</Link></div></div></Layout>;
}

