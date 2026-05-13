import React from 'react';
import { AcademicFormPage } from '../../academic/AcademicPages';

export function SupplierFormPage({ Layout }) {
    return (
        <AcademicFormPage
            Layout={Layout}
            titleCreate="Create Supplier"
            titleEdit="Edit Supplier"
            loadEndpoint="/suppliers"
            storeEndpoint="/suppliers/store"
            updateEndpoint="/suppliers/update"
            backTo="/suppliers"
        />
    );
}

