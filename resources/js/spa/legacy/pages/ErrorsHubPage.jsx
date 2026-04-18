import React from 'react';
import { LinkGrid, Shell } from '../LegacyViewMigrationsShared';

export function ErrorsHubPage({ Layout }) {
    return (
        <Shell Layout={Layout} title="Error Pages" subtitle="Hand-built error screens for HTTP status pages.">
            <LinkGrid
                links={[
                    { to: '/errors/400', label: '400 - Bad Request' },
                    { to: '/errors/403', label: '403 - Forbidden' },
                    { to: '/errors/404', label: '404 - Not Found' },
                    { to: '/errors/405', label: '405 - Method Not Allowed' },
                    { to: '/errors/500', label: '500 - Server Error' },
                ]}
            />
        </Shell>
    );
}
