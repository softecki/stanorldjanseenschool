import React from 'react';
import { useParams } from 'react-router-dom';
import { AccountsSimpleFormPage, inputClass } from '../AccountsModuleShared';

function accountHeadFields({ form, setForm }) {
    const fieldClass = 'space-y-1 md:col-span-1';
    const controlClass = `${inputClass} w-full`;

    return (
        <>
            <div className="rounded-xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5">
                <h3 className="text-sm font-semibold text-slate-900">Account head setup</h3>
                <p className="mt-1 text-xs text-slate-500">Define where this head belongs and whether it is active.</p>
                <div className="mt-4 grid gap-4 md:grid-cols-2">
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Head name</span>
                        <input
                            className={controlClass}
                            placeholder="e.g. Transport, School Store"
                            value={form.name || ''}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                        />
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Type</span>
                        <select
                            className={controlClass}
                            value={form.type || 'expense'}
                            onChange={(e) => setForm({ ...form, type: e.target.value })}
                        >
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Status</span>
                        <select
                            className={controlClass}
                            value={String(form.status ?? 1)}
                            onChange={(e) => setForm({ ...form, status: Number(e.target.value) })}
                        >
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </label>
                </div>
            </div>
        </>
    );
}

export function AccountHeadFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit account head' : 'Create account head'}
            edit={edit}
            id={id}
            loadPath="/account-head"
            storePath="/account-head/store"
            updatePath="/account-head/update"
            backTo="/account-heads"
        >
            {accountHeadFields}
        </AccountsSimpleFormPage>
    );
}

