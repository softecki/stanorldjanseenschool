import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BackendModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function BackendDashboardPdfPage({ Layout }) {
    return (
        <Shell Layout={Layout} title="Backend Dashboard PDF">
            <div className="rounded border bg-white p-4 text-sm">
                <a href="/dashboard/export-pdf" className="rounded bg-blue-600 px-3 py-2 text-white">Download Dashboard PDF</a>
            </div>
        </Shell>
    );
}

