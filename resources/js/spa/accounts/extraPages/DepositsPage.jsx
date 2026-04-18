import React from 'react';
import { AccountListPage } from '../AccountExtraShared';

export function DepositsPage({ Layout }) {
    return (
        <AccountListPage Layout={Layout} title="Deposits" endpoint="/deposit" createTo="/accounts/deposits/create" editBase="/accounts/deposits" />
    );
}
