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

export function AccountHeadFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit account head' : 'Create account head'}
            edit={edit}
            id={id}
            loadPath="/account-head"
            storePath="/account-head/store"
            updatePath="/account-head/update"
            backTo="/accounts/account-heads"
        >
            {({ form, setForm }) => <input className={inputClass} placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} />}
        </AccountsSimpleFormPage>
    );
}

