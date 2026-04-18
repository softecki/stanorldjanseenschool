import React from 'react';

export function Shell({ Layout, title, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-6xl space-y-6 p-6">
                <h1 className="text-2xl font-bold text-slate-900">{title}</h1>
                {children}
            </div>
        </Layout>
    );
}
