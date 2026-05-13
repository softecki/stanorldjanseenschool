import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BankAccountsModuleShared';
import { xhrJson } from '../../../api/xhrJson';
import {
    UiActionGroup,
    UiButtonLink,
    UiHeadRow,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

function formatErr(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    if (d.errors && typeof d.errors === 'object') {
        return Object.entries(d.errors)
            .map(([k, v]) => `${k}: ${Array.isArray(v) ? v.join(' ') : v}`)
            .join(' · ');
    }
    return 'Request failed.';
}

export function BankAccountsListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');
    const [deletingId, setDeletingId] = useState(null);

    const load = useCallback(() => {
        setErr('');
        return axios
            .get('/banksAccounts', { headers: xhrJson })
            .then((r) => {
                const list = r.data?.data;
                setRows(Array.isArray(list) ? list : []);
                setMeta(r.data?.meta || {});
            })
            .catch((ex) => setErr(formatErr(ex)));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const remove = async (id) => {
        if (!window.confirm('Delete this bank account?')) return;
        setErr('');
        setDeletingId(id);
        try {
            await axios.delete(`/banksAccounts/delete/${id}`, { headers: xhrJson });
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setDeletingId(null);
        }
    };

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
                                        <UiActionGroup editTo={`/banks-accounts/${row.id}/edit`} onDelete={() => remove(row.id)} busy={deletingId === row.id} />
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
