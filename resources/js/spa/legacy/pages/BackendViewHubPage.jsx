import React from 'react';
import { LinkGrid, Shell } from '../LegacyViewMigrationsShared';

export function BackendViewHubPage({ Layout }) {
    return (
        <Shell Layout={Layout} title="Backend View Migration" subtitle="Hand-built SPA routes replacing top-level backend blades.">
            <LinkGrid
                links={[
                    { to: '/backend/dashboard', label: 'Dashboard' },
                    { to: '/backend/dashboard-pdf', label: 'Dashboard PDF' },
                    { to: '/backend/dashboardtable', label: 'Dashboard Table' },
                    { to: '/backend/master', label: 'Backend Master Layout' },
                    { to: '/backend/menu-autocomplete', label: 'Menu Autocomplete' },
                ]}
            />
        </Shell>
    );
}
