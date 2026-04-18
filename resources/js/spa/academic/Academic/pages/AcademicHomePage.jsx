import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { FullPageLoader, Panel, firstValue, normalizeRows, optionFrom } from '../../AcademicModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function AcademicHomePage({ Layout }) {
    const links = [
        ['/classes', 'Classes'],
        ['/sections', 'Sections'],
        ['/subjects', 'Subjects'],
        ['/shifts', 'Shifts'],
        ['/class-rooms', 'Class Rooms'],
        ['/class-setups', 'Class Setups'],
        ['/subject-assigns', 'Subject Assigns'],
        ['/time-schedules', 'Time Schedules'],
        ['/class-routines', 'Class Routines'],
        ['/exam-routines', 'Exam Routines'],
    ];
    return (
        <Panel Layout={Layout} title="Academic">
            <div className="grid grid-cols-1 gap-2 text-sm md:grid-cols-2 lg:grid-cols-3">
                {links.map(([to, label]) => <Link key={to} className="rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:bg-blue-50/40" to={to}>{label}</Link>)}
            </div>
        </Panel>
    );
}

