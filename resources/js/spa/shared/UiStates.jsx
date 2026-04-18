import React from 'react';

export function EmptyState({ title = 'No data', hint = 'There are no records yet.' }) {
    return (
        <div className="rounded border border-dashed bg-slate-50 p-6 text-center">
            <p className="font-medium text-slate-700">{title}</p>
            <p className="mt-1 text-sm text-slate-500">{hint}</p>
        </div>
    );
}

export function confirmDelete(label = 'record') {
    return window.confirm(`Delete this ${label}?`);
}
