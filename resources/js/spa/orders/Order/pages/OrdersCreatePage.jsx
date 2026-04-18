import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { EmptyState, confirmDelete } from '../../../shared/UiStates';
import { Shell } from '../../OrdersModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function OrdersCreatePage({ Layout }) {
    const nav = useNavigate();
    const [products, setProducts] = useState([]);
    const [quantities, setQuantities] = useState({});

    useEffect(() => {
        axios.get('/order/create', { headers: xhrJson }).then((r) => setProducts(r.data?.meta?.products || []));
    }, []);

    const submit = async (e) => {
        e.preventDefault();
        const product_ids = Object.keys(quantities);
        const qty = product_ids.map((id) => Number(quantities[id] || 0));
        await axios.post('/order/store', { product_ids, quantities: qty }, { headers: xhrJson });
        nav('/orders');
    };

    return (
        <Shell Layout={Layout}>
            <h1 className="text-2xl font-bold">Place order</h1>
            <form onSubmit={submit} className="grid gap-3 rounded border bg-white p-4">
                {products.map((p) => (
                    <label key={p.id} className="flex items-center justify-between gap-3 text-sm">
                        <span>{p.name || `Product #${p.id}`}</span>
                        <input
                            type="number"
                            min="0"
                            className="w-28 rounded border px-3 py-2"
                            value={quantities[p.id] ?? ''}
                            onChange={(e) => setQuantities({ ...quantities, [p.id]: e.target.value })}
                        />
                    </label>
                ))}
                <button className="w-fit rounded bg-blue-600 px-4 py-2 text-white">Submit order</button>
            </form>
        </Shell>
    );
}

