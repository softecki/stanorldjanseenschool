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

export function ChartOfAccountsFormPage({ Layout, edit = false }) {
    const { id } = useParams();
    return (
        <AccountsSimpleFormPage
            Layout={Layout}
            title={edit ? 'Edit chart of account' : 'Create chart of account'}
            edit={edit}
            id={id}
            loadPath="/chart-of-accounts"
            storePath="/chart-of-accounts/store"
            updatePath="/chart-of-accounts/update"
            backTo="/accounts/chart-of-accounts"
        >
            {chartFormFields}
        </AccountsSimpleFormPage>
    );
}

