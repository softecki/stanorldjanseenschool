import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { PublicLayout } from '../PublicLayout';
import {
    CTABand,
    GhostButtonLink,
    PrimaryButtonLink,
    PublicCard,
    PublicError,
    PublicLoading,
    SectionHeader,
    Timeline,
    TrustStrip,
} from '../PublicUi';
import { mediaUrl, mergeSchoolMeta } from '../utils';

const MILESTONES = [
    { title: 'Foundations', body: 'A vision for inclusive learning rooted in integrity, curiosity, and service.' },
    { title: 'Growth', body: 'Expanded programs, campus improvements, and stronger community partnerships.' },
    { title: 'Today', body: 'Student-centered teaching, digital tools, and holistic wellbeing support.' },
];

export default function PublicAboutPage() {
    const [data, setData] = useState(null);
    const [meta, setMeta] = useState(null);
    const [err, setErr] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios
            .get('/about', { headers: xhrJson })
            .then((r) => {
                setData(r.data?.data || {});
                setMeta(r.data?.meta || null);
            })
            .catch((ex) => setErr(ex.response?.data?.message || 'Failed to load.'))
            .finally(() => setLoading(false));
    }, []);

    const school = mergeSchoolMeta(meta);
    const abouts = data?.abouts || [];
    const teachers = data?.teachers || [];
    const lead = abouts[0];
    const rest = abouts.slice(1);

    return (
        <PublicLayout
            title="About our school"
            subtitle="Mission, milestones, and the people who make learning extraordinary."
            school={school}
        >
            <PublicError message={err} />
            {loading ? (
                <PublicLoading />
            ) : (
                <>
                    <TrustStrip
                        items={[
                            { label: 'Student-first culture' },
                            { label: 'Qualified faculty' },
                            { label: 'Safe campus environment' },
                            { label: 'Community partnerships' },
                        ]}
                    />

                    {lead ? (
                        <section className="mt-12 grid gap-10 lg:grid-cols-2 lg:items-start">
                            <div>
                                <SectionHeader eyebrow="Leadership message" title={lead.title || 'Our story'} />
                                {lead.upload?.path ? (
                                    <img
                                        src={mediaUrl(lead.upload.path)}
                                        alt=""
                                        className="mt-6 max-h-72 w-full rounded-2xl object-cover shadow-lg ring-1 ring-slate-900/5"
                                    />
                                ) : null}
                            </div>
                            <PublicCard className="mt-10 lg:mt-14">
                                <div
                                    className="prose prose-slate max-w-none text-sm leading-relaxed"
                                    dangerouslySetInnerHTML={{ __html: lead.description || '' }}
                                />
                            </PublicCard>
                        </section>
                    ) : null}

                    {rest.length ? (
                        <section className="mt-16 space-y-10">
                            {rest.map((a) => (
                                <article key={a.id} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                                    <h2 className="text-xl font-bold text-slate-900">{a.title}</h2>
                                    <div
                                        className="prose prose-slate mt-3 max-w-none text-sm leading-relaxed"
                                        dangerouslySetInnerHTML={{ __html: a.description || '' }}
                                    />
                                </article>
                            ))}
                        </section>
                    ) : null}

                    <section className="mt-16 grid gap-10 lg:grid-cols-2">
                        <div>
                            <SectionHeader eyebrow="Our journey" title="Milestones" description="How we grow with our students and community." />
                            <div className="mt-6">
                                <Timeline items={MILESTONES} />
                            </div>
                        </div>
                        <PublicCard>
                            <h3 className="text-lg font-semibold text-slate-900">Campus values</h3>
                            <ul className="mt-4 list-inside list-disc space-y-2 text-sm text-slate-600">
                                <li>Respect, empathy, and inclusion in every classroom.</li>
                                <li>Rigor with support — high expectations and caring guidance.</li>
                                <li>Integrity in assessment, communication, and conduct.</li>
                                <li>Innovation that serves learning, not distraction.</li>
                            </ul>
                        </PublicCard>
                    </section>

                    {teachers.length ? (
                        <section className="mt-16">
                            <SectionHeader
                                title="Faculty spotlight"
                                description="Meet educators dedicated to nurturing confident, capable learners."
                            />
                            <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                {teachers.map((t) => (
                                    <PublicCard key={t.id} padding="p-5 text-center">
                                        <img
                                            src={mediaUrl(t.upload?.path || t.image)}
                                            alt=""
                                            className="mx-auto h-28 w-28 rounded-full object-cover ring-4 ring-blue-50"
                                        />
                                        <p className="mt-4 font-semibold text-slate-900">
                                            {t.first_name} {t.last_name}
                                        </p>
                                        {t.designation?.title ? (
                                            <p className="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">{t.designation.title}</p>
                                        ) : null}
                                    </PublicCard>
                                ))}
                            </div>
                        </section>
                    ) : null}

                    <div className="mt-16">
                        <CTABand
                            title="See campus life in action"
                            subtitle="Explore admissions requirements, fees guidance, and how to reach our registrars."
                            primary={<PrimaryButtonLink to="/online-admission">Admission steps</PrimaryButtonLink>}
                            secondary={<GhostButtonLink to="/contact">Ask a question</GhostButtonLink>}
                        />
                    </div>

                    <p className="mt-10 text-center text-sm text-slate-500">
                        Looking for quick facts? Visit the{' '}
                        <Link className="font-semibold text-blue-700 hover:underline" to="/">
                            home page
                        </Link>{' '}
                        for news and events.
                    </p>
                </>
            )}
        </PublicLayout>
    );
}
