import React from 'react';

/**
 * Tailwind placeholder (from Blade): resources/views/welcome.blade.php
 */
export default function Welcome(props) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <h1 className="text-lg font-semibold text-slate-900">welcome</h1>
      <p className="mt-2 text-sm text-slate-500">React + Tailwind scaffold. Wire props and API as needed.</p>
    </div>
  );
}
