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

export function PaymentMethodFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit payment method' : 'Create payment method'}
            edit={edit}
            id={id}
            loadPath="/payment-methods"
            storePath="/payment-methods/store"
            updatePath="/payment-methods/update"
            backTo="/accounts/payment-methods"
        >
            {({ form, setForm }) => <input className={inputClass} placeholder="Name" value={form.name || ''} onChange={(e) => setForm({ ...form, name: e.target.value })} />}
        </AccountsSimpleFormPage>
    );
}

