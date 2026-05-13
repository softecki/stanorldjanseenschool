import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../api/xhrJson';

export function Shell({ Layout, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-6 p-6">{children}</div>
        </Layout>
    );
}

export function paginateRows(r) {
    const p = r.data?.data;
    if (Array.isArray(p?.data)) return p.data;
    if (Array.isArray(p)) return p;
    return [];
}

/** Laravel paginator JSON: `{ data: { data: [...], current_page, last_page, total } }` */
export function paginateState(r) {
    const p = r.data?.data;
    if (!p || typeof p !== 'object') {
        return { rows: [], page: 1, lastPage: 1, total: 0, from: null, to: null };
    }
    if (Array.isArray(p.data)) {
        return {
            rows: p.data,
            page: p.current_page ?? 1,
            lastPage: p.last_page ?? 1,
            total: p.total ?? p.data.length,
            from: p.from ?? null,
            to: p.to ?? null,
        };
    }
    if (Array.isArray(p)) {
        return { rows: p, page: 1, lastPage: 1, total: p.length, from: p.length ? 1 : null, to: p.length };
    }
    return { rows: [], page: 1, lastPage: 1, total: 0, from: null, to: null };
}

