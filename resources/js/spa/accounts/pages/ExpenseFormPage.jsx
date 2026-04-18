import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
    AccountCard,
    AccountEmptyState,
    AccountPageHeader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import {
    AccountFullPageLoader,
    AccountsCrudListPage,
    AccountsHomePageComponent,
    AccountsPageShell,
    AccountsSectionHeader,
    AccountsSimpleFormPage,
    btnGhost,
    btnPrimary,
    extractRows,
    inputClass,
} from '../AccountsModuleShared';

export function ExpenseFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit expense' : 'Create expense'}
            edit={edit}
            id={id}
            loadPath="/expense"
            storePath="/expense/store"
            updatePath="/expense/update"
            backTo="/accounts/expense"
        >
            {({ form, setForm, meta }) => (
                <>
                    <input className={inputClass} placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                    <input className={inputClass} placeholder="Amount" value={form.amount || ''} onChange={(e) => setForm({ ...form, amount: e.target.value })} />
                    <select className={inputClass} value={form.expense_head || ''} onChange={(e) => setForm({ ...form, expense_head: e.target.value })}>
                        <option value="">Select head</option>
                        {(meta.heads || []).map((h) => (
                            <option key={h.id} value={h.id}>
                                {h.name || h.title}
                            </option>
                        ))}
                    </select>
                </>
            )}
        </AccountsSimpleFormPage>
    );
}

/** Payload / JSON report pages with readable layout. */
