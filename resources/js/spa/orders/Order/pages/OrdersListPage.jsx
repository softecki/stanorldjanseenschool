import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { EmptyState, confirmDelete } from '../../../shared/UiStates';
import { Shell } from '../../OrdersModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function OrdersListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});

    const load = () => axios.get('/order', { headers: xhrJson }).then((r) => {
        setRows(r.data?.data?.data || []);
        setMeta(r.data?.meta || {});
    });

    useEffect(() => { load(); }, []);

    const remove = async (id) => {
        if (!confirmDelete('order')) return;
        await axios.delete(`/order/delete/${id}`, { headers: xhrJson });
        load();
    };

    return (
        <Shell Layout={Layout}>
            <div className="flex items-center justify-between">
                <h1 className="text-2xl font-bold">{meta.title || 'Orders'}</h1>
                <Link className="rounded bg-blue-600 px-3 py-2 text-sm text-white" to="/orders/create">Create</Link>
            </div>
            <div className="rounded border bg-white p-3 text-sm">
                {rows.map((row) => (
                    <div key={row.id} className="flex items-center justify-between border-b py-2">
                        <span>Order #{row.id}</span>
                        <button className="text-red-700" onClick={() => remove(row.id)}>Delete</button>
                    </div>
                ))}
                {!rows.length ? <EmptyState title="No orders found" hint="Create your first order from the button above." /> : null}
            </div>
        </Shell>
    );
}

