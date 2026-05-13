import React from 'react';
import { useParams } from 'react-router-dom';
import { AccountsSimpleFormPage, inputClass } from '../AccountsModuleShared';

function paymentMethodFields({ form, setForm }) {
    const fieldClass = 'space-y-1 md:col-span-1';
    const controlClass = `${inputClass} w-full`;

    return (
        <>
            <div className="rounded-xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5">
                <h3 className="text-sm font-semibold text-slate-900">Payment method details</h3>
                <p className="mt-1 text-xs text-slate-500">Configure how this channel appears in collection and accounting forms.</p>
                <div className="mt-4 grid gap-4 md:grid-cols-2">
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Method name</span>
                        <input
                            className={controlClass}
                            placeholder="e.g. Bank Transfer, Mobile Money, Cash"
                            value={form.name || ''}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                        />
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Status</span>
                        <select
                            className={controlClass}
                            value={String(form.is_active ?? 1)}
                            onChange={(e) => setForm({ ...form, is_active: Number(e.target.value) })}
                        >
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Description</span>
                        <textarea
                            className={controlClass}
                            rows={4}
                            placeholder="Optional details shown to staff."
                            value={form.description || ''}
                            onChange={(e) => setForm({ ...form, description: e.target.value })}
                        />
                    </label>
                </div>
            </div>
        </>
    );
}

export function PaymentMethodFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit payment method' : 'Create payment method'}
            edit={edit}
            id={id}
            loadPath="/payment-methods"
            storePath="/payment-methods/store"
            updatePath="/payment-methods/update"
            backTo="/payment-methods"
        >
            {paymentMethodFields}
        </AccountsSimpleFormPage>
    );
}

