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

function mapFeesTypeLoadedData(raw) {
    const cid = raw?.class_id;
    const fromRel = raw?.school_class?.id;
    const resolved = fromRel != null && Number(fromRel) > 0 ? String(fromRel) : cid != null && Number(cid) > 0 ? String(cid) : '0';

    const cats = Array.isArray(raw?.student_categories)
        ? raw.student_categories.map((c) => String(c.id))
        : Array.isArray(raw?.student_category_ids)
          ? raw.student_category_ids.map((id) => String(id))
          : [];

    return {
        name: raw?.name ?? '',
        code: raw?.code ?? '',
        description: raw?.description ?? '',
        status: normalizeStatusForForm(raw?.status),
        class_id: resolved,
        student_category_ids: cats,
    };
}

function buildFeesTypeSubmitPayload(form) {
    const rawIds = form?.student_category_ids;
    const student_category_ids = Array.isArray(rawIds)
        ? rawIds.map((id) => Number(id)).filter((n) => Number.isFinite(n) && n > 0)
        : [];

    return {
        name: form?.name ?? '',
        code: form?.code ?? '',
        description: form?.description ?? '',
        status: form?.status ?? '1',
        class_id: form?.class_id == null || form?.class_id === '' || form?.class_id === '0' ? 0 : Number(form.class_id),
        student_category_ids,
    };
}

export function FeesTypeFormPage({ Layout, edit = false }) {
    return (
        <FeesEntityFormPage
            Layout={Layout}
            edit={edit}
            titleCreate=""
            titleEdit="Edit Fees Type"
            subtitleCreate=""
            subtitleEdit="All fields below map directly to the database row for this fee type (including linked student categories)."
            loadUrl="/fees-type"
            createUrl="/fees-type/store"
            updateUrl="/fees-type/update"
            backTo="/types"
            mapLoadedData={mapFeesTypeLoadedData}
            buildSubmitPayload={buildFeesTypeSubmitPayload}
            fields={({ meta, form, setForm, statusChoices: choices, edit, id }) => (
                <>
                    {edit ? (
                        <div className="md:col-span-2 rounded-lg border border-dashed border-gray-200 bg-gray-50/80 p-4">
                            <span className={fieldLabel}>Type ID</span>
                            <p className={`${fieldInput} border-transparent bg-white font-mono text-gray-700`}>{id}</p>
                            <p className={fieldHint}>Read-only database identifier.</p>
                        </div>
                    ) : null}

                    <div>
                        <label className={fieldLabel} htmlFor="fees-type-name">
                            Type name <span className="text-rose-600">*</span>
                        </label>
                        <input
                            id="fees-type-name"
                            className={fieldInput}
                            placeholder="e.g. Tuition fee"
                            value={form.name || ''}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                            required
                            autoComplete="off"
                        />
                        <p className={fieldHint}>Displayed in fee masters and related screens.</p>
                    </div>

                    <div>
                        <label className={fieldLabel} htmlFor="fees-type-code">
                            Code
                        </label>
                        <input
                            id="fees-type-code"
                            className={fieldInput}
                            placeholder="e.g. TUIT"
                            value={form.code || ''}
                            onChange={(e) => setForm({ ...form, code: e.target.value })}
                            autoComplete="off"
                        />
                        <p className={fieldHint}>Optional short code for exports or integrations.</p>
                    </div>

                    <div>
                        <label className={fieldLabel} htmlFor="fees-type-status">
                            Status <span className="text-rose-600">*</span>
                        </label>
                        <select
                            id="fees-type-status"
                            className={fieldInput}
                            value={String(form.status ?? '1')}
                            onChange={(e) => setForm({ ...form, status: e.target.value })}
                        >
                            {choices()}
                        </select>
                        <p className={fieldHint}>Inactive types are omitted from active-only pickers.</p>
                    </div>

                    <div>
                        <label className={fieldLabel} htmlFor="fees-type-class">
                            Class scope
                        </label>
                        <select
                            id="fees-type-class"
                            className={fieldInput}
                            value={String(form.class_id ?? '0')}
                            onChange={(e) => setForm({ ...form, class_id: e.target.value })}
                        >
                            <option value="0">None (all classes)</option>
                            {(meta.classes || []).map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </select>
                        <p className={fieldHint}>Restrict this type to one class, or leave as None.</p>
                    </div>

                    <div className="md:col-span-2 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 shadow-sm">
                        <h2 className="text-sm font-bold uppercase tracking-wide text-indigo-900">Student category assignment</h2>
                        <p className={`${fieldHint} mt-1 text-gray-700`}>
                            Optional: limit this fee type to specific student categories. Each category can only be linked to one fee type at a time. Manage categories under{' '}
                            <a href="/categories" className="font-semibold text-blue-700 underline hover:text-blue-900">
                                /categories
                            </a>
                            . Leave all unchecked so this type is not restricted by category.
                        </p>
                        <div className="mt-3 max-h-56 overflow-y-auto rounded-lg border border-indigo-200/80 bg-white p-3 shadow-inner">
                            {(meta.student_categories || []).length === 0 ? (
                                <p className="text-sm text-gray-600">
                                    No student categories found. Create at least one category under{' '}
                                    <a href="/categories/create" className="font-semibold text-blue-700 underline">
                                        /categories/create
                                    </a>{' '}
                                    first.
                                </p>
                            ) : (
                                <ul className="grid gap-2 sm:grid-cols-2">
                                    {(meta.student_categories || []).map((c) => {
                                        const idStr = String(c.id);
                                        const checked = (form.student_category_ids || []).includes(idStr);
                                        const inactive =
                                            c.status === 0 ||
                                            c.status === '0' ||
                                            String(c.status) === '2';
                                        return (
                                            <li key={c.id}>
                                                <label className="flex cursor-pointer items-start gap-2 rounded-md border border-transparent px-2 py-1.5 hover:bg-indigo-50/60">
                                                    <input
                                                        type="checkbox"
                                                        className="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                        checked={checked}
                                                        onChange={(e) => {
                                                            const cur = new Set(form.student_category_ids || []);
                                                            if (e.target.checked) cur.add(idStr);
                                                            else cur.delete(idStr);
                                                            setForm({ ...form, student_category_ids: [...cur] });
                                                        }}
                                                    />
                                                    <span className="text-sm text-gray-900">
                                                        {c.name}
                                                        {inactive ? (
                                                            <span className="ml-1.5 text-xs font-medium text-amber-700">(Inactive)</span>
                                                        ) : null}
                                                    </span>
                                                </label>
                                            </li>
                                        );
                                    })}
                                </ul>
                            )}
                        </div>
                    </div>

                    <div className="md:col-span-2">
                        <label className={fieldLabel} htmlFor="fees-type-description">
                            Description
                        </label>
                        <textarea
                            id="fees-type-description"
                            className={fieldInput}
                            rows={4}
                            placeholder="Internal notes (billing rules, GL hints, etc.)"
                            value={form.description ?? ''}
                            onChange={(e) => setForm({ ...form, description: e.target.value })}
                        />
                        <p className={fieldHint}>Optional; visible on the detail view.</p>
                    </div>
                </>
            )}
        />
    );
}
