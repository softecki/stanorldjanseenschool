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

/** Align with App\Enums\FineType: NONE=0, PERCENTAGE=1, FIX_AMOUNT=2 */
function mapFeesMasterLoadedData(raw) {
    const gid = raw?.fees_group_id ?? raw?.group?.id;
    const tid = raw?.fees_type_id ?? raw?.type?.id;
    let due = raw?.due_date;
    if (due != null && typeof due === 'string') {
        due = due.slice(0, 10);
    } else {
        due = '';
    }

    return {
        fees_group_id: gid != null && String(gid) !== '' ? String(gid) : '',
        fees_type_id: tid != null && String(tid) !== '' ? String(tid) : '',
        due_date: due,
        amount: raw?.amount != null && raw?.amount !== '' ? String(raw.amount) : '',
        fine_type: String(raw?.fine_type ?? '0'),
        percentage: raw?.percentage != null && raw?.percentage !== '' ? String(raw.percentage) : '0',
        fine_amount: raw?.fine_amount != null && raw?.fine_amount !== '' ? String(raw.fine_amount) : '0',
        status: normalizeStatusForForm(raw?.status),
    };
}

function buildFeesMasterSubmitPayload(form) {
    const ft = Number(form?.fine_type ?? 0);
    return {
        fees_group_id: Number(form?.fees_group_id),
        fees_type_id: Number(form?.fees_type_id),
        due_date: form?.due_date || null,
        amount: form?.amount,
        fine_type: ft,
        percentage: ft === 1 ? Number(form?.percentage ?? 0) : 0,
        fine_amount: ft === 2 ? Number(form?.fine_amount ?? 0) : ft === 1 ? Number(form?.fine_amount ?? 0) : 0,
        status: String(form?.status ?? '1'),
    };
}

export function FeesMasterFormPage({ Layout, edit = false }) {
    return (
        <FeesEntityFormPage
            Layout={Layout}
            edit={edit}
            titleCreate=""
            titleEdit="Edit Fees Master"
            subtitleCreate=""
            subtitleEdit="Update every field below. The active session from system settings is applied when you save."
            loadUrl="/fees-master"
            createUrl="/fees-master/store"
            updateUrl="/fees-master/update"
            backTo="/masters"
            mapLoadedData={mapFeesMasterLoadedData}
            buildSubmitPayload={buildFeesMasterSubmitPayload}
            fields={({ meta, form, setForm, statusChoices: choices, edit, id }) => {
                const ft = String(form.fine_type ?? '0');
                return (
                    <>
                        {edit ? (
                            <div className="md:col-span-2 rounded-lg border border-dashed border-gray-200 bg-gray-50/80 p-4">
                                <span className={fieldLabel}>Master ID</span>
                                <p className={`${fieldInput} border-transparent bg-white font-mono text-gray-700`}>{id}</p>
                                <p className={fieldHint}>Read-only. Session is taken from system settings on save.</p>
                            </div>
                        ) : null}

                        <div>
                            <label className={fieldLabel} htmlFor="fm-group">
                                Fees group <span className="text-rose-600">*</span>
                            </label>
                            <select
                                id="fm-group"
                                className={fieldInput}
                                value={form.fees_group_id || ''}
                                onChange={(e) => setForm({ ...form, fees_group_id: e.target.value })}
                                required
                            >
                                <option value="">Select fees group</option>
                                {(meta.fees_groups || []).map((g) => (
                                    <option key={g.id} value={g.id}>
                                        {g.name || g.title || `#${g.id}`}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className={fieldLabel} htmlFor="fm-type">
                                Fees type <span className="text-rose-600">*</span>
                            </label>
                            <select
                                id="fm-type"
                                className={fieldInput}
                                value={form.fees_type_id || ''}
                                onChange={(e) => setForm({ ...form, fees_type_id: e.target.value })}
                                required
                            >
                                <option value="">Select fees type</option>
                                {(meta.fees_types || []).map((t) => (
                                    <option key={t.id} value={t.id}>
                                        {t.name || t.title || `#${t.id}`}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className={fieldLabel} htmlFor="fm-due">
                                Due date <span className="text-rose-600">*</span>
                            </label>
                            <input
                                id="fm-due"
                                type="date"
                                className={fieldInput}
                                value={form.due_date || ''}
                                onChange={(e) => setForm({ ...form, due_date: e.target.value })}
                                required
                            />
                            <p className={fieldHint}>Shown on statements and collection screens.</p>
                        </div>

                        <div>
                            <label className={fieldLabel} htmlFor="fm-amount">
                                Amount <span className="text-rose-600">*</span>
                            </label>
                            <input
                                id="fm-amount"
                                type="number"
                                step="0.01"
                                min="0"
                                className={fieldInput}
                                placeholder="0.00"
                                value={form.amount ?? ''}
                                onChange={(e) => setForm({ ...form, amount: e.target.value })}
                                required
                            />
                        </div>

                        <div>
                            <label className={fieldLabel} htmlFor="fm-status">
                                Status <span className="text-rose-600">*</span>
                            </label>
                            <select
                                id="fm-status"
                                className={fieldInput}
                                value={String(form.status ?? '1')}
                                onChange={(e) => setForm({ ...form, status: e.target.value })}
                            >
                                {choices()}
                            </select>
                        </div>

                        <div className="md:col-span-2 border-t border-gray-100 pt-2">
                            <p className={fieldLabel}>Late fee (fine)</p>
                            <label className={fieldLabel} htmlFor="fm-fine-type">
                                Fine type <span className="text-rose-600">*</span>
                            </label>
                            <select
                                id="fm-fine-type"
                                className={fieldInput}
                                value={ft}
                                onChange={(e) => setForm({ ...form, fine_type: e.target.value })}
                            >
                                <option value="0">No fine</option>
                                <option value="1">Percentage of base amount</option>
                                <option value="2">Fixed amount</option>
                            </select>
                            <p className={fieldHint}>Matches system enum: 0 = none, 1 = percentage, 2 = fixed.</p>
                        </div>

                        {ft === '1' ? (
                            <div>
                                <label className={fieldLabel} htmlFor="fm-pct">
                                    Percentage <span className="text-rose-600">*</span>
                                </label>
                                <input
                                    id="fm-pct"
                                    type="number"
                                    min="0"
                                    max="100"
                                    className={fieldInput}
                                    value={form.percentage ?? '0'}
                                    onChange={(e) => setForm({ ...form, percentage: e.target.value })}
                                    required
                                />
                                <p className={fieldHint}>0–100. Optional fine amount may also be stored for reporting.</p>
                            </div>
                        ) : null}

                        {ft === '1' || ft === '2' ? (
                            <div>
                                <label className={fieldLabel} htmlFor="fm-fine-amt">
                                    {ft === '2' ? (
                                        <>
                                            Fine amount <span className="text-rose-600">*</span>
                                        </>
                                    ) : (
                                        <>Fine amount (optional)</>
                                    )}
                                </label>
                                <input
                                    id="fm-fine-amt"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    className={fieldInput}
                                    value={form.fine_amount ?? '0'}
                                    onChange={(e) => setForm({ ...form, fine_amount: e.target.value })}
                                    required={ft === '2'}
                                />
                            </div>
                        ) : null}

                        {ft === '0' ? (
                            <div className="md:col-span-2 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                                No late fee: percentage and fine amount are ignored on save.
                            </div>
                        ) : null}
                    </>
                );
            }}
        />
    );
}
