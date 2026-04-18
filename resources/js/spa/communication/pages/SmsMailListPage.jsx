import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';
import { UiButtonLink, UiHeadRow, UiTable, UiTableEmptyRow, UiTableWrap, UiTBody, UiTD, UiTH, UiTHead, UiTR } from '../../ui/UiKit';

export function SmsMailListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [err, setErr] = useState('');

    useEffect(() => {
        axios
            .get('/communication/smsmail', { headers: xhrJson })
            .then((r) => {
                setRows(paginateRows(r));
                setMeta(r.data?.meta ?? {});
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'));
    }, []);

    return (
        <Shell Layout={Layout}>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'SMS / Mail log'}</h1>
                    <p className="text-sm text-gray-500">Tracking rows from sms_tracking.</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <UiButtonLink variant="secondary" to="/communication/smsmail/campaign">
                        Campaign
                    </UiButtonLink>
                    <UiButtonLink to="/communication/smsmail/create">Create send</UiButtonLink>
                </div>
            </div>
            {err ? <p className="text-sm text-red-600">{err}</p> : null}
            <UiTableWrap>
                <UiTable className="text-xs">
                    <UiTHead>
                        <UiHeadRow>
                            <UiTH>To</UiTH>
                            <UiTH>Status</UiTH>
                            <UiTH>Message</UiTH>
                        </UiHeadRow>
                    </UiTHead>
                    <UiTBody>
                        {rows.length ? (
                            rows.map((row) => (
                                <UiTR key={row.id}>
                                    <UiTD className="font-mono">{row.to}</UiTD>
                                    <UiTD>{row.status_name}</UiTD>
                                    <UiTD className="max-w-xs truncate">{row.sms}</UiTD>
                                </UiTR>
                            ))
                        ) : (
                            <UiTableEmptyRow colSpan={3} message="No rows yet." />
                        )}
                    </UiTBody>
                </UiTable>
            </UiTableWrap>
        </Shell>
    );
}
