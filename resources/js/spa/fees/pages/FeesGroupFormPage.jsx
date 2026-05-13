import React from 'react';
import { FeesEntityFormPage } from '../FeesModuleShared';

const fieldLabel = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600';
const fieldInput = 'w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500';
const fieldHint = 'mt-1 text-xs text-gray-500';

function normalizeStatusForForm(status) {
    const s = String(status ?? '1');
    if (s === '2') return '0';
    if (s === '0' || s === '1') return s;
    return '1';
}

/** Keep only editable scalar fields; API may include fee_masters, fee_assigns, counts, etc. */
function mapFeesGroupLoadedData(raw) {
    return {
        name: raw?.name ?? '',
        description: raw?.description ?? '',
        status: normalizeStatusForForm(raw?.status),
        online_admission_fees: Number(raw?.online_admission_fees) === 1 ? 1 : 0,
    };
}

function buildFeesGroupSubmitPayload(form) {
    return {
        name: form?.name ?? '',
        description: form?.description ?? '',
        status: form?.status ?? '1',
        online_admission_fees: Number(form?.online_admission_fees) === 1 ? 1 : 0,
    };
}

export function FeesGroupFormPage({ Layout, edit = false }) {
    return (
        <FeesEntityFormPage
            Layout={Layout}
            edit={edit}
            titleCreate=""
            titleEdit="Edit Fees Group"
            subtitleCreate=""
            subtitleEdit="Update any field below. Changes apply to new fees masters and assignments that use this group."
            loadUrl="/fees-group"
            createUrl="/fees-group/store"
            updateUrl="/fees-group/update"
            backTo="/groups"
            mapLoadedData={mapFeesGroupLoadedData}
            buildSubmitPayload={buildFeesGroupSubmitPayload}
            fields={({ form, setForm, statusChoices: choices, edit, id }) => (
                <>
                    {edit ? (
                        <div className="md:col-span-2 rounded-lg border border-dashed border-gray-200 bg-gray-50/80 p-4">
                            <span className={fieldLabel}>Group ID</span>
                            <p className={`${fieldInput} border-transparent bg-white font-mono text-gray-700`}>{id}</p>
                            <p className={fieldHint}>This value is read-only and identifies the record in the database.</p>
                        </div>
                    ) : null}

                    <div>
                        <label className={fieldLabel} htmlFor="fees-group-name">
                            Group name <span className="text-rose-600">*</span>
                        </label>
                        <input
                            id="fees-group-name"
                            className={fieldInput}
                            placeholder="e.g. Annual tuition bundle"
                            value={form.name || ''}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                            required
                            autoComplete="off"
                        />
                        <p className={fieldHint}>Shown across fees masters, assignments, and reports.</p>
                    </div>

                    <div>
                        <label className={fieldLabel} htmlFor="fees-group-status">
                            Status <span className="text-rose-600">*</span>
                        </label>
                        <select
                            id="fees-group-status"
                            className={fieldInput}
                            value={String(form.status ?? '1')}
                            onChange={(e) => setForm({ ...form, status: e.target.value })}
                        >
                            {choices()}
                        </select>
                        <p className={fieldHint}>Inactive groups are hidden from active selection lists where applicable.</p>
                    </div>

                    <div className="md:col-span-2">
                        <label className={fieldLabel} htmlFor="fees-group-description">
                            Description
                        </label>
                        <textarea
                            id="fees-group-description"
                            className={fieldInput}
                            rows={4}
                            placeholder="Optional notes for administrators (purpose, rules, etc.)"
                            value={form.description ?? ''}
                            onChange={(e) => setForm({ ...form, description: e.target.value })}
                        />
                        <p className={fieldHint}>Stored as plain text; leave blank if not needed.</p>
                    </div>

                    <div className="md:col-span-2 rounded-lg border border-gray-100 bg-gray-50/50 p-4">
                        <label className="flex cursor-pointer items-start gap-3 text-sm text-gray-800" htmlFor="fees-group-online-admission">
                            <input
                                id="fees-group-online-admission"
                                type="checkbox"
                                className="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                checked={Number(form.online_admission_fees) === 1}
                                onChange={(e) => setForm({ ...form, online_admission_fees: e.target.checked ? 1 : 0 })}
                            />
                            <span>
                                <span className="font-semibold text-gray-900">Online admission fees</span>
                                <span className="mt-0.5 block text-xs font-normal text-gray-600">
                                    When enabled, this group can be tied to online admission fee flows (see system configuration).
                                </span>
                            </span>
                        </label>
                    </div>
                </>
            )}
        />
    );
}
