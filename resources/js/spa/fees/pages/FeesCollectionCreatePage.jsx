import React from 'react';
import { Link } from 'react-router-dom';

/**
 * Fee collection in this app is started from the collect flow (search / pick student).
 * This page replaces a mistaken match of /collections/:id with id "create".
 */
export function FeesCollectionCreatePage({ Layout }) {
    return (
        <Layout>
            <div className="mx-auto max-w-2xl p-6">
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h1 className="text-xl font-semibold text-gray-800">New fee collection</h1>
                    <p className="mt-2 text-sm text-gray-600">Search for a student and record a payment in the standard collect flow.</p>
                    <div className="mt-4 flex flex-wrap gap-2">
                        <a
                            href="/fees-collect/collect-list"
                            className="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Open collect list
                        </a>
                        <Link
                            to="/collections"
                            className="inline-flex rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            Back to collections
                        </Link>
                    </div>
                </div>
            </div>
        </Layout>
    );
}
