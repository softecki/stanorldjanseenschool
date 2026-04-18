import React from 'react';
import { AdminLayout } from '../layout/AdminLayout';
import { SpaApiExplorerPage } from '../components/SpaApiExplorerPage';

export function ReportEntryPage({ title, endpoint, endpointTemplate }) {
    return (
        <SpaApiExplorerPage
            Layout={AdminLayout}
            title={title}
            endpoint={endpoint}
            endpointTemplate={endpointTemplate}
            backLink={{ to: '/reports', label: 'All reports' }}
        />
    );
}
