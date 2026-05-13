import React from 'react';
import { useParams } from 'react-router-dom';
import { AccountsSimpleFormPage, inputClass } from '../AccountsModuleShared';

function incomeFields({ form, setForm, meta }) {
    const today = new Date().toISOString().slice(0, 10);
    const fieldClass = 'space-y-1 md:col-span-1';
    const controlClass = `${inputClass} w-full`;

    return (
        <>
            <div className="rounded-xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5">
                <h3 className="text-sm font-semibold text-slate-900">Income details</h3>
                <p className="mt-1 text-xs text-slate-500">Capture the source, amount, account head, and transaction date.</p>
                <div className="mt-4 grid gap-4 md:grid-cols-2">
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Name</span>
                        <input
                            className={controlClass}
                            placeholder="e.g. Donation, Uniform sales"
                            value={form.name || ''}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                        />
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Amount</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className={controlClass}
                            placeholder="0.00"
                            value={form.amount || ''}
                            onChange={(e) => setForm({ ...form, amount: e.target.value })}
                        />
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Income head</span>
                        <select
                            className={controlClass}
                            value={form.income_head || ''}
                            onChange={(e) => setForm({ ...form, income_head: e.target.value })}
                        >
                            <option value="">Select head</option>
                            {(meta.heads || []).map((h) => (
                                <option key={h.id} value={h.id}>
                                    {h.name || h.title}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Date</span>
                        <input
                            type="date"
                            className={controlClass}
                            value={form.date || today}
                            onChange={(e) => setForm({ ...form, date: e.target.value })}
                        />
                    </label>
                </div>
            </div>

            <div className="rounded-xl border border-slate-200/90 bg-white p-4 sm:p-5">
                <h3 className="text-sm font-semibold text-slate-900">Payment and notes</h3>
                <div className="mt-4 grid gap-4 md:grid-cols-2">
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Bank account</span>
                        <select
                            className={controlClass}
                            value={form.bank_account_id || form.account_number || ''}
                            onChange={(e) =>
                                setForm({
                                    ...form,
                                    bank_account_id: e.target.value || '',
                                    account_number: e.target.value || '',
                                })
                            }
                        >
                            <option value="">Select bank account (optional)</option>
                            {(meta.bank_accounts || []).map((a) => (
                                <option key={a.id} value={a.id}>
                                    {[a.bank_name, a.account_name, a.account_number].filter(Boolean).join(' - ')}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Invoice / receipt number</span>
                        <input
                            className={controlClass}
                            placeholder="Optional reference"
                            value={form.invoice_number || ''}
                            onChange={(e) => setForm({ ...form, invoice_number: e.target.value })}
                        />
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Description</span>
                        <textarea
                            className={controlClass}
                            rows={4}
                            placeholder="Description (optional)"
                            value={form.description || ''}
                            onChange={(e) => setForm({ ...form, description: e.target.value })}
                        />
                    </label>
                </div>
            </div>
        </>
    );
}

export function IncomeFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit income' : 'Create income'}
            edit={edit}
            id={id}
            loadPath="/income"
            storePath="/income/store"
            updatePath="/income/update"
            backTo="/income"
        >
            {incomeFields}
        </AccountsSimpleFormPage>
    );
}

