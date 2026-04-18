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

export function FeesMastersPage({ Layout }) {
    return <EntityListPage Layout={Layout} title="Fees Masters" endpoint="/fees-master" baseRoute="/fees/masters" createRoute="/fees/masters/create" deleteEndpoint="/fees-master/delete" columns={[
        { key: 'fees_group', label: 'Fees Group', render: (r) => r?.group?.name || r?.fees_group?.name || '-' },
        { key: 'fees_type', label: 'Fees Type', render: (r) => r?.type?.name || r?.fees_type?.name || '-' },
        { key: 'amount', label: 'Amount', render: (r) => r?.amount || '-' },
        { key: 'due_date', label: 'Due Date', render: (r) => r?.due_date || '-' },
    ]} />;
}

