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

export function FeesGroupsPage({ Layout }) {
    return <EntityListPage Layout={Layout} title="Fees Groups" endpoint="/fees-group" baseRoute="/fees/groups" createRoute="/fees/groups/create" deleteEndpoint="/fees-group/delete" columns={[
        { key: 'name', label: 'Name', render: (r) => r?.name || r?.title || '-' },
        { key: 'description', label: 'Description', render: (r) => r?.description || '-' },
        { key: 'status', label: 'Status', render: (r) => Number(r?.status) === 1 ? 'Active' : 'Inactive' },
    ]} />;
}

