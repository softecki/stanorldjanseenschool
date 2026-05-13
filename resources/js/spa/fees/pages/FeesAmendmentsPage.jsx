import React from 'react';
import { TransactionsListPage } from '../FeesModuleShared';

export function FeesAmendmentsPage({ Layout }) {
    return (
        <TransactionsListPage
            Layout={Layout}
            title="Amendments"
            endpoint="/fees-collect/collect-amendment"
            variant="amendments"
            suppressFeesHeader
        />
    );
}


