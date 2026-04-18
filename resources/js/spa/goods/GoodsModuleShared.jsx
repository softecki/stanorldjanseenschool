import React from 'react';

export function Card({ title, text }) {
  return <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><h2 className="text-lg font-semibold text-slate-900">{title}</h2><p className="mt-2 text-sm text-slate-500">{text}</p></div>;
}
