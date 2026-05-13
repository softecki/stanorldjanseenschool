import React, { useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { xhrJson } from '../api/xhrJson';
import { cn } from './PublicUi';
import { defaultSchoolMeta } from './utils';

const navLink =
    'rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-blue-700';

const navItems = [
    { to: '/', label: 'Home' },
    { to: '/about', label: 'About' },
    { to: '/news', label: 'News' },
    { to: '/events', label: 'Events' },
    { to: '/notices', label: 'Notices' },
    { to: '/result', label: 'Results' },
    { to: '/online-admission', label: 'Admission' },
    { to: '/contact', label: 'Contact' },
];

function brandInitials(name) {
    const parts = String(name || 'School')
        .trim()
        .split(/\s+/)
        .filter(Boolean);
    if (!parts.length) return 'S';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return `${parts[0][0] || ''}${parts[parts.length - 1][0] || ''}`.toUpperCase() || 'S';
}

export function PublicLayout({ title, subtitle, hero, school: schoolProp, children }) {
    const school = useMemo(() => ({ ...defaultSchoolMeta(), ...(schoolProp || {}) }), [schoolProp]);
    const [email, setEmail] = useState('');
    const [subMsg, setSubMsg] = useState('');
    const [subBusy, setSubBusy] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);

    const subscribe = async (e) => {
        e.preventDefault();
        setSubBusy(true);
        setSubMsg('');
        try {
            const { data } = await axios.post('/subscribe', { email }, { headers: xhrJson });
            const msg = Array.isArray(data) ? data[1] || data[0] : data?.message || 'Subscribed.';
            setSubMsg(msg);
            setEmail('');
        } catch (ex) {
            const d = ex.response?.data;
            setSubMsg(Array.isArray(d) ? d[1] || d[0] : d?.message || 'Subscription failed.');
        } finally {
            setSubBusy(false);
        }
    };

    const showHero = Boolean(title || subtitle || hero);

    return (
        <div className="min-h-screen bg-gradient-to-b from-slate-50 via-white to-slate-50 text-slate-900">
            <header className="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur-md">
                <div className="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
                    <Link to="/" className="flex min-w-0 items-center gap-3 text-lg font-bold tracking-tight text-blue-800">
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-sm font-extrabold text-white shadow-md ring-2 ring-blue-500/20">
                            {brandInitials(school.name)}
                        </span>
                        <span className="truncate">{school.name}</span>
                    </Link>

                    <nav className="hidden flex-wrap items-center gap-1 lg:flex">
                        {navItems.map((n) => (
                            <Link key={n.to} className={navLink} to={n.to}>
                                {n.label}
                            </Link>
                        ))}
                        <Link className={`${navLink} ml-1 border border-blue-200 bg-blue-50 text-blue-800`} to="/login">
                            Staff login
                        </Link>
                    </nav>

                    <button
                        type="button"
                        className="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-700 lg:hidden"
                        aria-expanded={mobileOpen}
                        aria-label="Toggle menu"
                        onClick={() => setMobileOpen((o) => !o)}
                    >
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                            {mobileOpen ? (
                                <path strokeLinecap="round" strokeLinejoin="round" d="M6 18 18 6M6 6l12 12" />
                            ) : (
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            )}
                        </svg>
                    </button>
                </div>

                {mobileOpen ? (
                    <div className="border-t border-slate-100 bg-white px-4 py-3 lg:hidden">
                        <div className="flex flex-col gap-1">
                            {navItems.map((n) => (
                                <Link
                                    key={n.to}
                                    className="rounded-lg px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"
                                    to={n.to}
                                    onClick={() => setMobileOpen(false)}
                                >
                                    {n.label}
                                </Link>
                            ))}
                            <Link
                                className="mt-1 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-center text-sm font-semibold text-blue-800"
                                to="/login"
                                onClick={() => setMobileOpen(false)}
                            >
                                Staff login
                            </Link>
                        </div>
                    </div>
                ) : null}
            </header>

            {showHero ? (
                <div className="relative overflow-hidden border-b border-blue-900/10 bg-gradient-to-r from-blue-800 via-indigo-800 to-violet-900 text-white">
                    <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_0%,rgba(255,255,255,0.14),transparent_50%)]" />
                    <div className="relative mx-auto max-w-6xl px-4 py-12 sm:py-16">
                        {title ? <h1 className="text-3xl font-bold tracking-tight sm:text-4xl">{title}</h1> : null}
                        {subtitle ? <p className="mt-3 max-w-2xl text-base leading-relaxed text-blue-100">{subtitle}</p> : null}
                        {hero}
                    </div>
                </div>
            ) : null}

            <main className="mx-auto max-w-6xl px-4 py-10">{children}</main>

            <footer className="border-t border-slate-800 bg-slate-950 text-slate-300">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 py-14 md:grid-cols-3">
                    <div className="md:col-span-1">
                        <p className="text-lg font-semibold text-white">{school.name}</p>
                        <p className="mt-2 text-sm leading-relaxed text-slate-400">{school.tagline}</p>
                        <div className="mt-4 space-y-1 text-sm text-slate-400">
                            {school.phone ? <p>Phone: {school.phone}</p> : null}
                            {school.email ? <p>Email: {school.email}</p> : null}
                            {school.address ? <p className="leading-relaxed">{school.address}</p> : null}
                        </div>
                    </div>
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-wider text-slate-500">Explore</p>
                        <div className="mt-4 flex flex-col gap-2 text-sm">
                            <Link className="hover:text-white" to="/about">
                                About
                            </Link>
                            <Link className="hover:text-white" to="/news">
                                News
                            </Link>
                            <Link className="hover:text-white" to="/events">
                                Events
                            </Link>
                            <Link className="hover:text-white" to="/online-admission">
                                Admission
                            </Link>
                        </div>
                    </div>
                    <div>
                        <p className="text-sm font-semibold uppercase tracking-wider text-slate-500">Stay updated</p>
                        <p className="mt-2 text-sm text-slate-400">Subscribe for announcements and highlights.</p>
                        <form onSubmit={subscribe} className="mt-4 flex flex-col gap-2 sm:flex-row">
                            <input
                                type="email"
                                required
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                placeholder="Email address"
                                className="flex-1 rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-white placeholder:text-slate-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                            />
                            <button
                                type="submit"
                                disabled={subBusy}
                                className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 disabled:opacity-60"
                            >
                                {subBusy ? '…' : 'Subscribe'}
                            </button>
                        </form>
                        {subMsg ? (
                            <p className={cn('mt-2 text-sm', subMsg.toLowerCase().includes('fail') ? 'text-amber-300' : 'text-emerald-400')}>
                                {subMsg}
                            </p>
                        ) : null}
                        <div className="mt-6 flex flex-wrap gap-4 text-sm">
                            <Link className="hover:text-white" to="/policy">
                                Privacy policy
                            </Link>
                            <Link className="hover:text-white" to="/landing">
                                Welcome landing
                            </Link>
                            <Link className="hover:text-white" to="/contact">
                                Contact
                            </Link>
                        </div>
                        <p className="mt-6 text-xs text-slate-600">
                            © {new Date().getFullYear()} {school.name}. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
