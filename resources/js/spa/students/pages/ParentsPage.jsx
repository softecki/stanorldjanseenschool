import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { NavLink } from 'react-router-dom';

import { xhrJson } from '../../api/xhrJson';
import { AdminLayout } from '../../layout/AdminLayout';
import {
    UiActionGroup,
    UiButton,
    UiButtonLink,
    UiHeadRow,
    UiPageLoader,
    UiPager,
    UiTable,
    UiTableEmptyRow,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../ui/UiKit';

function rowDisplayName(p) {
    return p.guardian_name || p.name || `${p.first_name || ''} ${p.last_name || ''}`.trim() || '—';
}

function rowPhone(p) {
    return p.guardian_mobile || p.phone || p.mobile || p.mobile_no || '—';
}

function rowEmail(p) {
    return p.guardian_email || p.email || '—';
}

export function ParentsPage() {
    const [rows, setRows] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 15, total: 0 });
    const [meta, setMeta] = useState({});
    const [query, setQuery] = useState('');
    const [debouncedQuery, setDebouncedQuery] = useState('');
    const [page, setPage] = useState(1);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(false);
    const [refreshToken, setRefreshToken] = useState(0);

    const hydrate = useCallback((payload) => {
        const paged = payload?.data;
        const list = Array.isArray(paged?.data) ? paged.data : Array.isArray(payload?.data) ? payload.data : [];
        setRows(list);
        setMeta(payload?.meta || {});
        setPagination({
            current_page: paged?.current_page ?? 1,
            last_page: paged?.last_page ?? 1,
            per_page: paged?.per_page ?? 15,
            total: paged?.total ?? list.length,
        });
    }, []);

    // Live search: runs as the user types (name, email, or any parent phone field on the server).
    useEffect(() => {
        const t = setTimeout(() => setDebouncedQuery(query.trim()), 300);
        return () => clearTimeout(t);
    }, [query]);

    useEffect(() => {
        setPage(1);
    }, [debouncedQuery]);

    useEffect(() => {
        setLoading(true);
        setErr('');
        const filtered = debouncedQuery.length > 0;
        const req = filtered
            ? axios.post('/parent/search', { keyword: debouncedQuery, page }, { headers: xhrJson })
            : axios.get('/parent', { headers: xhrJson, params: { page } });
        req
            .then((r) => hydrate(r.data || {}))
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load parents.'))
            .finally(() => setLoading(false));
    }, [debouncedQuery, page, refreshToken, hydrate]);

    const remove = async (id) => {
        if (!window.confirm('Delete this parent? This also removes their linked login user.')) return;
        try {
            await axios.delete(`/parent/delete/${id}`, { headers: xhrJson });
            setRefreshToken((n) => n + 1);
        } catch (ex) {
            setErr(ex.response?.data?.message || ex.response?.data?.[0] || 'Failed to delete parent.');
        }
    };

    const currentPage = pagination.current_page || 1;
    const perPage = pagination.per_page || 15;

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="mb-4 grid w-full grid-cols-2 gap-2 rounded-xl border border-gray-200 bg-white p-2">
                    <NavLink
                        to="/students"
                        className={({ isActive }) =>
                            `w-full rounded-lg px-4 py-2 text-center text-sm font-semibold transition ${
                                isActive
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'border border-gray-200 bg-gray-50 text-gray-700 hover:bg-white'
                            }`
                        }
                    >
                        Students
                    </NavLink>
                    <NavLink
                        to="/parents"
                        end
                        className={({ isActive }) =>
                            `w-full rounded-lg px-4 py-2 text-center text-sm font-semibold transition ${
                                isActive
                                    ? 'bg-blue-600 text-white shadow-sm'
                                    : 'border border-gray-200 bg-gray-50 text-gray-700 hover:bg-white'
                            }`
                        }
                    >
                        Guardian
                    </NavLink>
                </div>
                <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h1 className="text-2xl font-semibold text-gray-800">{meta.title || 'Parents'}</h1>
                        <div className="flex flex-wrap items-center gap-2">
                            <label className="sr-only" htmlFor="parents-search">
                                Search parents by name, email, or phone. Results update as you type.
                            </label>
                            <input
                                id="parents-search"
                                type="search"
                                autoComplete="off"
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        setDebouncedQuery(e.currentTarget.value.trim());
                                    }
                                    if (e.key === 'Escape') {
                                        e.preventDefault();
                                        setQuery('');
                                        setDebouncedQuery('');
                                        setPage(1);
                                    }
                                }}
                                title="Searches as you type: names, email, guardian / father / mother phone, and linked account phone"
                                placeholder="Name, email, or phone (guardian, father, mother)…"
                                className="min-w-[200px] flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm lg:max-w-md"
                            />
                            {query ? (
                                <UiButton
                                    type="button"
                                    variant="secondary"
                                    onClick={() => {
                                        setQuery('');
                                        setDebouncedQuery('');
                                        setPage(1);
                                    }}
                                >
                                    Clear
                                </UiButton>
                            ) : null}
                            <UiButtonLink to="/parents/create">Create</UiButtonLink>
                        </div>
                    </div>
                </div>
                {err ? <p className="mb-3 text-sm text-red-600">{err}</p> : null}
                {loading ? <UiPageLoader text="Loading parents…" /> : null}
                {!loading ? (
                    <>
                        <UiTableWrap>
                            <UiTable>
                                <UiTHead>
                                    <UiHeadRow>
                                        <UiTH className="w-12">#</UiTH>
                                        <UiTH>Guardian</UiTH>
                                        <UiTH>Phone</UiTH>
                                        <UiTH>Email</UiTH>
                                        <UiTH className="text-right">Actions</UiTH>
                                    </UiHeadRow>
                                </UiTHead>
                                <UiTBody>
                                    {rows.length === 0 ? (
                                        <UiTableEmptyRow colSpan={5} message="No parents found." />
                                    ) : (
                                        rows.map((p, idx) => (
                                            <UiTR key={p.id}>
                                                <UiTD className="whitespace-nowrap text-gray-500">
                                                    {(currentPage - 1) * perPage + idx + 1}
                                                </UiTD>
                                                <UiTD className="font-medium text-gray-900">{rowDisplayName(p)}</UiTD>
                                                <UiTD>{rowPhone(p)}</UiTD>
                                                <UiTD>{rowEmail(p)}</UiTD>
                                                <UiTD className="text-right">
                                                    <UiActionGroup
                                                        viewTo={`/parents/${p.id}`}
                                                        editTo={`/parents/${p.id}/edit`}
                                                        onDelete={() => remove(p.id)}
                                                    />
                                                </UiTD>
                                            </UiTR>
                                        ))
                                    )}
                                </UiTBody>
                            </UiTable>
                        </UiTableWrap>
                        <UiPager
                            className="mt-4"
                            page={currentPage}
                            lastPage={pagination.last_page || 1}
                            onPrev={() => setPage((x) => Math.max(1, x - 1))}
                            onNext={() => setPage((x) => Math.min(pagination.last_page || 1, x + 1))}
                        />
                    </>
                ) : null}
            </div>
        </AdminLayout>
    );
}
