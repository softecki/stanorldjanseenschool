import React from 'react';
import { Link } from 'react-router-dom';

export function CertificateUiHomePage({ Layout }) {
    return (
        <Layout>
            <div className="mx-auto max-w-lg space-y-4 p-6">
                <h1 className="text-2xl font-bold text-slate-900">Certificate (UI)</h1>
                <p className="text-sm text-slate-500">Shortcuts to the same flows as the main certificate module.</p>
                <div className="flex flex-col gap-2">
                    <Link
                        className="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium hover:bg-slate-50"
                        to="/certificate-ui/list"
                    >
                        List templates
                    </Link>
                    <Link
                        className="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium hover:bg-slate-50"
                        to="/certificate-ui/generate"
                    >
                        Generate
                    </Link>
                    <Link
                        className="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm font-medium hover:bg-slate-50"
                        to="/certificate-ui/create"
                    >
                        Create template
                    </Link>
                    <Link className="text-sm text-blue-600 hover:text-blue-800" to="/certificate">
                        Open main certificate workspace
                    </Link>
                </div>
            </div>
        </Layout>
    );
}
