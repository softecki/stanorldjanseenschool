import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';

export function PromoteStudentsIndexPage() {
    return (
        <AdminLayout>
            <div className="mx-auto max-w-7xl p-6">
                <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-800">Promote Students</h1>
                            <p className="mt-1 text-sm text-gray-500">Create a promote session and search students to promote.</p>
                        </div>
                        <Link to="/promote/create" className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                            Create
                        </Link>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

