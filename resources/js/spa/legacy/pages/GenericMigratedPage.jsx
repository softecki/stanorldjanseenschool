import React from 'react';
import { Shell } from '../LegacyViewMigrationsShared';

export function GenericMigratedPage({ Layout, title, description }) {
    return (
        <Shell Layout={Layout} title={title} subtitle={description}>
            <div className="rounded border bg-white p-5 text-sm text-slate-700">
                This page is implemented as a hand-built React/Tailwind SPA screen and does not reuse Blade view files.
            </div>
        </Shell>
    );
}
