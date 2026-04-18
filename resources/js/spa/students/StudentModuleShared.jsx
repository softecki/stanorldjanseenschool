import React from 'react';
import { UiPageLoader } from '../ui/UiKit';

export function FullPageLoader(props) {
    return <UiPageLoader {...props} />;
}

export function mappedClassOptions(classes = []) {
    return classes.map((c) => ({
        id: c?.class?.id ?? c?.id,
        name: c?.class?.name ?? c?.name ?? '-',
    })).filter((c) => c.id);
}

