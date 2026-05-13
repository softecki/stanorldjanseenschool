import React from 'react';
import { AccountListPage } from '../AccountExtraShared';

export function TransactionsPage({ Layout }) {
    return (
        <AccountListPage
            Layout={Layout}
            title="Transactions"
            endpoint="/transactions"
            createTo="/account-transactions/create"
            editBase="/account-transactions"
        />
    );
}
