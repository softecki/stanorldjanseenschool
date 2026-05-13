import React from 'react';
import { ExpenseEntryFormPage } from './ExpenseFormPage';
import { ExpenseRecordsPage } from './ExpenseRecordsPage';

export function CashPage({ Layout }) {
    return (
        <ExpenseRecordsPage
            Layout={Layout}
            title="Cash"
            subtitle="Cash movement records with edit and delete actions."
            endpoint="/cash/cash"
            createTo="/cash/create"
            editBase="/cash"
            deleteBase="/cash/delete"
            createLabel="Create cash entry"
        />
    );
}

export function CashFormPage({ Layout, edit = false }) {
    return (
        <ExpenseEntryFormPage
            Layout={Layout}
            edit={edit}
            titleCreate="Create cash entry"
            titleEdit="Edit cash entry"
            loadPath="/cash"
            storePath="/cash/store"
            updatePath="/cash/update"
            backTo="/cash"
        />
    );
}

