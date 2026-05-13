import React from 'react';
import { NavLink } from 'react-router-dom';
import { IconEdit, IconX } from '../ui/UiKit';

/**
 * Full-width actions for the collect area: legacy update fees + SPA cancelled list.
 */
export function FeesCollectNavTabs({ updateFeesHref = '/students/update-fees' }) {
    return (
        <div className="mb-3 grid w-full grid-cols-2 overflow-hidden rounded-xl border border-slate-200 bg-slate-200/70 p-px shadow-sm sm:mb-4 sm:rounded-2xl">
            <a
                href={updateFeesHref}
                className="flex items-center justify-center gap-2 border-r border-slate-200 bg-white py-3.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-50 sm:py-4"
            >
                <IconEdit className="h-4 w-4 shrink-0 text-slate-600" aria-hidden />
                Update fees
            </a>
            <NavLink
                to="/collections/cancelled"
                className={({ isActive }) =>
                    [
                        'flex items-center justify-center gap-2 py-3.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500 sm:py-4',
                        isActive
                            ? 'bg-indigo-600 text-white shadow-inner'
                            : 'bg-white text-slate-800 hover:bg-slate-50',
                    ].join(' ')
                }
            >
                {({ isActive }) => (
                    <>
                        <IconX
                            className={[
                                'h-4 w-4 shrink-0',
                                isActive ? 'text-white' : 'text-slate-500',
                            ].join(' ')}
                            aria-hidden
                        />
                        Cancelled collect
                    </>
                )}
            </NavLink>
        </div>
    );
}
