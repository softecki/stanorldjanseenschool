import React from 'react';

import { AdminLayout } from '../layout/AdminLayout';
import { StaffUsersTable } from './StaffUsersTable';

export function UsersPage() {
    return (
        <AdminLayout>
            <StaffUsersTable pageTitle="Users" />
        </AdminLayout>
    );
}
