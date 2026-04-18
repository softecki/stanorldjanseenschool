import React from 'react';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { hubCardClass } from '../components/hubCardClass';

export function CommunicationHomePage() {
    const items = [
        { to: '/communication/notice-board', label: 'Notice board' },
        { to: '/communication/smsmail', label: 'SMS / Mail' },
        { to: '/communication/smsmail/campaign', label: 'SMS campaign' },
        { to: '/communication/template', label: 'SMS / Mail templates' },
    ];
    return (
        <AdminLayout>
            <div className="mx-auto max-w-5xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">Communication</h1>
                    <p className="mt-1 text-sm text-gray-500">Notices, messaging, campaigns, and templates are implemented as SPA flows.</p>
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
