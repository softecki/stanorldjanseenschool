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

export function FeesMasterFormPage({ Layout, edit = false }) {
    return (
        <FeesEntityFormPage
            Layout={Layout}
            edit={edit}
            titleCreate="Create Fees Master"
            titleEdit="Edit Fees Master"
            loadUrl="/fees-master"
            createUrl="/fees-master/store"
            updateUrl="/fees-master/update"
            backTo="/fees/masters"
            fields={({ meta, form, setForm, statusChoices }) => (
                <>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.fees_group_id || ''} onChange={(e) => setForm({ ...form, fees_group_id: e.target.value })} required>
                        <option value="">Select Fees Group</option>
                        {(meta.fees_groups || []).map((g) => <option key={g.id} value={g.id}>{optionLabel(g)}</option>)}
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.fees_type_id || ''} onChange={(e) => setForm({ ...form, fees_type_id: e.target.value })} required>
                        <option value="">Select Fees Type</option>
                        {(meta.fees_types || []).map((t) => <option key={t.id} value={t.id}>{optionLabel(t)}</option>)}
                    </select>
                    <input type="date" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={form.due_date || ''} onChange={(e) => setForm({ ...form, due_date: e.target.value })} />
                    <input type="number" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Amount" value={form.amount || ''} onChange={(e) => setForm({ ...form, amount: e.target.value })} />
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.fine_type || '1')} onChange={(e) => setForm({ ...form, fine_type: e.target.value })}>
                        <option value="1">None</option>
                        <option value="2">Percentage</option>
                        <option value="3">Fixed Amount</option>
                    </select>
                    <select className="rounded-lg border border-gray-200 px-3 py-2 text-sm" value={String(form.status || '1')} onChange={(e) => setForm({ ...form, status: e.target.value })}>{statusChoices()}</select>
                    <input type="number" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Percentage" value={form.percentage || 0} onChange={(e) => setForm({ ...form, percentage: e.target.value })} />
                    <input type="number" className="rounded-lg border border-gray-200 px-3 py-2 text-sm" placeholder="Fine Amount" value={form.fine_amount || 0} onChange={(e) => setForm({ ...form, fine_amount: e.target.value })} />
                </>
            )}
        />
    );
}

