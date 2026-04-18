import React from 'react';

/**
 * Tailwind placeholder (from Blade): resources/views/backend/partials/alert-message.blade.php
 */
export default function AlertMessage(props) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <h1 className="text-lg font-semibold text-slate-900">alert message</h1>
      <p className="mt-2 text-sm text-slate-500">React + Tailwind scaffold. Wire props and API as needed.</p>
    </div>
  );
}
