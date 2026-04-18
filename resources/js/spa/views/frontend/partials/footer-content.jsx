import React from 'react';

/**
 * Tailwind placeholder (from Blade): resources/views/frontend/partials/footer-content.blade.php
 */
export default function FooterContent(props) {
  return (
    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
      <h1 className="text-lg font-semibold text-slate-900">footer content</h1>
      <p className="mt-2 text-sm text-slate-500">React + Tailwind scaffold. Wire props and API as needed.</p>
    </div>
  );
}
