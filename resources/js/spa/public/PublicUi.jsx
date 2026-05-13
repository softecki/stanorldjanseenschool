import React from 'react';
import { Link } from 'react-router-dom';

/** @param {...string} parts */
export function cn(...parts) {
    return parts.filter(Boolean).join(' ');
}

/** Section heading with optional eyebrow + actions */
export function SectionHeader({ eyebrow, title, description, actions, className }) {
    return (
        <div className={cn('flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between', className)}>
            <div className="max-w-3xl">
                {eyebrow ? (
                    <p className="text-xs font-bold uppercase tracking-[0.2em] text-blue-700/90">{eyebrow}</p>
                ) : null}
                {title ? <h2 className="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{title}</h2> : null}
                {description ? <p className="mt-2 text-base text-slate-600">{description}</p> : null}
            </div>
            {actions ? <div className="flex shrink-0 flex-wrap gap-2">{actions}</div> : null}
        </div>
    );
}

export function PublicCard({ className, children, padding = 'p-6' }) {
    return (
        <div
            className={cn(
                'rounded-2xl border border-slate-200/90 bg-white shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md',
                padding,
                className,
            )}
        >
            {children}
        </div>
    );
}

export function StatCard({ value, label, hint, accent = 'blue' }) {
    const accents = {
        blue: 'from-blue-600 to-indigo-700',
        emerald: 'from-emerald-500 to-teal-700',
        amber: 'from-amber-400 to-orange-600',
        violet: 'from-violet-500 to-purple-700',
    };
    const grad = accents[accent] || accents.blue;
    return (
        <div
            className={cn(
                'relative overflow-hidden rounded-2xl bg-gradient-to-br p-6 text-white shadow-lg ring-1 ring-white/10',
                grad,
            )}
        >
            <p className="text-3xl font-bold tabular-nums tracking-tight">{value ?? '—'}</p>
            <p className="mt-1 text-sm font-semibold text-white/90">{label}</p>
            {hint ? <p className="mt-2 text-xs text-white/75">{hint}</p> : null}
            <div className="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-2xl" aria-hidden />
        </div>
    );
}

export function FeatureGrid({ items }) {
    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {(items || []).map((item, i) => (
                <PublicCard key={item.title || i} padding="p-6">
                    {item.icon ? (
                        <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-700">{item.icon}</div>
                    ) : null}
                    <h3 className="mt-3 text-lg font-semibold text-slate-900">{item.title}</h3>
                    <p className="mt-2 text-sm leading-relaxed text-slate-600">{item.body}</p>
                </PublicCard>
            ))}
        </div>
    );
}

export function CTABand({ title, subtitle, primary, secondary }) {
    return (
        <section className="relative overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-r from-blue-700 via-indigo-700 to-violet-800 px-8 py-12 text-white shadow-xl ring-1 ring-white/10">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.12),transparent_45%)]" />
            <div className="relative mx-auto max-w-4xl text-center">
                {title ? <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">{title}</h2> : null}
                {subtitle ? <p className="mx-auto mt-3 max-w-2xl text-base text-blue-100">{subtitle}</p> : null}
                <div className="mt-8 flex flex-wrap justify-center gap-3">{primary}{secondary}</div>
            </div>
        </section>
    );
}

export function TrustStrip({ items }) {
    if (!items?.length) return null;
    return (
        <div className="flex flex-wrap items-center justify-center gap-x-10 gap-y-4 rounded-2xl border border-slate-200 bg-slate-50/80 px-6 py-5">
            {items.map((t, i) => (
                <div key={t.label || i} className="flex items-center gap-2 text-sm text-slate-600">
                    {t.icon ? <span className="text-blue-600">{t.icon}</span> : null}
                    <span className="font-medium text-slate-800">{t.label}</span>
                </div>
            ))}
        </div>
    );
}

export function Timeline({ items }) {
    return (
        <ol className="relative space-y-6 border-l-2 border-blue-100 pl-8">
            {(items || []).map((it, i) => (
                <li key={it.title || i} className="relative">
                    <span className="absolute -left-[11px] top-1.5 h-5 w-5 rounded-full border-4 border-white bg-blue-600 shadow" />
                    <p className="text-sm font-bold text-slate-900">{it.title}</p>
                    <p className="mt-1 text-sm text-slate-600">{it.body}</p>
                </li>
            ))}
        </ol>
    );
}

export function ContentRail({ main, aside, className }) {
    return (
        <div className={cn('grid gap-10 lg:grid-cols-3', className)}>
            <div className="min-w-0 lg:col-span-2">{main}</div>
            <aside className="min-w-0 space-y-6">{aside}</aside>
        </div>
    );
}

export function PublicLoading({ label = 'Loading…' }) {
    return (
        <div className="flex flex-col items-center justify-center gap-3 py-20 text-slate-500" role="status" aria-live="polite">
            <span className="h-10 w-10 animate-spin rounded-full border-2 border-blue-200 border-t-blue-600" />
            <span className="text-sm font-medium">{label}</span>
        </div>
    );
}

export function PublicError({ message }) {
    if (!message) return null;
    return (
        <div className="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            {message}
        </div>
    );
}

export function PublicEmpty({ title = 'Nothing here yet', hint }) {
    return (
        <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-12 text-center">
            <p className="font-semibold text-slate-800">{title}</p>
            {hint ? <p className="mt-2 text-sm text-slate-600">{hint}</p> : null}
        </div>
    );
}

/** Primary / secondary link buttons styled for public pages */
export function PrimaryButtonLink({ to, children, className }) {
    return (
        <Link
            to={to}
            className={cn(
                'inline-flex items-center justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-blue-900 shadow hover:bg-blue-50',
                className,
            )}
        >
            {children}
        </Link>
    );
}

export function GhostButtonLink({ to, children, className }) {
    return (
        <Link
            to={to}
            className={cn(
                'inline-flex items-center justify-center rounded-xl border border-white/40 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10',
                className,
            )}
        >
            {children}
        </Link>
    );
}
