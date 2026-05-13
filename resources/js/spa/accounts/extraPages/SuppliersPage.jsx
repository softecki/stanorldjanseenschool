import React from 'react';
import { AccountListPage } from '../AccountExtraShared';

export function SuppliersPage({ Layout }) {
    return (
        <AccountListPage Layout={Layout} title="Suppliers" endpoint="/suppliers" createTo="/suppliers/create" editBase="/suppliers" />
    );
}
