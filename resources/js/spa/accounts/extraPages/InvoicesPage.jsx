import React from 'react';
import { AccountListPage } from '../AccountExtraShared';

export function InvoicesPage({ Layout }) {
    return (
        <AccountListPage Layout={Layout} title="Invoices" endpoint="/invoices" createTo="/accounts/invoices/create" editBase="/accounts/invoices" />
    );
}
