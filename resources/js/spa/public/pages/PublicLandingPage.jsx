import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import {
    CTABand,
    FeatureGrid,
    GhostButtonLink,
    PrimaryButtonLink,
    PublicCard,
    PublicLoading,
    SectionHeader,
    TrustStrip,
} from '../PublicUi';
import { mergeSchoolMeta } from '../utils';

const icon = (path) => (
    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" aria-hidden>
        <path strokeLinecap="round" strokeLinejoin="round" d={path} />
    </svg>
);

const LANDING_FEATURES = [
    {
        title: 'Academic depth',
        body: 'Structured progression from early years through senior levels, with clear outcomes and supportive assessment.',
        icon: icon('M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'),
    },
    {
        title: 'Whole-student care',
        body: 'Pastoral support, wellbeing resources, and respectful discipline so every learner feels known and valued.',
        icon: icon('M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'),
    },
    {
        title: 'Connected community',
        body: 'Families stay informed through notices, events, and digital touchpoints—plus transparent channels when you need us.',
        icon: icon('M18 18.72a9.09 9.09 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'),
    },
];

const checkIcon = (
    <svg className="h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden>
        <path
            fillRule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
            clipRule="evenodd"
        />
    </svg>
);

function QuickLinkCard({ to, title, description, tone = 'blue' }) {
    const tones = {
        blue: 'from-blue-600 to-indigo-700',
        violet: 'from-violet-600 to-purple-800',
        teal: 'from-teal-600 to-cyan-700',
        amber: 'from-amber-500 to-orange-600',
    };
    const bar = tones[tone] || tones.blue;
    return (
        <Link to={to} className="group block h-full">
            <PublicCard padding="p-0" className="h-full overflow-hidden transition group-hover:border-blue-200 group-hover:shadow-lg">
                <div className={`h-1.5 bg-gradient-to-r ${bar}`} />
                <div className="p-5">
                    <p className="text-lg font-semibold text-slate-900 group-hover:text-blue-800">{title}</p>
                    <p className="mt-2 text-sm leading-relaxed text-slate-600">{description}</p>
                    <p className="mt-4 text-sm font-semibold text-blue-700">
                        Open <span aria-hidden>→</span>
                    </p>
                </div>
            </PublicCard>
        </Link>
    );
}

export default function PublicLandingPage() {
    const [meta, setMeta] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get('/', { headers: xhrJson })
            .then((r) => setMeta(r.data?.meta || null))
            .catch(() => setMeta(null))
            .finally(() => setLoading(false));
    }, []);

    const school = mergeSchoolMeta(meta);

    return (
        <PublicLayout school={school}>
            {loading ? (
                <PublicLoading label="Preparing your welcome…" />
            ) : (
                <>
                    {/* Hero */}
                    <section className="relative overflow-hidden rounded-3xl border border-slate-200/90 bg-slate-950 shadow-2xl ring-1 ring-slate-900/10">
                        <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_70%_-10%,rgba(56,189,248,0.35),transparent)]" />
                        <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_0%_100%,rgba(99,102,241,0.25),transparent)]" />
                        <div className="pointer-events-none absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-950/95 to-indigo-950/90" />

                        <div className="relative grid gap-12 px-6 py-12 sm:px-10 sm:py-16 lg:grid-cols-12 lg:items-center">
                            <div className="lg:col-span-7">
                                <p className="text-xs font-bold uppercase tracking-[0.28em] text-sky-300/95">Welcome</p>
                                <h1 className="mt-4 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-[2.75rem] lg:leading-[1.08]">
                                    {school.name}
                                </h1>
                                <p className="mt-5 max-w-xl text-lg leading-relaxed text-slate-300">{school.tagline}</p>

                                {(school.phone || school.email) && (
                                    <div className="mt-8 flex flex-wrap gap-3">
                                        {school.phone ? (
                                            <a
                                                href={`tel:${String(school.phone).replace(/\s/g, '')}`}
                                                className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white backdrop-blur transition hover:bg-white/10"
                                            >
                                                <svg className="h-4 w-4 shrink-0 text-sky-300" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" aria-hidden>
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"
                                                    />
                                                </svg>
                                                {school.phone}
                                            </a>
                                        ) : null}
                                        {school.email ? (
                                            <a
                                                href={`mailto:${school.email}`}
                                                className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-medium text-white backdrop-blur transition hover:bg-white/10"
                                            >
                                                <svg className="h-4 w-4 shrink-0 text-sky-300" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" aria-hidden>
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"
                                                    />
                                                </svg>
                                                {school.email}
                                            </a>
                                        ) : null}
                                    </div>
                                )}

                                <div className="mt-10 flex flex-wrap gap-3">
                                    <PrimaryButtonLink to="/">Explore the site</PrimaryButtonLink>
                                    <GhostButtonLink to="/online-admission">Online admission</GhostButtonLink>
                                    <Link
                                        to="/about"
                                        className="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-slate-200 underline-offset-4 hover:text-white hover:underline"
                                    >
                                        Our story
                                    </Link>
                                </div>
                            </div>

                            <div className="relative hidden lg:col-span-5 lg:block">
                                <div className="relative mx-auto max-w-sm">
                                    <div className="absolute -left-6 top-8 h-36 w-36 rounded-full bg-sky-500/20 blur-3xl" aria-hidden />
                                    <div className="absolute -right-4 bottom-0 h-32 w-32 rounded-full bg-indigo-500/25 blur-3xl" aria-hidden />
                                    <div className="relative space-y-4">
                                        <div className="translate-x-4 rounded-2xl border border-white/10 bg-white/10 p-5 text-white shadow-xl backdrop-blur-md">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-sky-200/90">For families</p>
                                            <p className="mt-2 text-sm leading-relaxed text-slate-100">Browse news, events, and notices—then reach out when you are ready.</p>
                                        </div>
                                        <div className="-translate-x-2 rounded-2xl border border-white/10 bg-gradient-to-br from-sky-600/90 to-indigo-800/90 p-5 text-white shadow-xl">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-white/80">For students</p>
                                            <p className="mt-2 text-sm leading-relaxed text-sky-50">Results and campus updates stay a tap away on the public site.</p>
                                        </div>
                                        <div className="translate-x-2 rounded-2xl border border-emerald-400/30 bg-emerald-950/40 p-5 text-emerald-50 shadow-xl backdrop-blur-sm">
                                            <p className="text-xs font-semibold uppercase tracking-wide text-emerald-200/90">Trusted</p>
                                            <p className="mt-2 text-sm leading-relaxed">Transparent processes and staff-ready tools behind the scenes.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div className="mt-10">
                        <TrustStrip
                            items={[
                                { label: 'Licensed programs', icon: checkIcon },
                                { label: 'Student-centred support', icon: checkIcon },
                                { label: 'Clear admissions path', icon: checkIcon },
                                { label: 'Secure online services', icon: checkIcon },
                            ]}
                        />
                    </div>

                    {/* Why choose */}
                    <section className="mt-16">
                        <SectionHeader
                            eyebrow="Why families choose us"
                            title="Education with clarity and heart"
                            description="Whether you are exploring for the first time or returning for another year, this is the front door to our digital campus—warm, organised, and easy to navigate."
                            actions={
                                <Link to="/contact" className="text-sm font-semibold text-blue-700 hover:underline">
                                    Talk to us →
                                </Link>
                            }
                        />
                        <div className="mt-10">
                            <FeatureGrid items={LANDING_FEATURES} />
                        </div>
                    </section>

                    {/* Quick links */}
                    <section className="mt-16">
                        <SectionHeader
                            eyebrow="Jump in"
                            title="Popular starting points"
                            description="Pick a destination below—each area is kept current for parents, guardians, and visitors."
                        />
                        <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <QuickLinkCard
                                to="/about"
                                tone="blue"
                                title="About"
                                description="Mission, leadership, and what makes our learning culture distinct."
                            />
                            <QuickLinkCard
                                to="/news"
                                tone="violet"
                                title="News & stories"
                                description="Highlights from classrooms, sports, arts, and community milestones."
                            />
                            <QuickLinkCard
                                to="/events"
                                tone="teal"
                                title="Events"
                                description="Open days, ceremonies, and calendar moments you will not want to miss."
                            />
                            <QuickLinkCard
                                to="/online-admission"
                                tone="amber"
                                title="Admission"
                                description="Start or continue an application online—step-by-step and mobile friendly."
                            />
                        </div>
                    </section>

                    {school.address ? (
                        <section className="mt-16">
                            <PublicCard className="border-slate-200/90 bg-gradient-to-br from-slate-50 to-white">
                                <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p className="text-xs font-bold uppercase tracking-[0.2em] text-blue-700/90">Visit</p>
                                        <h2 className="mt-2 text-xl font-bold text-slate-900">Find us on the map</h2>
                                        <p className="mt-2 max-w-xl text-sm leading-relaxed text-slate-600">{school.address}</p>
                                    </div>
                                    <div className="flex shrink-0 flex-wrap gap-3">
                                        <Link
                                            to="/contact"
                                            className="inline-flex items-center justify-center rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-800"
                                        >
                                            Get directions
                                        </Link>
                                        <Link
                                            to="/result"
                                            className="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-sm hover:bg-slate-50"
                                        >
                                            Exam results
                                        </Link>
                                    </div>
                                </div>
                            </PublicCard>
                        </section>
                    ) : null}

                    <div className="mt-16">
                        <CTABand
                            title="Ready for the full experience?"
                            subtitle="The homepage brings together sliders, counters, galleries, and live news—everything in one scroll."
                            primary={<PrimaryButtonLink to="/">Open homepage</PrimaryButtonLink>}
                            secondary={
                                <Link
                                    to="/notices"
                                    className="inline-flex items-center justify-center rounded-xl border border-white/40 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10"
                                >
                                    Read notices
                                </Link>
                            }
                        />
                    </div>
                </>
            )}
        </PublicLayout>
    );
}
