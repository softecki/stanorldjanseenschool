import React, { useEffect, useState } from 'react';
import axios from 'axios';
import {
    AccountCard,
    AccountEmptyState,
    AccountFullPageLoader,
    AccountHeadRow,
    AccountPageHeader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from './components/AccountUi';
import { IconPlus, UiButtonLink, UiIconLinkEdit } from '../ui/UiKit';

import { xhrJson } from '../api/xhrJson';

export function AccountListPage({ Layout, title, endpoint, createTo, editBase }) {
    const [rows, setRows] = useState([]);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);
    useEffect(() => {
        setLoading(true);
        setErr('');
        axios
            .get(endpoint, { headers: xhrJson })
            .then((r) => setRows(r.data?.data?.data || r.data?.data || []))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load data.'))
            .finally(() => setLoading(false));
    }, [endpoint]);

    return (
        <Layout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <AccountPageHeader
                        title={title}
                        actions={
                            <UiButtonLink to={createTo} variant="primary" leftIcon={<IconPlus />}>
                                Create
                            </UiButtonLink>
                        }
                    />
                </div>
                {err ? <p className="mb-4 text-sm text-red-600">{err}</p> : null}
                {loading ? <AccountFullPageLoader text={`Loading ${title.toLowerCase()}…`} /> : null}
                {!loading ? <AccountCard>
                    {!rows.length ? (
                        <AccountEmptyState />
                    ) : (
                        <AccountTable>
                            <AccountTHead>
                                <AccountHeadRow>
                                    <AccountTH>Name</AccountTH>
                                    <AccountTH className="text-right">Actions</AccountTH>
                                </AccountHeadRow>
                            </AccountTHead>
                            <tbody className="divide-y divide-gray-100 bg-white">
                                {rows.map((row) => (
                                    <AccountTR key={row.id}>
                                        <AccountTD>{row.name || row.title || `#${row.id}`}</AccountTD>
                                        <AccountTD className="text-right">
                                            <div className="flex justify-end">
                                                <UiIconLinkEdit to={`${editBase}/${row.id}/edit`} />
                                            </div>
                                        </AccountTD>
                                    </AccountTR>
                                ))}
                            </tbody>
                        </AccountTable>
                    )}
                </AccountCard> : null}
            </div>
        </Layout>
    );
}

