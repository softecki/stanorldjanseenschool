/**
 * Shared SPA UI: tables, page loader, buttons, icon-only row actions.
 * Import from `../ui/UiKit` or `../ui` — keep new screens consistent with these primitives.
 */
import React from 'react';
import { Link } from 'react-router-dom';

/** Canonical table shell — use everywhere lists are shown. */
export function UiTableWrap({ children, className = '' }) {
    return <div className={`overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm ${className}`}>{children}</div>;
}

export function UiTable({ children, className = '' }) {
    return <table className={`min-w-full divide-y divide-gray-200 text-sm ${className}`}>{children}</table>;
}

export function UiTHead({ children, className = '' }) {
    return (
        <thead className={`bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 ${className}`}>{children}</thead>
    );
}

export function UiTBody({ children, className = '' }) {
    return <tbody className={`divide-y divide-gray-100 bg-white ${className}`}>{children}</tbody>;
}

/** Body rows — includes hover highlight */
export function UiTR({ children, className = '' }) {
    return <tr className={`border-b border-gray-100 transition hover:bg-blue-50/30 ${className}`}>{children}</tr>;
}

/** Header row — no hover */
export function UiHeadRow({ children, className = '' }) {
    return <tr className={`border-b border-gray-200 ${className}`}>{children}</tr>;
}

export function UiTH({ children, className = '' }) {
    return <th className={`px-4 py-3 ${className}`}>{children}</th>;
}

export function UiTD({ children, className = '' }) {
    return <td className={`px-4 py-3 text-gray-800 ${className}`}>{children}</td>;
}

export function UiTableEmptyRow({ colSpan, message = 'No records.' }) {
    return (
        <tr>
            <td colSpan={colSpan} className="px-4 py-10 text-center text-sm text-gray-500">
                {message}
            </td>
        </tr>
    );
}

/** Single full-page loading state — use for all route-level loading. */
export function UiPageLoader({ text = 'Loading…', className = '' }) {
    return (
        <div
            className={`flex min-h-[45vh] items-center justify-center rounded-xl border border-gray-200 bg-white p-8 shadow-sm ${className}`}
            role="status"
            aria-live="polite"
        >
            <div className="flex flex-col items-center gap-3">
                <div className="relative h-11 w-11" aria-hidden>
                    <span className="absolute inset-0 rounded-full border-[3px] border-blue-100" />
                    <span className="absolute inset-0 animate-spin rounded-full border-[3px] border-transparent border-t-blue-600" />
                </div>
                <p className="text-sm font-medium text-gray-600">{text}</p>
            </div>
        </div>
    );
}

/** Inline spinner for buttons or compact areas */
export function UiInlineLoader({ className = '' }) {
    return (
        <span className={`inline-flex h-5 w-5 items-center justify-center ${className}`} role="status" aria-label="Loading">
            <span className="h-4 w-4 animate-spin rounded-full border-2 border-gray-200 border-t-blue-600" />
        </span>
    );
}

const btnBase = 'inline-flex items-center justify-center gap-2 rounded-lg text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

const variants = {
    primary: `${btnBase} bg-blue-600 px-3.5 py-2 text-white shadow-sm hover:bg-blue-700`,
    secondary: `${btnBase} border border-gray-200 bg-white px-3.5 py-2 text-gray-700 shadow-sm hover:bg-gray-50`,
    ghost: `${btnBase} px-3 py-2 text-gray-700 hover:bg-gray-100`,
    danger: `${btnBase} bg-rose-600 px-3.5 py-2 text-white shadow-sm hover:bg-rose-700`,
    outline: `${btnBase} border border-gray-300 bg-white px-3 py-1.5 text-gray-700 hover:bg-gray-50`,
};

export function UiButton({ variant = 'primary', type = 'button', className = '', leftIcon, children, ...rest }) {
    return (
        <button type={type} className={`${variants[variant] || variants.primary} ${className}`} {...rest}>
            {leftIcon ? <span className="shrink-0">{leftIcon}</span> : null}
            {children}
        </button>
    );
}

/** Same styles as UiButton but renders a React Router Link */
export function UiButtonLink({ variant = 'primary', className = '', leftIcon, children, to, ...rest }) {
    return (
        <Link to={to} className={`${variants[variant] || variants.primary} ${className}`} {...rest}>
            {leftIcon ? <span className="shrink-0">{leftIcon}</span> : null}
            {children}
        </Link>
    );
}

/** Shared class for icon-only actions (links & buttons) */
export const uiIconBtnClass =
    'inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 disabled:opacity-50';

const iconBtn = uiIconBtnClass;

export function IconView({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
    );
}

export function IconEdit({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 20h9" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 3.5a2.1 2.1 0 113 3L7 19l-4 1 1-4 12.5-12.5z" />
        </svg>
    );
}

export function IconTrash({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M3 6h18" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 6V4h8v2" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M19 6l-1 14H6L5 6" />
        </svg>
    );
}

export function IconPlus({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M12 5v14M5 12h14" />
        </svg>
    );
}

/** Collect / payment shortcut */
export function IconBanknote({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3v-1H4v1a3 3 0 003 3z" />
        </svg>
    );
}

export function IconReceipt({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 9V2h12v7" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18h12v4H6z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 14H4a2 2 0 01-2-2v-2a2 2 0 012-2h16a2 2 0 012 2v2a2 2 0 01-2 2h-2" />
        </svg>
    );
}

export function IconX({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    );
}

/** Terms / list-of-items shortcut */
export function IconList({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01" />
        </svg>
    );
}

export function IconGlobe({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <circle cx="12" cy="12" r="10" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M2 12h20M12 2a15 15 0 000 20M12 2a15 15 0 010 20" />
        </svg>
    );
}

/** Homework evaluation / checklist */
export function IconClipboardCheck({ className = 'h-4 w-4' }) {
    return (
        <svg viewBox="0 0 24 24" className={className} fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 5a2 2 0 012-2h2a2 2 0 012 2v0a2 2 0 01-2 2h-2a2 2 0 01-2-2v0z" />
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 14l2 2 4-4" />
        </svg>
    );
}

export function UiIconLinkView({ to, label = 'View' }) {
    return (
        <Link to={to} className={`${iconBtn} text-blue-600 hover:bg-blue-50`} title={label} aria-label={label}>
            <IconView />
        </Link>
    );
}

export function UiIconLinkEdit({ to, label = 'Edit' }) {
    return (
        <Link to={to} className={`${iconBtn} text-amber-600 hover:bg-amber-50`} title={label} aria-label={label}>
            <IconEdit />
        </Link>
    );
}

export function UiIconLinkTranslate({ to, label = 'Translate' }) {
    return (
        <Link to={to} className={`${iconBtn} text-violet-600 hover:bg-violet-50`} title={label} aria-label={label}>
            <IconGlobe />
        </Link>
    );
}

export function UiIconLinkTerms({ to, label = 'Terms' }) {
    return (
        <Link to={to} className={`${iconBtn} text-emerald-700 hover:bg-emerald-50`} title={label} aria-label={label}>
            <IconList />
        </Link>
    );
}

/** Generic icon-only button for row actions (e.g. Evaluate). */
export function UiIconButton({ onClick, disabled, busy, label = 'Action', className = '', children }) {
    return (
        <button
            type="button"
            onClick={onClick}
            disabled={disabled || busy}
            className={`${iconBtn} ${className}`}
            title={label}
            aria-label={label}
        >
            {busy ? (
                <span className="h-4 w-4 animate-spin rounded-full border-2 border-gray-200 border-t-blue-600" aria-hidden />
            ) : (
                children
            )}
        </button>
    );
}

export function UiIconButtonDelete({ onClick, disabled, busy, label = 'Delete' }) {
    return (
        <button
            type="button"
            disabled={disabled || busy}
            onClick={onClick}
            className={`${iconBtn} text-rose-600 hover:bg-rose-50 disabled:opacity-60`}
            title={label}
            aria-label={label}
        >
            {busy ? (
                <span className="h-4 w-4 animate-spin rounded-full border-2 border-rose-200 border-t-rose-600" aria-hidden />
            ) : (
                <IconTrash />
            )}
        </button>
    );
}

/** Row actions: icon-only view / edit / translate / delete (same pattern as fees ActionButtons). */
export function UiActionGroup({ viewTo, editTo, translateTo, onDelete, busy = false, hideDelete = false }) {
    return (
        <div className="flex items-center justify-end gap-2">
            {viewTo ? <UiIconLinkView to={viewTo} /> : null}
            {editTo ? <UiIconLinkEdit to={editTo} /> : null}
            {translateTo ? <UiIconLinkTranslate to={translateTo} /> : null}
            {!hideDelete ? <UiIconButtonDelete onClick={onDelete} disabled={false} busy={busy} /> : null}
        </div>
    );
}

const pagerBtn =
    'rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none';

export function UiPager({ page, lastPage, onPrev, onNext, className = '' }) {
    if (lastPage <= 1) return null;
    return (
        <div className={`flex justify-between text-sm text-gray-600 ${className}`}>
            <span>
                Page {page} / {lastPage}
            </span>
            <div className="flex gap-2">
                <button type="button" disabled={page <= 1} onClick={onPrev} className={pagerBtn} aria-label="Previous page">
                    Prev
                </button>
                <button type="button" disabled={page >= lastPage} onClick={onNext} className={pagerBtn} aria-label="Next page">
                    Next
                </button>
            </div>
        </div>
    );
}
