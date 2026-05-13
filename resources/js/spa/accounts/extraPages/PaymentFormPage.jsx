import React from 'react';
import { AcademicFormPage } from '../../academic/AcademicPages';

export function PaymentFormPage({ Layout }) {
    return (
        <AcademicFormPage
            Layout={Layout}
            titleCreate="Create Payment"
            titleEdit="Edit Payment"
            loadEndpoint="/payments"
            storeEndpoint="/payments/store"
            updateEndpoint="/payments/update"
            backTo="/payments"
        />
    );
}

