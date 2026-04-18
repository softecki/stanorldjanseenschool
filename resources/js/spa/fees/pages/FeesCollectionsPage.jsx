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

export function FeesCollectionsPage({ Layout }) {
    return <EntityListPage Layout={Layout} title="Fees Collections" endpoint="/fees-collect" baseRoute="/fees/collections" createRoute="/fees/collections/create" deleteEndpoint="/fees-collect/delete" columns={[
        { key: 'student', label: 'Student', render: (r) => r?.student_name || r?.student?.full_name || r?.student?.first_name || '-' },
        { key: 'class', label: 'Class', render: (r) => r?.class?.name || '-' },
        { key: 'amount', label: 'Amount', render: (r) => r?.paid_amount || r?.amount || '-' },
        { key: 'date', label: 'Date', render: (r) => r?.date || '-' },
    ]} />;
}

