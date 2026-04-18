import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../api/xhrJson';

export function Shell({ Layout, children }) {
    return (
        <Layout>
            <div className="mx-auto max-w-7xl space-y-6 p-6">{children}</div>
        </Layout>
    );
}

export function paginateRows(r) {
    const p = r.data?.data;
    if (Array.isArray(p?.data)) return p.data;
    if (Array.isArray(p)) return p;
    return [];
}

