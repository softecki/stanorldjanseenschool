import React from 'react';
import { AcademicFormPage } from '../../academic/AcademicPages';

export function TransactionFormPage({ Layout }) {
    return (
        <AcademicFormPage
            Layout={Layout}
            titleCreate="Create Transaction"
            titleEdit="Edit Transaction"
            loadEndpoint="/transactions"
            storeEndpoint="/transactions/store"
            updateEndpoint="/transactions/update"
            backTo="/accounts/transactions"
        />
    );
}

