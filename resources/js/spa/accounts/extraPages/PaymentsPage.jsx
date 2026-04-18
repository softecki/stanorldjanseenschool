import React from 'react';
import { AccountListPage } from '../AccountExtraShared';

export function PaymentsPage({ Layout }) {
    return (
        <AccountListPage Layout={Layout} title="Payments" endpoint="/payments" createTo="/accounts/payments/create" editBase="/accounts/payments" />
    );
}
