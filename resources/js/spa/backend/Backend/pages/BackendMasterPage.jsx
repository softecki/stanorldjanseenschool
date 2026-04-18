import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BackendModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function BackendMasterPage({ Layout }) {
    return <Shell Layout={Layout} title="Backend Master"><div className="rounded border bg-white p-4 text-sm">Backend master SPA shell.</div></Shell>;
}

