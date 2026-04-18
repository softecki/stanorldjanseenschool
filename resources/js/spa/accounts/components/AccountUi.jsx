import React from 'react';
import {
    UiHeadRow,
    UiPageLoader,
    UiTable,
    UiTBody,
    UiTD,
    UiTH,
    UiTHead,
    UiTR,
    UiTableWrap,
} from '../../ui/UiKit';

/** Page chrome for accounting lists/forms (Tailwind only). */
export function AccountPageHeader({ title, actions }) {
    return (
        <div className="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1 className="text-2xl font-semibold text-gray-800">{title}</h1>
            {actions ? <div className="flex flex-wrap gap-2">{actions}</div> : null}
        </div>
    );
}

export function AccountCard({ children, className = '' }) {
    return <div className={`rounded-xl border border-gray-200 bg-white shadow-sm ${className}`}>{children}</div>;
}

export function AccountTable({ children }) {
    return (
        <UiTableWrap>
            <UiTable>{children}</UiTable>
        </UiTableWrap>
    );
}

export function AccountTHead({ children }) {
    return <UiTHead>{children}</UiTHead>;
}

export function AccountTR({ children, className = '' }) {
    return <UiTR className={className}>{children}</UiTR>;
}

export function AccountHeadRow({ children, className = '' }) {
    return <UiHeadRow className={className}>{children}</UiHeadRow>;
}

export function AccountTH({ children, className = '' }) {
    return <UiTH className={className}>{children}</UiTH>;
}

export function AccountTD({ children, className = '' }) {
    return <UiTD className={className}>{children}</UiTD>;
}

export function AccountTBody({ children }) {
    return <UiTBody>{children}</UiTBody>;
}

export function AccountEmptyState({ message }) {
    return (
        <div className="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-6 py-10 text-center text-sm text-gray-500">
            {message || 'No records yet.'}
        </div>
    );
}

export function AccountFullPageLoader({ text = 'Loading…' }) {
    return <UiPageLoader text={text} />;
}
