import React from 'react';
import { ExpenseRecordsPage } from '../pages/ExpenseRecordsPage';

export function DepositsPage({ Layout }) {
    return (
        <ExpenseRecordsPage
            Layout={Layout}
            title="Deposits"
            subtitle="Deposit records with bank accounts, references, and actions."
            endpoint="/deposit"
            createTo="/deposits/create"
            editBase="/deposits"
            deleteBase="/deposit/delete"
            createLabel="Create deposit"
        />
    );
}
