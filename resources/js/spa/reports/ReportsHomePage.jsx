import React from 'react';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { hubCardClass } from '../components/hubCardClass';

export function ReportsHomePage() {
    const items = [
        { to: '/reports/marksheet', label: 'Marksheet' },
        { to: '/reports/merit-list', label: 'Merit list' },
        { to: '/reports/progress-card', label: 'Progress card' },
        { to: '/reports/due-fees', label: 'Due fees' },
        { to: '/reports/class-routine', label: 'Class routine' },
        { to: '/reports/exam-routine', label: 'Exam routine' },
        { to: '/reports/duplicate-students', label: 'Duplicate students' },
        { to: '/reports/account', label: 'Account report' },
        { to: '/reports/accounting/income', label: 'Accounting income' },
        { to: '/reports/accounting/expense', label: 'Accounting expense' },
        { to: '/reports/accounting/profit-loss', label: 'Profit / loss' },
        { to: '/reports/accounting/dashboard', label: 'Accounting dashboard' },
        { to: '/reports/accounting/cashbook', label: 'Cashbook' },
        { to: '/reports/accounting/audit-log', label: 'Audit log' },
        { to: '/reports/fees-collection', label: 'Fees collection' },
        { to: '/reports/fees-summary', label: 'Fees summary' },
        { to: '/reports/students', label: 'Students list' },
        { to: '/reports/fees-by-year', label: 'Fees by year' },
        { to: '/reports/boarding-students', label: 'Boarding students' },
        { to: '/reports/boarding-students/missing-2026', label: 'Missing boarding 2026' },
        { to: '/reports/accounting/bank-reconciliation', label: 'Bank reconciliation' },
        { to: '/reports/accounting/bank-reconciliation/process', label: 'Bank reconciliation process' },
    ];
    return (
        <AdminLayout>
            <div className="mx-auto max-w-6xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">Reports</h1>
                    <p className="mt-1 text-sm text-gray-500">
                        Open a report to view data in the SPA layout (tables, filters where implemented, and raw JSON when needed).
                    </p>
                </div>
                <div className="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
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
