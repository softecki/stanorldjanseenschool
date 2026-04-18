import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    ActionButtons,
    EntityListPage,
    EntityViewPage,
    FeesEntityFormPage,
    FullPageLoader,
    TransactionsListPage,
    normalizeFeesTransactionRows,
    optionLabel,
    panelTitle,
    statusChoices,
    studentsTableClass,
} from '../FeesModuleShared';

export function FeesTypeFormPage({ Layout, edit = false }) {
    return (
        <FeesEntityFormPage
            Layout={Layout}
            edit={edit}
            titleCreate="Create Fees Type"
            titleEdit="Edit Fees Type"
            loadUrl="/fees-type"
            createUrl="/fees-type/store"
            updateUrl="/fees-type/update"
            backTo="/fees/types"
            fields={({ meta, form, setForm, statusChoices }) => (
                <>
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Code" value={form.code || ''} onChange={(e) => setForm({ ...form, code: e.target.value })} />
                    <textarea className="rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-2" rows={3} placeholder="Description" value={form.description || ''} onChange={(e) => setForm({ ...form, description: e.target.value })} />
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.class_id || '0')} onChange={(e) => setForm({ ...form, class_id: e.target.value })}>
                        <option value="0">None</option>
                        {(meta.classes || []).map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.status || '1')} onChange={(e) => setForm({ ...form, status: e.target.value })}>{statusChoices()}</select>
                </>
            )}
        />
    );
}

