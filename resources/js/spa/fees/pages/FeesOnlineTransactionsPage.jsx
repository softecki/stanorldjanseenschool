import React from 'react';
import { TransactionsListPage } from '../FeesModuleShared';

export function FeesOnlineTransactionsPage({ Layout }) {
    return (
        <TransactionsListPage
            Layout={Layout}
            title="Online Transactions"
            endpoint="/fees-collect/collect-transactions"
            variant="online"
            canCancelPush
            suppressFeesHeader
        />
    );
}

