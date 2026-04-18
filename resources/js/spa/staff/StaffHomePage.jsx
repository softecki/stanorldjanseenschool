import React from 'react';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { hubCardClass } from '../components/hubCardClass';

export function StaffHomePage() {
    const items = [
        { to: '/roles', label: 'Roles' },
        { to: '/users', label: 'Staff' },
        { to: '/staff/department', label: 'Department' },
        { to: '/staff/batch-processing', label: 'Batch processing' },
        { to: '/staff/designation', label: 'Designation' },
    ];
    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">Staff manage</h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Shortcuts match the sidebar. List screens load from the same JSON endpoints as the legacy Blade pages.
                    </p>
                </div>
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    {items.map((item) => (
                        <Link key={item.to} to={item.to} className={hubCardClass}>
                            <span className="font-medium text-gray-900 group-hover:text-blue-700">{item.label}</span>
                            <span className="mt-2 text-xs text-gray-400">{item.to}</span>
                        </Link>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
