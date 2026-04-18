import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Shell } from '../../BackendModuleShared';
import { xhrJson } from '../../../api/xhrJson';

export function BackendDashboardPage({ Layout }) {
    const [data, setData] = useState(null);
    useEffect(() => {
        axios.get('/dashboard', { headers: xhrJson }).then((r) => setData(r.data?.data || {}));
    }, []);
    return (
        <Shell Layout={Layout} title="Backend Dashboard">
            <div className="rounded border bg-white p-4 text-xs">
                <pre>{JSON.stringify(data, null, 2)}</pre>
            </div>
        </Shell>
    );
}

