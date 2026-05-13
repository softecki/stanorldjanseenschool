import React from 'react';
import { useParams } from 'react-router-dom';
import { ExpenseEntryFormPage } from '../pages/ExpenseFormPage';

export function DepositFormPage({ Layout }) {
    const { id } = useParams();
    return (
        <ExpenseEntryFormPage
            Layout={Layout}
            titleCreate="Create Deposit"
            titleEdit="Edit Deposit"
            edit={Boolean(id)}
            loadPath="/deposit"
            storePath="/deposit/store"
            updatePath="/deposit/update"
            backTo="/deposits"
        />
    );
}

