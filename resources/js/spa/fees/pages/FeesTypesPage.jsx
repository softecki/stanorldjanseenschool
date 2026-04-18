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

export function FeesTypesPage({ Layout }) {
    return <EntityListPage Layout={Layout} title="Fees Types" endpoint="/fees-type" baseRoute="/fees/types" createRoute="/fees/types/create" deleteEndpoint="/fees-type/delete" columns={[
        { key: 'name', label: 'Name', render: (r) => r?.name || '-' },
        { key: 'code', label: 'Code', render: (r) => r?.code || '-' },
        { key: 'status', label: 'Status', render: (r) => Number(r?.status) === 1 ? 'Active' : 'Inactive' },
    ]} />;
}

