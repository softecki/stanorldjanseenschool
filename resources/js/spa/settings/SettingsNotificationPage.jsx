import React, { useCallback, useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';

import { xhrJson } from '../api/xhrJson';
import { AdminLayout } from '../layout/AdminLayout';
import {
    UiButton,
    UiHeadRow,
    UiInlineLoader,
    UiPageLoader,
    UiTable,
    UiTableWrap,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
} from '../ui/UiKit';

function formatErr(ex) {
    const d = ex.response?.data;
    if (!d) return ex.message || 'Request failed.';
    if (typeof d.message === 'string') return d.message;
    return 'Request failed.';
}

function humanEvent(event) {
    if (!event) return '';
    return String(event).replace(/_/g, ' ');
}

/** Notification channels & templates (legacy AJAX behaviour, SPA UI). */
export function SettingsNotificationPage() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({ title: 'Notification setting', can_update: true });
    const [loading, setLoading] = useState(true);
    const [err, setErr] = useState('');
    const [info, setInfo] = useState('');
    const [busy, setBusy] = useState(null);
    const [modalOpen, setModalOpen] = useState(false);
    const [modalLoading, setModalLoading] = useState(false);
    const [modalSaving, setModalSaving] = useState(false);
    const [modal, setModal] = useState({
        id: null,
        key: '',
        shortcode: '',
        subject: '',
        email_body: '',
        sms_body: '',
        app_body: '',
        web_body: '',
    });

    const load = useCallback(() => {
        setLoading(true);
        setErr('');
        return axios
            .get('/notification-settings', { headers: xhrJson })
            .then((r) => {
                const list = r.data?.data;
                setRows(Array.isArray(list) ? list : []);
                setMeta({
                    title: r.data?.meta?.title || 'Notification setting',
                    can_update: r.data?.meta?.can_update !== false,
                });
            })
            .catch((ex) => setErr(formatErr(ex)))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        load();
    }, [load]);

    const postToggle = async (payload) => {
        if (!meta.can_update) return;
        const sig = `${payload.type}-${payload.id}-${payload.host || payload.reciever || ''}`;
        setBusy(sig);
        setErr('');
        try {
            await axios.post('/notification-settings', payload, {
                headers: { ...xhrJson, 'Content-Type': 'application/json' },
            });
            setInfo('Saved.');
            setTimeout(() => setInfo(''), 2500);
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
            await load();
        } finally {
            setBusy(null);
        }
    };

    const onDestinationChange = (row, hostKey, checked) => {
        postToggle({
            id: row.id,
            host: hostKey,
            status: checked ? 1 : 0,
            type: 'destination',
        });
    };

    const onRecipientToggle = (row, recKey, checked) => {
        postToggle({
            id: row.id,
            reciever: recKey,
            status: checked ? 1 : 0,
            type: 'recipient-status',
        });
    };

    const openModal = async (row, key) => {
        if (!meta.can_update) return;
        setModalOpen(true);
        setModalLoading(true);
        setErr('');
        try {
            const { data } = await axios.get(`/notification_event_modal/${row.id}/${encodeURIComponent(key)}`, { headers: xhrJson });
            const d = data?.data || {};
            setModal({
                id: d.id,
                key: d.key,
                shortcode: d.shortcode ?? '',
                subject: d.subject ?? '',
                email_body: d.email_body ?? '',
                sms_body: d.sms_body ?? '',
                app_body: d.app_body ?? '',
                web_body: d.web_body ?? '',
            });
        } catch (ex) {
            setErr(formatErr(ex));
            setModalOpen(false);
        } finally {
            setModalLoading(false);
        }
    };

    const saveModal = async (e) => {
        e.preventDefault();
        if (!meta.can_update) return;
        setModalSaving(true);
        setErr('');
        try {
            await axios.post(
                '/notification-settings',
                {
                    id: modal.id,
                    key: modal.key,
                    subject: modal.subject,
                    email_body: modal.email_body,
                    sms_body: modal.sms_body,
                    app_body: modal.app_body,
                    web_body: modal.web_body,
                    type: 'recipient',
                },
                { headers: { ...xhrJson, 'Content-Type': 'application/json' } },
            );
            setInfo('Template updated.');
            setTimeout(() => setInfo(''), 2500);
            setModalOpen(false);
            await load();
        } catch (ex) {
            setErr(formatErr(ex));
        } finally {
            setModalSaving(false);
        }
    };

    const areaClass = 'rounded-xl border border-slate-200 bg-slate-50/60 p-3 text-sm';

    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl space-y-6 p-6">
                <div>
                    <Link to="/settings" className="text-xs font-medium text-blue-700 hover:underline">
                        ← Settings
                    </Link>
                    <h1 className="mt-2 text-2xl font-semibold text-slate-900">{meta.title}</h1>
                    <p className="mt-1 max-w-3xl text-sm text-slate-600">
                        Choose where each notification is sent (Email, SMS, etc.) and which roles receive it. Use the edit control to change subjects and
                        message bodies per role.
                    </p>
                </div>

                {err ? (
                    <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                        {err}
                    </div>
                ) : null}
                {info ? (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
                        {info}
                    </div>
                ) : null}

                {loading ? (
                    <UiPageLoader text="Loading notification rules…" />
                ) : (
                    <UiTableWrap className="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                        <UiTable className="text-sm">
                            <UiTHead className="bg-slate-50">
                                <UiHeadRow>
                                    <UiTH>Event</UiTH>
                                    <UiTH>Destinations</UiTH>
                                    <UiTH>Recipients</UiTH>
                                </UiHeadRow>
                            </UiTHead>
                            <UiTBody>
                                {rows.map((row) => (
                                    <UiTR key={row.id}>
                                        <UiTD className="align-top font-medium text-slate-900">{humanEvent(row.event)}</UiTD>
                                        <UiTD className="align-top">
                                            <div className="flex flex-col gap-2">
                                                {row.host && typeof row.host === 'object'
                                                    ? Object.entries(row.host).map(([key, on]) => {
                                                          const sig = `destination-${row.id}-${key}`;
                                                          return (
                                                              <label key={sig} className="flex cursor-pointer items-center gap-2">
                                                                  <input
                                                                      type="checkbox"
                                                                      className="h-4 w-4 rounded border-slate-300"
                                                                      checked={Number(on) === 1}
                                                                      disabled={!meta.can_update || busy === sig}
                                                                      onChange={(e) => onDestinationChange(row, key, e.target.checked)}
                                                                  />
                                                                  <span className="capitalize text-slate-800">{key}</span>
                                                              </label>
                                                          );
                                                      })
                                                    : null}
                                            </div>
                                        </UiTD>
                                        <UiTD className="align-top">
                                            <div className="flex flex-wrap gap-3">
                                                {row.reciever && typeof row.reciever === 'object'
                                                    ? Object.entries(row.reciever).map(([key, on]) => {
                                                          const sig = `recipient-status-${row.id}-${key}`;
                                                          return (
                                                              <div key={sig} className={areaClass}>
                                                                  <div className="flex items-center justify-between gap-3">
                                                                      <label className="flex cursor-pointer items-center gap-2">
                                                                          <input
                                                                              type="checkbox"
                                                                              className="h-4 w-4 rounded border-slate-300"
                                                                              checked={Number(on) === 1}
                                                                              disabled={!meta.can_update || busy === sig}
                                                                              onChange={(e) => onRecipientToggle(row, key, e.target.checked)}
                                                                          />
                                                                          <span className="font-semibold text-slate-900">{key}</span>
                                                                      </label>
                                                                      <UiButton
                                                                          type="button"
                                                                          variant="outline"
                                                                          className="h-8 px-2 text-xs"
                                                                          disabled={!meta.can_update}
                                                                          onClick={() => openModal(row, key)}
                                                                      >
                                                                          Edit template
                                                                      </UiButton>
                                                                  </div>
                                                                  <p className="mt-2 whitespace-pre-wrap text-xs text-slate-600">
                                                                      {row.shortcode && row.shortcode[key] != null ? String(row.shortcode[key]) : ''}
                                                                  </p>
                                                              </div>
                                                          );
                                                      })
                                                    : null}
                                            </div>
                                        </UiTD>
                                    </UiTR>
                                ))}
                            </UiTBody>
                        </UiTable>
                    </UiTableWrap>
                )}

                {modalOpen ? (
                    <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center" onClick={() => setModalOpen(false)} role="presentation">
                        <div
                            className="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
                            onClick={(e) => e.stopPropagation()}
                            role="dialog"
                            aria-modal="true"
                        >
                            <div className="mb-4 flex items-center justify-between gap-2">
                                <h2 className="text-lg font-semibold text-slate-900">Edit template</h2>
                                <button type="button" className="rounded-lg px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" onClick={() => setModalOpen(false)}>
                                    Close
                                </button>
                            </div>
                            {modalLoading ? (
                                <div className="flex justify-center py-12">
                                    <UiInlineLoader />
                                </div>
                            ) : (
                                <form onSubmit={saveModal} className="space-y-4">
                                    <div>
                                        <p className="text-sm font-medium text-slate-700">Shortcodes</p>
                                        <p className="mt-1 whitespace-pre-wrap rounded-lg bg-indigo-50 px-3 py-2 text-sm text-indigo-950">{modal.shortcode}</p>
                                    </div>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Subject
                                        <input className="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" value={modal.subject} onChange={(e) => setModal((m) => ({ ...m, subject: e.target.value }))} />
                                    </label>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Email body
                                        <textarea className="mt-1 min-h-[120px] w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" value={modal.email_body} onChange={(e) => setModal((m) => ({ ...m, email_body: e.target.value }))} />
                                    </label>
                                    <label className="block text-sm font-medium text-slate-700">
                                        SMS body
                                        <textarea className="mt-1 min-h-[80px] w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" value={modal.sms_body} onChange={(e) => setModal((m) => ({ ...m, sms_body: e.target.value }))} />
                                    </label>
                                    <label className="block text-sm font-medium text-slate-700">
                                        App message
                                        <textarea className="mt-1 min-h-[80px] w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" value={modal.app_body} onChange={(e) => setModal((m) => ({ ...m, app_body: e.target.value }))} />
                                    </label>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Web message
                                        <textarea className="mt-1 min-h-[80px] w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" value={modal.web_body} onChange={(e) => setModal((m) => ({ ...m, web_body: e.target.value }))} />
                                    </label>
                                    <div className="flex justify-end gap-2 border-t border-slate-100 pt-4">
                                        <UiButton type="button" variant="secondary" onClick={() => setModalOpen(false)}>
                                            Cancel
                                        </UiButton>
                                        <UiButton type="submit" disabled={modalSaving || !meta.can_update}>
                                            {modalSaving ? (
                                                <>
                                                    <UiInlineLoader /> Saving…
                                                </>
                                            ) : (
                                                'Save'
                                            )}
                                        </UiButton>
                                    </div>
                                </form>
                            )}
                        </div>
                    </div>
                ) : null}
            </div>
        </AdminLayout>
    );
}
