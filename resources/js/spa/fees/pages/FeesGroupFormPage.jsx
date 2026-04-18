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

export function FeesGroupFormPage({ Layout, edit = false }) {
    return (
        <FeesEntityFormPage
            Layout={Layout}
            edit={edit}
            titleCreate="Create Fees Group"
            titleEdit="Edit Fees Group"
            loadUrl="/fees-group"
            createUrl="/fees-group/store"
            updateUrl="/fees-group/update"
            backTo="/fees/groups"
            fields={({ form, setForm, statusChoices }) => (
                <>
                    <input className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} required />
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.status || '1')} onChange={(e) => setForm({ ...form, status: e.target.value })}>{statusChoices()}</select>
                    <textarea className="rounded-lg border border-gray-200 px-3 py-2 text-sm md:col-span-2" rows={3} placeholder="Description" value={form.description || ''} onChange={(e) => setForm({ ...form, description: e.target.value })} />
                    <label className="inline-flex items-center gap-2 text-sm text-gray-700 md:col-span-2">
                        <input type="checkbox" checked={String(form.online_admission_fees || '0') === '1'} onChange={(e) => setForm({ ...form, online_admission_fees: e.target.checked ? 1 : 0 })} />
                        Online Admission Fees
                    </label>
                </>
            )}
        />
    );
}

