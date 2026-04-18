import React from 'react';
import { AcademicFormPage } from '../../academic/AcademicPages';

export function InvoiceFormPage({ Layout }) {
    return (
        <AcademicFormPage
            Layout={Layout}
            titleCreate="Create Invoice"
            titleEdit="Edit Invoice"
            loadEndpoint="/invoices"
            storeEndpoint="/invoices/store"
            updateEndpoint="/invoices/update"
            backTo="/accounts/invoices"
        />
    );
}

