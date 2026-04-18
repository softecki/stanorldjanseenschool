import React from 'react';
import { Link } from 'react-router-dom';

export function Shell({ Layout, title, subtitle, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-6xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">{title}</h1>
                    {subtitle ? <p className="text-sm text-slate-600">{subtitle}</p> : null}
                </div>
                {children}
            </div>
        </Layout>
    );
}

export function LinkGrid({ links }) {
    return (
        <div className="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            {links.map((item) => (
                <Link key={item.to} to={item.to} className="rounded border bg-white p-4 text-sm hover:bg-slate-50">
                    <p className="font-medium text-slate-900">{item.label}</p>
                    <p className="mt-1 text-xs text-slate-500">{item.to}</p>
                </Link>
            ))}
        </div>
    );
}
