import React from 'react';
import { LinkGrid, Shell } from '../LegacyViewMigrationsShared';

export function PanelHubPage({ Layout }) {
    return (
        <Shell Layout={Layout} title="Parent / Student Panel Migration" subtitle="Hand-built routes for parent and student panel pages.">
            <LinkGrid
                links={[
                    { to: '/parent-panel/dashboard', label: 'Parent Dashboard' },
                    { to: '/parent-panel/attendance', label: 'Parent Attendance' },
                    { to: '/parent-panel/class-routine', label: 'Parent Class Routine' },
                    { to: '/parent-panel/exam-routine', label: 'Parent Exam Routine' },
                    { to: '/parent-panel/subject-list', label: 'Parent Subject List' },
                    { to: '/parent-panel/homework-list', label: 'Parent Homework List' },
                    { to: '/parent-panel/fees', label: 'Parent Fees' },
                    { to: '/parent-panel/marksheet', label: 'Parent Marksheet' },
                    { to: '/student-panel/dashboard', label: 'Student Dashboard' },
                    { to: '/student-panel/attendance', label: 'Student Attendance' },
                    { to: '/student-panel/class-routine', label: 'Student Class Routine' },
                    { to: '/student-panel/exam-routine', label: 'Student Exam Routine' },
                    { to: '/student-panel/subject-list', label: 'Student Subject List' },
                    { to: '/student-panel/homeworks', label: 'Student Homeworks' },
                    { to: '/student-panel/fees', label: 'Student Fees' },
                    { to: '/student-panel/marksheet', label: 'Student Marksheet' },
                ]}
            />
        </Shell>
    );
}
