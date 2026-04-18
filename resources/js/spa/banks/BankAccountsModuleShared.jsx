import React from 'react';

export function Shell({ Layout, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-3xl space-y-6 p-6">{children}</div>
        </Layout>
    );
}
