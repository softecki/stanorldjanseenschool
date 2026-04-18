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

export function IncomeFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit income' : 'Create income'}
            edit={edit}
            id={id}
            loadPath="/income"
            storePath="/income/store"
            updatePath="/income/update"
            backTo="/accounts/income"
        >
            {({ form, setForm, meta }) => (
                <>
                    <input className={inputClass} placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                    <input className={inputClass} placeholder="Amount" value={form.amount || ''} onChange={(e) => setForm({ ...form, amount: e.target.value })} />
                    <select className={inputClass} value={form.income_head || ''} onChange={(e) => setForm({ ...form, income_head: e.target.value })}>
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

