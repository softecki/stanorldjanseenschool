import React from 'react';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { hubCardClass } from '../components/hubCardClass';

export function SettingsHomePage() {
    const items = [
        { to: '/settings/general', label: 'General settings' },
        { to: '/settings/notification', label: 'Notification setting' },
        { to: '/settings/storage', label: 'Storage settings' },
        { to: '/settings/task-schedulers', label: 'Task schedules' },
        { to: '/settings/software-update', label: 'Software update' },
        { to: '/settings/recaptcha', label: 'Recaptcha' },
        { to: '/settings/sms', label: 'SMS settings' },
        { to: '/settings/payment-gateway', label: 'Payment gateway' },
        { to: '/settings/email', label: 'Email settings' },
        { to: '/settings/genders', label: 'Genders' },
        { to: '/banks-accounts', label: 'Bank accounts' },
        { to: '/settings/religions', label: 'Religions' },
        { to: '/blood-groups', label: 'Blood groups' },
        { to: '/settings/sessions', label: 'Sessions' },
    ];
    return (
        <AdminLayout>
            <div className="mx-auto max-w-6xl space-y-6 p-6">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">Settings</h1>
                    <p className="mt-1 text-sm text-gray-500">Structured screens use the SPA explorer where a full form has not been ported yet.</p>
                </div>
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
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
