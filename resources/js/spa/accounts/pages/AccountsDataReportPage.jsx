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

export function AccountsDataReportPage({ Layout, title, endpoint }) {
    const [payload, setPayload] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        axios
            .get(endpoint, { headers: xhrJson })
            .then((r) => setPayload(r.data ?? null))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'))
            .finally(() => setLoading(false));
    }, [endpoint]);

    return (
        <Layout>
            <AccountsPageShell>
                <AccountsSectionHeader title={title} subtitle="Live data from the API (read-only in this view)." />
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader /> : null}
                {!loading && !err ? (
                    <AccountCard className="p-4">
                        <pre className="max-h-[70vh] overflow-auto rounded-lg bg-gray-50 p-4 font-mono text-xs text-gray-800">{JSON.stringify(payload, null, 2)}</pre>
                    </AccountCard>
                ) : null}
            </AccountsPageShell>
        </Layout>
    );
}

