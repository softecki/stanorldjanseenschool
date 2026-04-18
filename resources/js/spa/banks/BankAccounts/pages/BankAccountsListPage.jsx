import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BankAccountsModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import {
    UiButtonLink,
    UiHeadRow,
    UiIconLinkEdit,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

export function BankAccountsListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    useEffect(() => {
        axios
            .get('/banksAccounts', { headers: xhrJson })
            .then((r) => {
                setRows(r.data?.data || []);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load bank accounts.'));
    }, []);

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Bank accounts'}</h1>
                    <p className="text-sm text-gray-500">Manage institution bank accounts.</p>
                </div>
                <UiButtonLink to="/banks-accounts/create">Add account</UiButtonLink>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <UiTableWrap>
                <UiTable>
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>Bank</UiTH>
                            <UiTH>Account name</UiTH>
                            <UiTH>Number</UiTH>
                            <UiTH>Status</UiTH>
                            <UiTH className="text-right">Actions</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD>{row.bank_name}</UiTD>
                                    <UiTD>{row.account_name}</UiTD>
                                    <UiTD className="font-mono text-xs">{row.account_number}</UiTD>
                                    <UiTD>{row.status ? 'Active' : 'Inactive'}</UiTD>
                                    <UiTD className="text-right">
                                        <div className="flex justify-end">
                                            <UiIconLinkEdit to={`/banks-accounts/${row.id}/edit`} />
                                        </div>
                                    </UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={5} message="No bank accounts yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}

