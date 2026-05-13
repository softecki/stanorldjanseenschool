import React from 'react';
import { AdminLayout } from '../layout/AdminLayout';
import { StaffUsersTable } from './StaffUsersTable';

export function StaffHomePage() {
    return (
        <AdminLayout>
            <StaffUsersTable
                pageTitle="Staff"
                subtitle="Staff accounts linked to HR records. Use Edit for the full form, Active/Off for status, or Delete to remove the staff row and login user."
            />
        </AdminLayout>
    );
}
