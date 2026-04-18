import React from 'react';
import { AccountListPage } from '../AccountExtraShared';

export function SuppliersPage({ Layout }) {
    return (
        <AccountListPage Layout={Layout} title="Suppliers" endpoint="/suppliers" createTo="/accounts/suppliers/create" editBase="/accounts/suppliers" />
    );
}
