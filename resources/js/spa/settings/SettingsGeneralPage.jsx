import React from 'react';
import { AdminLayout } from '../layout/AdminLayout';
import { SpaApiExplorerPage } from '../components/SpaApiExplorerPage';

export function SettingsGeneralPage() {
    return (
        <SpaApiExplorerPage
            Layout={AdminLayout}
            title="General settings"
            endpoint="/general-settings"
            subtitle="Same payload as the legacy general settings screen: summary fields, languages, and sessions appear in Context below."
        />
    );
}
