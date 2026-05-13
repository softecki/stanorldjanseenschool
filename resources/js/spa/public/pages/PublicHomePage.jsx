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
    PublicEmpty,
    PublicError,
    PublicLoading,
    SectionHeader,
    StatCard,
    TrustStrip,
} from '../PublicUi';
import { excerpt, formatDate, mediaUrl, mergeSchoolMeta, safeArray } from '../utils';

const PROGRAM_FEATURES = [
    {
        title: 'Holistic academics',
        body: 'Balanced curriculum with strong foundations in literacy, numeracy, science, and digital readiness.',
    },
    {
        title: 'Character & leadership',
        body: 'Guidance, mentoring, and student voice initiatives that nurture responsibility and empathy.',
    },
    {
        title: 'Community & activities',
        body: 'Clubs, arts, sports, and celebrations that make campus life engaging and inclusive.',
    },
];

export default function PublicHomePage() {
    const [payload, setPayload] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get('/', { headers: xhrJson })
            .then((r) => {
                setPayload(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Could not load homepage.'))
            .finally(() => setLoading(false));
    }, []);

    const school = mergeSchoolMeta(meta);

    const sliders = safeArray(payload?.sliders);
    const countersRaw = payload?.counters;
    const counters =
        safeArray(countersRaw?.data).length > 0 ? safeArray(countersRaw?.data) : safeArray(countersRaw);
    const galleryCategories = safeArray(payload?.galleryCategory);
    const galleryPaginator = payload?.gallery;
    const galleryItems = galleryPaginator?.data ?? [];
    const latestNews = safeArray(payload?.latestNews);
    const comingEvents = safeArray(payload?.comingEvents);

    const sliderImg = (s) => mediaUrl(s?.upload?.path || s?.image || s?.banner_image || s?.photo || '');

    const statAccents = ['blue', 'emerald', 'amber', 'violet'];

    return (
        <PublicLayout school={school}>
            <PublicError message={err} />
            {loading ? (
                <PublicLoading label="Loading campus highlights…" />
            ) : (
                <>
                    {/* Hero */}
                    <section className="-mx-4 overflow-hidden rounded-3xl border border-slate-200/80 bg-slate-900 shadow-2xl ring-1 ring-slate-900/5 sm:mx-0">
                        <div className="relative flex min-h-[300px] flex-col justify-end bg-gradient-to-br from-blue-950 via-indigo-950 to-slate-950 p-8 text-white md:min-h-[380px]">
                            {sliders.length ? (
                                <img
                                    src={sliderImg(sliders[0])}
                                    alt=""
                                    className="absolute inset-0 h-full w-full object-cover opacity-35"
                                />
                            ) : null}
                            <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent" />
                            <div className="relative z-10 max-w-2xl">
                                <p className="text-xs font-bold uppercase tracking-[0.25em] text-blue-200/90">Welcome</p>
                                <h2 className="mt-2 text-3xl font-bold leading-tight md:text-5xl">{school.name}</h2>
                                <p className="mt-3 max-w-xl text-base text-blue-100 md:text-lg">{school.tagline}</p>
                                <div className="mt-8 flex flex-wrap gap-3">
                                    <PrimaryButtonLink to="/online-admission">Apply online</PrimaryButtonLink>
                                    <GhostButtonLink to="/contact">Plan a visit</GhostButtonLink>
                                </div>
                            </div>
                        </div>
                    </section>

                    <TrustStrip
                        items={[
                            { label: 'Licensed academic programs' },
                            { label: 'Safe, supportive campus' },
                            { label: 'Transparent admissions' },
                            { label: 'Active parent communication' },
                        ]}
                    />

                    {/* Stats */}
                    {counters.length ? (
                        <section className="mt-14">
                            <SectionHeader
                                eyebrow="At a glance"
                                title="Numbers that reflect our community"
                                description="Live counters from your school profile — celebrating growth and consistency."
                            />
                            <div className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                {counters.slice(0, 4).map((c, i) => (
                                    <StatCard
                                        key={c.id ?? i}
                                        value={c.number ?? c.counter ?? '—'}
                                        label={c.title ?? c.name ?? '—'}
                                        accent={statAccents[i % statAccents.length]}
                                    />
                                ))}
                            </div>
                        </section>
                    ) : null}

                    {/* Programs */}
                    <section className="mt-16">
                        <SectionHeader
                            eyebrow="Programs"
                            title="Built for modern learners"
                            description="A campus experience that blends academic rigor with wellbeing, creativity, and belonging."
                            actions={
                                <Link to="/about" className="text-sm font-semibold text-blue-700 hover:underline">
                                    Learn our story →
                                </Link>
                            }
                        />
                        <div className="mt-8">
                            <FeatureGrid
                                items={PROGRAM_FEATURES.map((p) => ({
                                    title: p.title,
                                    body: p.body,
                                    icon: (
                                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm6 0a.75.75 0 100-1.5.75.75 0 000 1.5zm6 0a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                                        </svg>
                                    ),
                                }))}
                            />
                        </div>
                    </section>

                    <section className="mt-16 grid gap-10 lg:grid-cols-2">
                        <div>
                            <SectionHeader eyebrow="Newsroom" title="Latest stories" />
                            <div className="mt-6 space-y-4">
                                {latestNews.length ? (
                                    latestNews.map((n) => (
                                        <Link
                                            key={n.id}
                                            to={`/news-detail/${n.id}`}
                                            className="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 transition hover:border-blue-200 hover:shadow-md"
                                        >
                                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                {formatDate(n.publish_date)}
                                            </p>
                                            <p className="mt-2 font-semibold text-slate-900">{n.title}</p>
                                            <p className="mt-2 text-sm text-slate-600">{excerpt(n.description || n.content, 130)}</p>
                                        </Link>
                                    ))
                                ) : (
                                    <PublicEmpty title="No news published yet" hint="Check back soon for updates from campus." />
                                )}
                            </div>
                            <Link to="/news" className="mt-5 inline-block text-sm font-semibold text-blue-700 hover:underline">
                                View all news →
                            </Link>
                        </div>
                        <div>
                            <SectionHeader eyebrow="Calendar" title="Upcoming events" />
                            <div className="mt-6 space-y-4">
                                {comingEvents.length ? (
                                    comingEvents.map((ev) => (
                                        <Link
                                            key={ev.id}
                                            to={`/event-detail/${ev.id}`}
                                            className="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm ring-1 ring-slate-900/5 transition hover:border-blue-200"
                                        >
                                            <div className="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow">
                                                <span className="text-[10px] font-bold uppercase opacity-90">
                                                    {formatDate(ev.date).split(' ')[0]}
                                                </span>
                                                <span className="text-xl font-bold leading-none">
                                                    {formatDate(ev.date).split(' ')[2] || '•'}
                                                </span>
                                            </div>
                                            <div className="min-w-0">
                                                <p className="font-semibold text-slate-900">{ev.title}</p>
                                                <p className="mt-1 text-sm text-slate-600">{excerpt(ev.description, 110)}</p>
                                            </div>
                                        </Link>
                                    ))
                                ) : (
                                    <PublicEmpty title="No upcoming events" hint="Follow notices for schedule changes." />
                                )}
                            </div>
                            <Link to="/events" className="mt-5 inline-block text-sm font-semibold text-blue-700 hover:underline">
                                Browse events →
                            </Link>
                        </div>
                    </section>

                    {/* Gallery */}
                    {galleryCategories.length || galleryItems.length ? (
                        <section className="mt-16">
                            <SectionHeader title="Campus gallery" description="Moments from learning, performances, and community life." />
                            <div className="mt-8 grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                {galleryItems.map((g) => (
                                    <PublicCard key={g.id} padding="p-0 overflow-hidden">
                                        <img
                                            src={mediaUrl(g.upload?.path || g.image)}
                                            alt={g.title || ''}
                                            className="aspect-[4/3] w-full object-cover"
                                        />
                                        {g.title ? (
                                            <p className="px-3 py-2 text-xs font-medium text-slate-700">{g.title}</p>
                                        ) : null}
                                    </PublicCard>
                                ))}
                            </div>
                        </section>
                    ) : null}

                    <div className="mt-16">
                        <CTABand
                            title="Ready for the next chapter?"
                            subtitle="Start an online application or speak with our admissions desk — we’ll guide you through requirements and timelines."
                            primary={<PrimaryButtonLink to="/online-admission">Begin admission</PrimaryButtonLink>}
                            secondary={<GhostButtonLink to="/contact">Talk to admissions</GhostButtonLink>}
                        />
                    </div>
                </>
            )}
        </PublicLayout>
    );
}
