import React from 'react';
import { TransactionsListPage } from '../FeesModuleShared';

export function FeesTransactionsPage({ Layout }) {
    return (
        <TransactionsListPage
            Layout={Layout}
            title="Fees Transactions"
            endpoint="/fees-collect/collect-list"
            variant="cash"
            suppressFeesHeader
        />
    );
}

