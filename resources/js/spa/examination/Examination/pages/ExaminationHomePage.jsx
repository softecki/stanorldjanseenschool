import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Shell, readPaginate } from '../../ExaminationModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function ExaminationHomePage({ Layout }) {
    const links = [
        { to: '/examination/marks-grades', label: 'Marks grades' },
        { to: '/examination/settings', label: 'Examination settings' },
        { to: '/examination/exam-assign', label: 'Exam assign' },
        { to: '/examination/marks-register', label: 'Marks register' },
    ];
    return (
        <Shell Layout={Layout} wide>
            <h1 className="text-2xl font-bold text-slate-900">Examination</h1>
            <p className="text-sm text-slate-500">Manage grades, exam assignment, and marks register.</p>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {links.map((l) => (
                    <Link
                        key={l.to}
                        to={l.to}
                        className="rounded-xl border border-slate-200 bg-white p-4 text-sm font-medium text-blue-700 shadow-sm hover:bg-slate-50"
                    >
                        {l.label}
                    </Link>
                ))}
            </div>
        </Shell>
    );
}

/* ——— Marks grades ——— */

