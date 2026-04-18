import React from 'react';
import { LinkGrid, Shell } from '../LegacyViewMigrationsShared';

export function FrontendHubPage({ Layout }) {
    return (
        <Shell Layout={Layout} title="Frontend / Landing / Layouts" subtitle="Public site SPA replacements without Blade mirroring.">
            <LinkGrid
                links={[
                    { to: '/frontend', label: 'Frontend Home' },
                    { to: '/frontend/about', label: 'About' },
                    { to: '/frontend/news', label: 'News' },
                    { to: '/frontend/events', label: 'Events' },
                    { to: '/frontend/notices', label: 'Notices' },
                    { to: '/frontend/contact', label: 'Contact' },
                    { to: '/frontend/result', label: 'Result Lookup' },
                    { to: '/frontend/page', label: 'Dynamic Page' },
                    { to: '/frontend-landing/school', label: 'School Landing' },
                    { to: '/layouts/app', label: 'App Layout Preview' },
                    { to: '/home', label: 'Home' },
                    { to: '/index', label: 'Index' },
                    { to: '/welcome', label: 'Welcome' },
                ]}
            />
        </Shell>
    );
}
