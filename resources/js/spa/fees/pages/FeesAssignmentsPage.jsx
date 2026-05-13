import React from 'react';
import { EntityListPage } from '../FeesModuleShared';

function pickName(obj) {
    if (obj == null) return null;
    if (typeof obj === 'string') {
        const t = obj.trim();
        return t.length ? t : null;
    }
    if (typeof obj === 'object' && !Array.isArray(obj)) {
        const n = obj.name ?? obj.title;
        if (typeof n === 'string' && n.trim()) return n.trim();
    }
    return null;
}

export function FeesAssignmentsPage({ Layout }) {
    return (
        <EntityListPage
            Layout={Layout}
            variant="corporate"
            title="Fees Assignments"
            createButtonLabel="New assignment"
            hideEyebrow
            hideTitle
            ignoreMetaTitle
            entityLabel="assignments"
            endpoint="/fees-assign"
            baseRoute="/assignments"
            createRoute="/assignments/create"
            deleteEndpoint="/fees-assign/delete"
            columns={[
                {
                    key: 'fees_group',
                    label: 'Fees group',
                    thClassName: 'min-w-[10rem]',
                    tdClassName: 'font-medium text-slate-900',
                    render: (r) => pickName(r?.group) || pickName(r?.fees_group) || '—',
                },
                {
                    key: 'class',
                    label: 'Class',
                    thClassName: 'min-w-[8rem]',
                    render: (r) => pickName(r?.class) || '—',
                },
                {
                    key: 'section',
                    label: 'Section',
                    thClassName: 'w-[7rem]',
                    render: (r) => pickName(r?.section) || '—',
                },
                {
                    key: 'session',
                    label: 'Session',
                    thClassName: 'min-w-[8rem]',
                    render: (r) => pickName(r?.session) || '—',
                },
            ]}
        />
    );
}
