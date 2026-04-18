import React from 'react';

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

export function pickArray(data, preferredKey) {
    if (!data) return [];
    if (preferredKey && Array.isArray(data[preferredKey])) return data[preferredKey];
    const paginator = Object.values(data).find((v) => v && Array.isArray(v?.data));
    if (paginator?.data) return paginator.data;
    const firstArray = Object.values(data).find((v) => Array.isArray(v));
    return firstArray || [];
}
