import React, { useEffect, useState } from 'react';
import axios from 'axios';

import { xhrJson } from '../../../api/xhrJson';
import {
    UiActionGroup,
    UiButton,
    UiButtonLink,
    UiHeadRow,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../../../ui/UiKit';

export function GmeetListPage({ Layout }) {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({});
    const [filters, setFilters] = useState({ class: '', section: '', subject: '' });
    const [err, setErr] = useState('');

    const load = async () => {
        try {
            const r = await axios.get('/liveclass/gmeet', { headers: xhrJson });
            setRows(r.data?.data?.data || r.data?.data || []);
            setMeta(r.data?.meta || {});
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Failed to load.');
        }
    };

    useEffect(() => {
        load();
    }, []);

    const search = async (e) => {
        e.preventDefault();
        try {
            const r = await axios.post('/liveclass/gmeet/search', filters, { headers: xhrJson });
            setRows(r.data?.data?.data || r.data?.data || []);
            setMeta((m) => ({ ...m, ...(r.data?.meta || {}) }));
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Search failed.');
        }
    };

    const remove = async (id) => {
        if (!window.confirm('Delete this meeting?')) return;
        try {
            await axios.delete(`/liveclass/gmeet/delete/${id}`, { headers: xhrJson });
            load();
        } catch (ex) {
            setErr(ex.response?.data?.message || 'Delete failed.');
        }
    };

    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-4 p-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-semibold text-gray-900">{meta.title || 'Gmeet'}</h1>
                    <UiButtonLink to="/liveclass/gmeet/create">Create</UiButtonLink>
                </div>
                <form onSubmit={search} className="grid gap-2 rounded-xl border border-gray-200 bg-white p-4 md:grid-cols-4">
                    <select
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        value={filters.class}
                        onChange={(e) => setFilters({ ...filters, class: e.target.value })}
                    >
                        <option value="">Class</option>
                        {(meta.classes || []).map((c) => {
                            const v = c.class?.id || c.id;
                            const n = c.class?.name || c.name;
                            return (
                                <option key={v} value={v}>
                                    {n}
                                </option>
                            );
                        })}
                    </select>
                    <input
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        placeholder="Section id"
                        value={filters.section}
                        onChange={(e) => setFilters({ ...filters, section: e.target.value })}
                    />
                    <input
                        className="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        placeholder="Subject id"
                        value={filters.subject}
                        onChange={(e) => setFilters({ ...filters, subject: e.target.value })}
                    />
                    <UiButton type="submit" variant="secondary" className="bg-gray-800 text-white hover:bg-gray-900">
                        Search
                    </UiButton>
                </form>
                {err ? <p className="text-sm text-red-600">{err}</p> : null}
                <UiTableWrap>
                    <UiTable>
                        <UiTHead>
                            <UiHeadRow>
                                <UiTH>Title</UiTH>
                                <UiTH>Class/Section</UiTH>
                                <UiTH>Time</UiTH>
                                <UiTH className="text-right">Actions</UiTH>
                            </UiHeadRow>
                        </UiTHead>
                        <UiTBody>
                            {rows.map((r) => (
                                <UiTR key={r.id}>
                                    <UiTD>
                                        <a className="font-medium text-blue-700 hover:underline" href={r.gmeet_link} target="_blank" rel="noreferrer">
                                            {r.title || `Meeting #${r.id}`}
                                        </a>
                                    </UiTD>
                                    <UiTD>
                                        {r.classes_id}/{r.section_id}
                                    </UiTD>
                                    <UiTD>
                                        {r.start} - {r.end}
                                    </UiTD>
                                    <UiTD className="text-right">
                                        <UiActionGroup editTo={`/liveclass/gmeet/${r.id}/edit`} onDelete={() => remove(r.id)} />
                                    </UiTD>
                                </UiTR>
                            ))}
                        </UiTBody>
                    </UiTable>
                </UiTableWrap>
            </div>
        </Layout>
    );
}
