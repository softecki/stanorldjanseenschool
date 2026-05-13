import React from 'react';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';
import { hubCardClass } from '../components/hubCardClass';

const sections = [
    {
        title: 'Core',
        description: 'Branding, notifications, storage, and system tools.',
        items: [
            { to: '/settings/general', label: 'General settings', hint: 'Name, logos, language, session, currency, contact' },
            { to: '/settings/notification', label: 'Notification settings', hint: 'Channels, recipients, templates' },
            { to: '/settings/storage', label: 'Storage settings', hint: 'Upload driver & paths' },
            { to: '/settings/task-schedulers', label: 'Task schedules', hint: 'Cron-style overview' },
            { to: '/settings/software-update', label: 'Software update', hint: 'Migrations & version' },
        ],
    },
    {
        title: 'Security & messaging',
        description: 'Gateways and automated messages.',
        items: [
            { to: '/settings/recaptcha', label: 'reCAPTCHA', hint: 'Bot protection' },
            { to: '/settings/sms', label: 'SMS settings', hint: 'SMS provider' },
            { to: '/settings/payment-gateway', label: 'Payment gateway', hint: 'Online fees' },
            { to: '/settings/email', label: 'Email settings', hint: 'SMTP / mail' },
        ],
    },
    {
        title: 'Reference data',
        description: 'Dropdowns used across students and staff.',
        items: [
            { to: '/settings/genders', label: 'Genders', hint: 'CRUD + translate' },
            { to: '/settings/religions', label: 'Religions', hint: 'CRUD + translate' },
            { to: '/settings/sessions', label: 'Sessions', hint: 'Academic years' },
            { to: '/blood-groups', label: 'Blood groups', hint: 'Medical reference' },
            { to: '/banks-accounts', label: 'Bank accounts', hint: 'Fee receipts' },
        ],
    },
];

export function SettingsHomePage() {
    return (
        <AdminLayout>
            <div className="mx-auto max-w-6xl space-y-8 p-6">
                <section className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-slate-900 to-slate-950 px-6 py-8 text-white shadow-xl sm:px-8">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-300/90">Administration</p>
                    <h1 className="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">Settings</h1>
                    <p className="mt-2 max-w-2xl text-sm text-slate-200/95">
                        Configure the school profile, integrations, and shared lists. Items that still use the JSON explorer open the same API responses as
                        the legacy Blade screens until a dedicated form is added.
                    </p>
                </section>

                {sections.map((section) => (
                    <section key={section.title} className="space-y-3">
                        <div>
                            <h2 className="text-lg font-semibold text-slate-900">{section.title}</h2>
                            <p className="text-sm text-slate-500">{section.description}</p>
                        </div>
                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {section.items.map((item) => (
                                <Link key={item.to} to={item.to} className={hubCardClass}>
                                    <span className="font-medium text-gray-900 group-hover:text-blue-700">{item.label}</span>
                                    <span className="mt-1 block text-xs leading-snug text-gray-500">{item.hint}</span>
                                    <span className="mt-2 block truncate font-mono text-[11px] text-gray-400">{item.to}</span>
                                </Link>
                            ))}
                        </div>
                    </section>
                ))}
            </div>
        </AdminLayout>
    );
}
