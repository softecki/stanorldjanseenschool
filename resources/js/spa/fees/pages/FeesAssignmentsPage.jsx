import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import {
    ActionButtons,
    EntityListPage,
    EntityViewPage,
    FeesEntityFormPage,
    FullPageLoader,
    TransactionsListPage,
    normalizeFeesTransactionRows,
    optionLabel,
    panelTitle,
    statusChoices,
    studentsTableClass,
} from '../FeesModuleShared';

export function FeesAssignmentsPage({ Layout }) {
    return <EntityListPage Layout={Layout} title="Fees Assignments" endpoint="/fees-assign" baseRoute="/fees/assignments" createRoute="/fees/assignments/create" deleteEndpoint="/fees-assign/delete" columns={[
        { key: 'fees_group', label: 'Fees Group', render: (r) => r?.group?.name || r?.fees_group?.name || '-' },
        { key: 'class', label: 'Class', render: (r) => r?.class?.name || '-' },
        { key: 'session', label: 'Session', render: (r) => r?.session?.name || '-' },
        { key: 'status', label: 'Status', render: (r) => Number(r?.status) === 1 ? 'Active' : 'Inactive' },
    ]} />;
}

