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

export function ChartOfAccountsPage({ Layout }) {
    return (
        <AccountsCrudListPage
            Layout={Layout}
            title="Chart of Accounts"
            subtitle="Ledger accounts for income, expense, assets, and liabilities."
            endpoint="/chart-of-accounts"
            createTo="/accounts/chart-of-accounts/create"
            rowLabel={(row) => row.name || row.title || `Account #${row.id}`}
        />
    );
}

function chartFormFields({ form, setForm }) {
    return (
        <>
            <input className={inputClass} placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} />
            <select className={inputClass} value={form.type || 'income'} onChange={(e) => setForm({ ...form, type: e.target.value })}>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
                <option value="asset">Asset</option>
                <option value="liability">Liability</option>
            </select>
            <input className={inputClass} placeholder="Code" value={form.code || ''} onChange={(e) => setForm({ ...form, code: e.target.value })} />
        </>
    );
}

