import React from 'react';
import { SpaApiExplorerPage } from '../../components/SpaApiExplorerPage';

export function EndpointDataPage({ Layout, title, endpoint }) {
    return <SpaApiExplorerPage Layout={Layout} title={title} endpoint={endpoint} />;
}
