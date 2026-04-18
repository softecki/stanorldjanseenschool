import React from 'react';

/**
 * Auth shell (replaces backend/auth/master layout) — Tailwind only, no Blade.
 */
export function AuthLayout({ title, subtitle, children }) {
    return (
        <div className="flex min-h-screen flex-col bg-gradient-to-b from-slate-100 to-slate-200">
            <div className="flex flex-1 items-center justify-center p-4">
                <div className="w-full max-w-md rounded-xl border border-slate-200/80 bg-white p-6 shadow-lg shadow-slate-300/40">
                    <div className="mb-6 text-center">
                        <div className="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white">
                            S
                        </div>
                        <h1 className="text-xl font-semibold tracking-tight text-slate-900">{title}</h1>
                        {subtitle ? <p className="mt-1 text-sm text-slate-500">{subtitle}</p> : null}
                    </div>
                    {children}
                </div>
            </div>
        </div>
    );
}

export function AuthField({ label, children }) {
    return (
        <label className="block">
            <span className="mb-1 block text-sm font-medium text-slate-700">{label}</span>
            {children}
        </label>
    );
}

export function AuthInput(props) {
    return (
        <input
            className="mt-0.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            {...props}
        />
    );
}

export function AuthButton({ children, busy, ...rest }) {
    return (
        <button
            type="submit"
            disabled={busy}
            className="w-full rounded-md bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow hover:bg-blue-700 disabled:opacity-60"
            {...rest}
        >
            {children}
        </button>
    );
}
