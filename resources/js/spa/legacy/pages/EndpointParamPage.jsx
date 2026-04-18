import React from 'react';
import { useParams } from 'react-router-dom';
import { EndpointDataPage } from './EndpointDataPage';

export function EndpointParamPage({ Layout, title, endpointTemplate }) {
    const params = useParams();
    const endpoint = Object.keys(params).reduce(
        (acc, key) => acc.replace(`:${key}`, params[key]),
        endpointTemplate,
    );
    return <EndpointDataPage Layout={Layout} title={title} endpoint={endpoint} />;
}
