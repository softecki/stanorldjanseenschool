import React from 'react';
import { AccountListPage } from '../AccountExtraShared';

export function TransactionsPage({ Layout }) {
    return (
        <AccountListPage
            Layout={Layout}
            title="Transactions"
            endpoint="/transactions"
            createTo="/accounts/transactions/create"
            editBase="/accounts/transactions"
        />
    );
}
