import React from 'react';
import { useParams } from 'react-router-dom';
import { AccountsSimpleFormPage, inputClass } from '../AccountsModuleShared';

function flattenParents(nodes, depth = 0, out = [], excludedId = null) {
    (nodes || []).forEach((node) => {
        if (!node || typeof node !== 'object') return;
        if (excludedId !== null && String(node.id) === excludedId) return;
        out.push({ id: node.id, name: `${'— '.repeat(depth)}${node.name || `#${node.id}`}` });
        if (Array.isArray(node.children) && node.children.length > 0) {
            flattenParents(node.children, depth + 1, out, excludedId);
        }
    });
    return out;
}

function chartFormFields({ form, setForm, meta }) {
    const currentId = form?.id == null ? null : String(form.id);
    const parentOptions = flattenParents(meta?.parents || [], 0, [], currentId);
    const fieldClass = 'space-y-1 md:col-span-1';
    const controlClass = `${inputClass} w-full`;

    return (
        <>
            <div className="rounded-xl border border-slate-200/90 bg-slate-50/60 p-4 sm:p-5">
                <h3 className="text-sm font-semibold text-slate-900">Basic Information</h3>
                <p className="mt-1 text-xs text-slate-500">Define the account identity, classification, and lifecycle status.</p>
                <div className="mt-4 grid gap-4 md:grid-cols-2">
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Account name</span>
                        <input
                            className={controlClass}
                            placeholder="e.g. Tuition Fees"
                            value={form.name || ''}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                        />
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Code (optional)</span>
                        <input
                            className={controlClass}
                            placeholder="e.g. INC-001"
                            value={form.code || ''}
                            onChange={(e) => setForm({ ...form, code: e.target.value })}
                        />
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Type</span>
                        <select
                            className={controlClass}
                            value={form.type || 'income'}
                            onChange={(e) => setForm({ ...form, type: e.target.value })}
                        >
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
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
            <div className="rounded-xl border border-slate-200/90 bg-white p-4 sm:p-5">
                <h3 className="text-sm font-semibold text-slate-900">Structure and Notes</h3>
                <div className="mt-4 grid gap-4 md:grid-cols-2">
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Parent account</span>
                        <select
                            className={controlClass}
                            value={form.parent_id || ''}
                            onChange={(e) => setForm({ ...form, parent_id: e.target.value || null })}
                        >
                            <option value="">No parent</option>
                            {parentOptions.map((p) => (
                                <option key={p.id} value={p.id}>
                                    {p.name}
                                </option>
                            ))}
                        </select>
                    </label>
                    <label className={fieldClass}>
                        <span className="text-xs font-medium text-slate-600">Description</span>
                        <textarea
                            className={controlClass}
                            placeholder="Write a short note about this account..."
                            rows={4}
                            value={form.description || ''}
                            onChange={(e) => setForm({ ...form, description: e.target.value })}
                        />
                    </label>
                </div>
            </div>
        </>
    );
}

export function ChartOfAccountsFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit chart of account' : 'Create chart of account'}
            edit={edit}
            id={id}
            loadPath="/chart-of-accounts"
            storePath="/chart-of-accounts/store"
            updatePath="/chart-of-accounts/update"
            backTo="/chart-of-accounts"
        >
            {chartFormFields}
        </AccountsSimpleFormPage>
    );
}

