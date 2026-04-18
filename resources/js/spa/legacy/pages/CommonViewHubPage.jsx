import React from 'react';
import { LinkGrid, Shell } from '../LegacyViewMigrationsShared';

export function CommonViewHubPage({ Layout }) {
    return (
        <Shell Layout={Layout} title="Common / Components / Emails" subtitle="SPA-safe equivalents for shared Blade fragments.">
            <LinkGrid
                links={[
                    { to: '/common/pagination', label: 'Common Pagination UI' },
                    { to: '/components/sidebar-header', label: 'Sidebar Header Component' },
                    { to: '/components/certificate-generate', label: 'Certificate Generate Component' },
                    { to: '/emails/daily-report', label: 'Daily Report Email Preview' },
                ]}
            />
        </Shell>
    );
}
