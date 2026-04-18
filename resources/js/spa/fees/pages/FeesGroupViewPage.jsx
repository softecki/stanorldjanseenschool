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

export function FeesGroupViewPage({ Layout }) {
    return <EntityViewPage Layout={Layout} title="Fees Group Details" loadEndpoint="/fees-group" backTo="/fees/groups" editBase="/fees/groups" />;
}

