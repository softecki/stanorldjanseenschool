import React from 'react';
import { Link } from 'react-router-dom';
import { AdminLayout } from '../layout/AdminLayout';

export function ReportsHomePage() {
    const sections = [
        {
            title: 'Fees & students',
            description: 'Collection, balances, student lists, and boarding reports.',
            accent: 'from-blue-600 to-indigo-600',
            items: [
                { to: '/reports/fees-collection', label: 'Fees collection', desc: 'Review fee payments with class and date filters.' },
                { to: '/reports/outstanding-breakdown', label: 'Break Down Report', desc: 'See selected fee type breakdowns by student.' },
                { to: '/reports/fees-summary', label: 'Fees summary', desc: 'Summarized fee collection totals.' },
                { to: '/reports/students', label: 'Students list', desc: 'Export and review student report data.' },
                { to: '/reports/fees-by-year', label: 'Fees by year', desc: 'Track fee balances year by year.' },
                { to: '/reports/boarding-students', label: 'Boarding students', desc: 'Monitor boarding student fee records.' },
            ],
        },
        {
            title: 'Accounting',
            description: 'Income, expense, reconciliation, and financial control reports.',
            accent: 'from-emerald-600 to-teal-600',
            items: [
                { to: '/reports/account', label: 'Account report', desc: 'Search and review account records.' },
                { to: '/reports/accounting/income', label: 'Accounting income', desc: 'Manual income report excluding duplicated fee totals.' },
                { to: '/reports/accounting/expense', label: 'Accounting expense', desc: 'Review expense movements and categories.' },
                { to: '/reports/accounting/profit-loss', label: 'Profit / loss', desc: 'Compare income and expense performance.' },
                { to: '/reports/accounting/cashbook', label: 'Cashbook', desc: 'View cash movement entries.' },
                { to: '/reports/accounting/audit-log', label: 'Audit log', desc: 'Track important accounting changes.' },
                { to: '/reports/accounting/bank-reconciliation', label: 'Bank reconciliation', desc: 'Compare bank balances and transactions.' },
            ],
        },
        {
            title: 'Academic',
            description: 'Exam, marks, routine, and student quality reports.',
            accent: 'from-purple-600 to-fuchsia-600',
            items: [
                { to: '/reports/marksheet', label: 'Marksheet', desc: 'Generate marksheet reports.' },
                { to: '/reports/merit-list', label: 'Merit list', desc: 'Rank students by academic performance.' },
                { to: '/reports/progress-card', label: 'Progress card', desc: 'Create progress card summaries.' },
                { to: '/reports/due-fees', label: 'Due fees', desc: 'Check student fee dues from report filters.' },
                { to: '/reports/class-routine', label: 'Class routine', desc: 'Review class timetable reports.' },
                { to: '/reports/exam-routine', label: 'Exam routine', desc: 'Review exam timetable reports.' },
                { to: '/reports/duplicate-students', label: 'Duplicate students', desc: 'Find possible duplicate student records.' },
            ],
        },
    ];

    const totalReports = sections.reduce((total, section) => total + section.items.length, 0);

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl space-y-8 p-6">
                <div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div className="grid gap-6 bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 p-8 text-white lg:grid-cols-[1fr_auto] lg:items-center">
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-[0.3em] text-blue-200">Reporting center</p>
                            <h1 className="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Reports</h1>
                            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-200">
                                Open school, fees, academic, and accounting reports from one clean workspace.
                            </p>
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-white/10 p-5 text-center shadow-inner">
                            <p className="text-3xl font-bold">{totalReports}</p>
                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-blue-100">Available reports</p>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6">
                    {sections.map((section) => (
                        <section key={section.title} className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className={`h-1.5 bg-gradient-to-r ${section.accent}`} />
                            <div className="border-b border-slate-100 p-5">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h2 className="text-lg font-semibold text-slate-950">{section.title}</h2>
                                        <p className="mt-1 text-sm text-slate-500">{section.description}</p>
                                    </div>
                                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {section.items.length} reports
                                    </span>
                                </div>
                            </div>
                            <div className="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-3">
                                {section.items.map((item) => (
                                    <Link
                                        key={item.to}
                                        to={item.to}
                                        className="group rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white hover:shadow-md"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 className="font-semibold text-slate-900 group-hover:text-blue-700">{item.label}</h3>
                                                <p className="mt-2 text-sm leading-5 text-slate-500">{item.desc}</p>
                                            </div>
                                            <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-sm font-bold text-slate-400 shadow-sm ring-1 ring-slate-200 group-hover:bg-blue-600 group-hover:text-white group-hover:ring-blue-600">
                                                &rarr;
                                            </span>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>
            </div>
        </AdminLayout>
    );
}
