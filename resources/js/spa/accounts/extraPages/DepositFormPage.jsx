import React from 'react';
import { AcademicFormPage } from '../../academic/AcademicPages';

export function DepositFormPage({ Layout }) {
    return (
        <AcademicFormPage
            Layout={Layout}
            titleCreate="Create Deposit"
            titleEdit="Edit Deposit"
            loadEndpoint="/deposit"
            storeEndpoint="/deposit/store"
            updateEndpoint="/deposit/update"
            backTo="/accounts/deposits"
        />
    );
}

