import React, { useEffect, useMemo, useState } from 'react';
import { Link, NavLink, useLocation } from 'react-router-dom';
import axios from 'axios';

const cn = (...parts) => parts.filter(Boolean).join(' ');

function Icon({ name, className = 'h-5 w-5 shrink-0' }) {
    const common = { className: cn('text-gray-500 group-hover:text-blue-600', className), fill: 'none', viewBox: '0 0 24 24', strokeWidth: 1.5, stroke: 'currentColor' };
    switch (name) {
        case 'home':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
            );
        case 'chart':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            );
        case 'users':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            );
        case 'academic':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm6 0a.75.75 0 100-1.5.75.75 0 000 1.5zm6 0a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                </svg>
            );
        case 'money':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.165c1.11.33 2.25-.036 2.973-1.022a60.858 60.858 0 014.92-4.92 1.11 1.11 0 00-.655-2.022 59.472 59.472 0 01-6.09-1.282 60.15 60.15 0 00-1.92-.234 59.64 59.64 0 00-6.335 0 60.05 60.05 0 00-1.92.234 59.472 59.472 0 01-6.09 1.282 1.11 1.11 0 00-.655 2.022 60.858 60.858 0 004.92 4.92 1.11 1.11 0 002.973 1.022 60.07 60.07 0 0115.797 2.165v-9.75a60.05 60.05 0 00-1.92-.234 59.64 59.64 0 00-6.335 0 60.15 60.15 0 00-1.92.234 59.472 59.472 0 01-6.09 1.282 1.11 1.11 0 00-.655 2.022 60.858 60.858 0 004.92 4.92 1.11 1.11 0 002.973 1.022 60.07 60.07 0 0115.797 2.165V9.75" />
                </svg>
            );
        case 'wallet':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0v3a2.25 2.25 0 01-2.25 2.25H15a3 3 0 01-6 0H5.25A2.25 2.25 0 013 12v-3" />
                </svg>
            );
        case 'book':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            );
        case 'mail':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            );
        case 'cog':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.723 6.723 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            );
        case 'bars':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} className={cn(className, 'h-6 w-6')} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            );
        case 'user-circle':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} className={cn('text-gray-500', className)} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            );
        case 'chevron-down':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} className={cn('h-4 w-4 text-gray-400 transition-transform', className)} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            );
        case 'document':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            );
        case 'tag':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.568 3.082a.75.75 0 01.53-.22h5.902c.199 0 .39.079.53.22l3.388 3.388c.14.14.22.331.22.53v5.902a.75.75 0 01-.22.53l-6.89 6.89a.75.75 0 01-1.06 0l-6.89-6.89a.75.75 0 010-1.06l6.89-6.89z" />
                    <circle cx="14.25" cy="7.875" r="1.125" />
                </svg>
            );
        case 'arrow-up':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 19.5V4.5m0 0l-4.5 4.5M12 4.5l4.5 4.5" />
                </svg>
            );
        case 'user-minus':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM3.75 19.5a8.25 8.25 0 0116.5 0M16.5 12.75h4.5" />
                </svg>
            );
        case 'shield':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 3l7.5 3v6c0 5.25-3.413 8.396-7.5 9-4.087-.604-7.5-3.75-7.5-9V6L12 3z" />
                </svg>
            );
        case 'trash':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 6h18M9 6V4.5A1.5 1.5 0 0110.5 3h3A1.5 1.5 0 0115 4.5V6m-9 0l.75 12.75A1.5 1.5 0 008.25 20.25h7.5a1.5 1.5 0 001.5-1.5L18 6" />
                </svg>
            );
        case 'building':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 21h18M4.5 21V6.75A.75.75 0 015.25 6h5.25a.75.75 0 01.75.75V21m0 0h6V11.25a.75.75 0 00-.75-.75h-4.5a.75.75 0 00-.75.75V21" />
                </svg>
            );
        case 'layers':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 3l9 4.5-9 4.5L3 7.5 12 3zm0 9l9 4.5-9 4.5-9-4.5 9-4.5z" />
                </svg>
            );
        case 'atom':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <circle cx="12" cy="12" r="2.25" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M4.5 12c0-4.142 3.358-7.5 7.5-7.5S19.5 7.858 19.5 12 16.142 19.5 12 19.5 4.5 16.142 4.5 12zM7.05 5.55l9.9 12.9M16.95 5.55l-9.9 12.9" />
                </svg>
            );
        case 'clipboard-check':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 5.25h6M9.75 3h4.5A2.25 2.25 0 0116.5 5.25V6h1.5a1.5 1.5 0 011.5 1.5v10.5a1.5 1.5 0 01-1.5 1.5h-12A1.5 1.5 0 014.5 18V7.5A1.5 1.5 0 016 6h1.5v-.75A2.25 2.25 0 019.75 3zm-1.5 9.75l2.25 2.25 5.25-5.25" />
                </svg>
            );
        case 'clock':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 7.5v4.5l3 3" />
                </svg>
            );
        case 'calendar':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <rect x="3.75" y="4.5" width="16.5" height="15.75" rx="2.25" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 9h16.5M8.25 2.25V6.75M15.75 2.25V6.75" />
                </svg>
            );
        case 'credit-card':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <rect x="2.25" y="5.25" width="19.5" height="13.5" rx="2.25" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M2.25 9.75h19.5M6.75 15h3.75" />
                </svg>
            );
        case 'receipt':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 3.75h12v16.5l-2.25-1.5-1.5 1.5-1.5-1.5-1.5 1.5-1.5-1.5-1.5 1.5L6 20.25V3.75zM9 8.25h6M9 11.25h6M9 14.25h4.5" />
                </svg>
            );
        case 'arrow-path':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M16.023 9.348h4.992V4.356M3.978 14.652H8.97v4.992M20.477 9.348A8.25 8.25 0 006.374 5.05L3.978 7.446m0 7.206A8.25 8.25 0 0017.626 18.95l2.396-2.396" />
                </svg>
            );
        case 'bank':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 9l9-5.25L21 9M4.5 10.5h15M6 10.5v7.5m4.5-7.5v7.5m4.5-7.5v7.5m4.5-7.5v7.5M3 19.5h18" />
                </svg>
            );
        case 'speaker':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M11.25 5.25L6 9H3.75v6H6l5.25 3.75V5.25zM15.75 9.75a3 3 0 010 4.5M17.625 7.875a6 6 0 010 8.25" />
                </svg>
            );
        case 'chat':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M7.5 8.25h9m-9 3h6m-9.75 8.25l1.313-3.5A8.25 8.25 0 1118.75 15.5H8.437L3.75 19.5z" />
                </svg>
            );
        case 'megaphone':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M10.34 6.357L18.75 3v18l-8.41-3.357A3.75 3.75 0 016.75 14.25V9.75a3.75 3.75 0 013.59-3.393zM6.75 15.75H5.25a2.25 2.25 0 01-2.25-2.25v-3a2.25 2.25 0 012.25-2.25h1.5" />
                </svg>
            );
        case 'briefcase':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <rect x="3" y="6.75" width="18" height="12.75" rx="2.25" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9 6.75v-1.5A2.25 2.25 0 0111.25 3h1.5A2.25 2.25 0 0115 5.25v1.5M3 12h18" />
                </svg>
            );
        case 'identification':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <rect x="3" y="5.25" width="18" height="13.5" rx="2.25" />
                    <circle cx="8.25" cy="11.25" r="1.875" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12.75 9.75h4.5M12.75 12.75h4.5M6 15.75h4.5" />
                </svg>
            );
        case 'building-office':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3.75 21h16.5M5.25 21V4.5h13.5V21M9 8.25h1.5m3 0H15M9 12h1.5m3 0H15M9 15.75h1.5m3 0H15" />
                </svg>
            );
        case 'sparkles':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 3l1.125 3.375L16.5 7.5l-3.375 1.125L12 12l-1.125-3.375L7.5 7.5l3.375-1.125L12 3zM18 13.5l.75 2.25L21 16.5l-2.25.75L18 19.5l-.75-2.25L15 16.5l2.25-.75L18 13.5zM6 13.5l.75 2.25L9 16.5l-2.25.75L6 19.5l-.75-2.25L3 16.5l2.25-.75L6 13.5z" />
                </svg>
            );
        case 'bell':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M14.25 18.75a2.25 2.25 0 11-4.5 0M5.25 15.75h13.5l-1.5-2.25V10.5a5.25 5.25 0 10-10.5 0v3L5.25 15.75z" />
                </svg>
            );
        case 'server':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <rect x="3.75" y="4.5" width="16.5" height="5.25" rx="1.5" />
                    <rect x="3.75" y="14.25" width="16.5" height="5.25" rx="1.5" />
                    <circle cx="7.5" cy="7.125" r=".75" />
                    <circle cx="7.5" cy="16.875" r=".75" />
                </svg>
            );
        case 'wrench':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M21 6.75a4.5 4.5 0 01-6.11 4.2l-8.4 8.4a2.121 2.121 0 01-3-3l8.4-8.4A4.5 4.5 0 1121 6.75z" />
                </svg>
            );
        case 'shield-check':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 3l7.5 3v6c0 5.25-3.413 8.396-7.5 9-4.087-.604-7.5-3.75-7.5-9V6L12 3z" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M9.75 12l1.5 1.5 3-3" />
                </svg>
            );
        case 'device-phone':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <rect x="7.5" y="2.25" width="9" height="19.5" rx="2.25" />
                    <circle cx="12" cy="18" r=".75" />
                </svg>
            );
        case 'key':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <circle cx="8.25" cy="12" r="3.75" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 12h8.25M17.25 12v2.25M19.5 12v1.5" />
                </svg>
            );
        case 'envelope':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <rect x="3" y="5.25" width="18" height="13.5" rx="2.25" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 7.5l9 6 9-6" />
                </svg>
            );
        case 'user-group':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 19.5a6 6 0 00-12 0M9 12a3 3 0 100-6 3 3 0 000 6zM18 17.25a4.5 4.5 0 00-3.375-4.35M16.5 9a2.25 2.25 0 100-4.5" />
                </svg>
            );
        case 'water':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 3.75c2.625 3 5.25 6.185 5.25 9A5.25 5.25 0 0112 18a5.25 5.25 0 01-5.25-5.25c0-2.815 2.625-6 5.25-9z" />
                </svg>
            );
        case 'logout':
            return (
                <svg xmlns="http://www.w3.org/2000/svg" {...common} aria-hidden="true">
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15" />
                    <path strokeLinecap="round" strokeLinejoin="round" d="M18 15l3-3m0 0-3-3m3 3H9" />
                </svg>
            );
        default:
            return null;
    }
}

/** Match paths for sidebar active + open groups. Supports SPA `to` or Laravel `href`. */
function itemMatchPath(item) {
    if (item.to) return item.to;
    if (item.href) {
        const h = item.href.trim();
        if (h.startsWith('http://') || h.startsWith('https://')) {
            try {
                return new URL(h).pathname.replace(/\/$/, '') || '/';
            } catch {
                return h;
            }
        }
        return h.split('?')[0].replace(/\/$/, '') || '/';
    }
    return '';
}

function pathMatches(pathname, itemOrPath) {
    const to = typeof itemOrPath === 'string' ? itemOrPath : itemMatchPath(itemOrPath);
    if (!to) return false;
    if (to === '/dashboard') return pathname === '/dashboard';
    return pathname === to || pathname.startsWith(`${to}/`);
}

/**
 * Mirrors resources/views/backend/partials/sidebar.blade.php (order, labels, routes).
 * `to` = React Router; `href` = full Laravel URL when no SPA screen exists.
 */
const menuGroups = [
    {
        id: 'student',
        label: 'Student Info',
        icon: 'user-group',
        links: [
            { label: 'Students', to: '/students', icon: 'user-group' },
            { label: 'Student Category', to: '/categories', icon: 'tag' },
            { label: 'Promote Students', to: '/promote', icon: 'arrow-up' },
            { label: 'QR Code', to: '/qr_code', icon: 'sparkles' },
            { label: 'Deleted student history', to: '/deleted-history', icon: 'trash' },
        ],
    },
    {
        id: 'academic',
        label: 'Academic',
        icon: 'building-office',
        links: [
            { label: 'Class', to: '/classes', icon: 'building' },
            { label: 'Subject', to: '/subjects', icon: 'atom' },
            { label: 'Class Room', to: '/class-rooms', icon: 'building-office' },
        ],
    },
    {
        id: 'fees',
        label: 'Fees',
        icon: 'receipt',
        links: [
            { label: 'Group', to: '/groups', icon: 'layers' },
            { label: 'Type', to: '/types', icon: 'tag' },
            { label: 'Master', to: '/masters', icon: 'book' },
            { label: 'Assign', to: '/assignments', icon: 'clipboard-check' },
            { label: 'Collect', to: '/collections', icon: 'wallet' },
            { label: 'Transactions', to: '/transactions', icon: 'receipt' },
            { label: 'Online Transactions', to: '/online-transactions', icon: 'credit-card' },
            { label: 'Amendments', to: '/amendments', icon: 'arrow-path' },
        ],
    },
    {
        id: 'accounts',
        label: 'Accounts',
        icon: 'bank',
        links: [
            { label: 'Overview', to: '/accounting', icon: 'chart' },
            { label: 'Accounting Dashboard', to: '/accounting/dashboard', icon: 'chart' },
            { label: 'Chart of Accounts', to: '/chart-of-accounts', icon: 'book' },
            { label: 'Payment Methods', to: '/payment-methods', icon: 'credit-card' },
            { label: 'Account Head', to: '/account-heads', icon: 'document' },
            { label: 'Income', to: '/income', icon: 'arrow-up' },
            { label: 'Expense', to: '/expense', icon: 'arrow-path' },
            { label: 'Cash', to: '/cash', icon: 'wallet' },
            { label: 'Deposits', to: '/deposits', icon: 'bank' },
            { label: 'Payments', to: '/payments', icon: 'credit-card' },
            { label: 'Transactions', to: '/account-transactions', icon: 'receipt' },
            { label: 'Suppliers', to: '/suppliers', icon: 'building' },
            { label: 'Invoices', to: '/invoices', icon: 'document' },
            { label: 'Products', to: '/product', icon: 'building' },
            { label: 'Items', to: '/item', icon: 'tag' },
        ],
    },
    {
        id: 'report',
        label: 'Report',
        icon: 'chart',
        links: [
            { label: 'Reports home', to: '/reports', icon: 'chart' },
            { label: 'Fees Collection', to: '/reports/fees-collection', icon: 'receipt' },
            { label: 'Break Down Report', to: '/reports/outstanding-breakdown', icon: 'document' },
            { label: 'Collection Summary', to: '/reports/fees-summary', icon: 'chart' },
            { label: 'Student List', to: '/reports/students', icon: 'users' },
            { label: 'Fees Assignment By Year', to: '/reports/fees-by-year', icon: 'calendar' },
            { label: 'Boarding Students Report', to: '/reports/boarding-students', icon: 'building-office' },
            { label: 'Duplicate Students', to: '/reports/duplicate-students', icon: 'user-group' },
            { label: 'Marksheet', to: '/reports/marksheet', icon: 'document' },
            { label: 'Merit List', to: '/reports/merit-list', icon: 'sparkles' },
            { label: 'Accounts', to: '/reports/account', icon: 'bank' },
            { label: 'Bank Reconciliation', to: '/reports/accounting/bank-reconciliation', icon: 'bank' },
            { label: 'Bank Reconciliation Process', to: '/reports/accounting/bank-reconciliation/process', icon: 'arrow-path' },
        ],
    },
    {
        id: 'communication',
        label: 'Communication',
        icon: 'megaphone',
        links: [
            { label: 'Notice Board', to: '/communication/notice-board', icon: 'megaphone' },
            { label: 'SMS / Mail', to: '/communication/smsmail', icon: 'chat' },
            { label: 'SMS Campaign', to: '/communication/smsmail/campaign', icon: 'speaker' },
            { label: 'SMS/Mail Template', to: '/communication/template', icon: 'envelope' },
        ],
    },
    {
        id: 'staff',
        label: 'Staff Manage',
        icon: 'briefcase',
        links: [
            { label: 'Staff home', to: '/staff', icon: 'briefcase' },
            { label: 'Roles', to: '/roles', icon: 'shield' },
            { label: 'Staff', to: '/users', icon: 'identification' },
            { label: 'Department', to: '/staff/department', icon: 'building-office' },
            { label: 'Batch Processing', to: '/staff/batch-processing', icon: 'arrow-path' },
            { label: 'Designation', to: '/staff/designation', icon: 'briefcase' },
        ],
    },
    {
        id: 'settings',
        label: 'Settings',
        icon: 'wrench',
        links: [
            { label: 'Settings home', to: '/settings', icon: 'wrench' },
            { label: 'General Settings', to: '/settings/general', icon: 'cog' },
            { label: 'Notification Setting', to: '/settings/notification', icon: 'bell' },
            { label: 'Storage Settings', to: '/settings/storage', icon: 'server' },
            { label: 'Task Schedules', to: '/settings/task-schedulers', icon: 'clock' },
            { label: 'Software Update', to: '/settings/software-update', icon: 'wrench' },
            { label: 'Recaptcha Settings', to: '/settings/recaptcha', icon: 'shield-check' },
            { label: 'SMS Settings', to: '/settings/sms', icon: 'device-phone' },
            { label: 'Payment Gateway Settings', to: '/settings/payment-gateway', icon: 'credit-card' },
            { label: 'Email Settings', to: '/settings/email', icon: 'envelope' },
            { label: 'Genders', to: '/settings/genders', icon: 'users' },
            { label: 'Bank Accounts', to: '/banks-accounts', icon: 'bank' },
            { label: 'Religions', to: '/settings/religions', icon: 'sparkles' },
            { label: 'Blood Groups', to: '/blood-groups', icon: 'water' },
            { label: 'Sessions', to: '/settings/sessions', icon: 'key' },
        ],
    },
];

const linkClass = ({ isActive }) =>
    cn(
        'sidebar-link group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
        isActive ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50',
    );

const subLinkClass = ({ isActive }) =>
    cn(
        'sidebar-link flex items-center gap-2 rounded-lg py-2 pl-9 pr-3 text-sm transition',
        isActive ? 'bg-blue-50/80 font-medium text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
    );

export function AdminLayout({ children }) {
    const loc = useLocation();
    const [now, setNow] = useState(() => new Date());
    const [sidebarOpen, setSidebarOpen] = useState(() => (typeof window !== 'undefined' ? window.innerWidth >= 1024 : true));
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const [openGroups, setOpenGroups] = useState(() => {
        const init = {};
        menuGroups.forEach((g, i) => {
            init[i] = g.links.some((item) => pathMatches(loc.pathname, item));
        });
        return init;
    });

    useEffect(() => {
        const next = {};
        menuGroups.forEach((g, i) => {
            next[i] = g.links.some((item) => pathMatches(loc.pathname, item));
        });
        setOpenGroups((prev) => {
            const merged = { ...prev };
            Object.keys(next).forEach((k) => {
                if (next[k]) merged[k] = true;
            });
            return merged;
        });
    }, [loc.pathname]);

    useEffect(() => {
        const timer = setInterval(() => setNow(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    const pageTitle = useMemo(() => {
        if (loc.pathname === '/dashboard') return 'Dashboard';
        if (loc.pathname === '/classes') return '';
        if (loc.pathname === '/sections') return '';
        if (loc.pathname === '/subjects') return '';
        if (loc.pathname === '/subject-assigns') return '';
        if (loc.pathname === '/time-schedules') return '';
        if (loc.pathname === '/class-rooms') return '';
        if (loc.pathname === '/class-routines') return '';
        if (loc.pathname === '/exam-routines') return '';
        if (loc.pathname === '/deleted-history') return '';
        const parts = loc.pathname.split('/').filter(Boolean);
        const last = parts[parts.length - 1];
        if (!last) return 'Workspace';
        return last.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    }, [loc.pathname]);

    const dayLabel = now.toLocaleDateString(undefined, { weekday: 'long' });
    const dateLabel = now.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
    const timeLabel = now.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    const toggleGroup = (i) => {
        setOpenGroups((prev) => ({ ...prev, [i]: !prev[i] }));
    };

    return (
        <div className="flex h-screen flex-col overflow-hidden bg-gray-100 font-sans text-gray-700 antialiased">
            <div
                className="flex h-full min-h-0 flex-1 overflow-hidden"
                onKeyDown={(e) => {
                    if (e.key === 'Escape') setUserMenuOpen(false);
                }}
                role="presentation"
            >
                {sidebarOpen ? (
                    <button
                        type="button"
                        className="fixed inset-0 z-40 bg-gray-900/40 backdrop-blur-sm lg:hidden"
                        aria-label="Close menu"
                        onClick={() => setSidebarOpen(false)}
                    />
                ) : null}

                <aside
                    className={cn(
                        'fixed inset-y-0 left-0 z-50 flex h-full min-h-0 w-64 flex-col border-r border-gray-200 bg-white shadow-sm transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0',
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                    )}
                >
                    <div className="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-gradient-to-r from-white to-blue-50/50 px-4">
                        <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-md ring-2 ring-blue-500/20">
                            <Icon name="chart" className="h-5 w-5 text-white group-hover:text-white" />
                        </span>
                        <div className="min-w-0">
                            <span className="truncate bg-gradient-to-r from-blue-800 to-indigo-700 bg-clip-text text-lg font-bold text-transparent">School Admin</span>
                            <p className="truncate text-[10px] font-medium uppercase tracking-wide text-gray-400">Workspace</p>
                        </div>
                    </div>

                    <nav className="flex-1 overflow-y-auto py-4">
                        <ul className="space-y-0.5 px-2">
                            <li>
                                <NavLink to="/dashboard" className={linkClass} end>
                                    <Icon name="home" />
                                    Dashboard
                                </NavLink>
                            </li>
                        </ul>

                        <ul className="space-y-1 px-2">
                            {menuGroups.map((group, i) => {
                                const expanded = !!openGroups[i];
                                const groupActive = group.links.some((item) => pathMatches(loc.pathname, item));
                                return (
                                    <li key={group.id} className="rounded-lg border border-transparent">
                                        <button
                                            type="button"
                                            onClick={() => toggleGroup(i)}
                                            className={cn(
                                                'flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition',
                                                groupActive ? 'bg-blue-50/50 text-blue-800' : 'text-gray-800 hover:bg-gray-50',
                                            )}
                                        >
                                            <span className="flex min-w-0 items-center gap-3">
                                                <span
                                                    className={cn(
                                                        'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border',
                                                        groupActive
                                                            ? 'border-blue-200 bg-blue-100/70'
                                                            : 'border-gray-200 bg-gray-50',
                                                    )}
                                                >
                                                    <Icon
                                                        name={group.icon || 'document'}
                                                        className={cn(
                                                            'h-4 w-4',
                                                            groupActive ? 'text-blue-700' : 'text-blue-600',
                                                        )}
                                                    />
                                                </span>
                                                <span className="truncate">{group.label}</span>
                                            </span>
                                            <Icon name="chevron-down" className={cn(expanded ? 'rotate-180' : '')} />
                                        </button>
                                        {expanded ? (
                                            <ul className="mt-0.5 space-y-0.5 border-l border-gray-100 pb-1 pl-2 ml-3">
                                                {group.links.map((item) => {
                                                    const pathKey = item.to || item.href;
                                                    const isActive = pathMatches(loc.pathname, item);
                                                    const closeMobile = () => {
                                                        if (typeof window !== 'undefined' && window.innerWidth < 1024) {
                                                            setSidebarOpen(false);
                                                        }
                                                    };
                                                    return (
                                                        <li key={`${group.id}-${item.label}-${pathKey}`}>
                                                            {item.to ? (
                                                                <NavLink to={item.to} className={subLinkClass} onClick={closeMobile}>
                                                                    <Icon name={item.icon || 'document'} className="h-4 w-4 shrink-0" />
                                                                    {item.label}
                                                                </NavLink>
                                                            ) : (
                                                                <a href={item.href} className={subLinkClass({ isActive })} onClick={closeMobile}>
                                                                    <Icon name={item.icon || 'document'} className="h-4 w-4 shrink-0" />
                                                                    {item.label}
                                                                </a>
                                                            )}
                                                        </li>
                                                    );
                                                })}
                                            </ul>
                                        ) : null}
                                    </li>
                                );
                            })}
                        </ul>

                        <div className="mt-4 px-2">
                            <button
                                type="button"
                                className="sidebar-link flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                                onClick={() => {
                                    axios.post('/logout').then(() => {
                                        window.location.href = '/login';
                                    });
                                }}
                            >
                                <Icon name="logout" className="h-5 w-5 text-red-500" />
                                Log out
                            </button>
                        </div>
                    </nav>
                </aside>

                <div className="flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
                    <header className="sticky top-0 z-30 shrink-0 border-b border-gray-200 bg-white shadow-sm">
                        <div className="h-0.5 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600" aria-hidden />
                        <div className="flex h-16 items-center justify-between gap-4 px-4">
                        <div className="flex min-w-0 items-center gap-3">
                            <button
                                type="button"
                                className="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 lg:hidden"
                                onClick={() => setSidebarOpen((s) => !s)}
                                aria-label="Toggle sidebar"
                            >
                                <Icon name="bars" />
                            </button>
                            <h1 className="truncate text-lg font-semibold text-gray-800">{pageTitle}</h1>
                        </div>

                        <div
                            className="flex min-w-0 flex-1 items-center justify-end gap-2 px-1 sm:justify-center sm:px-2"
                            aria-live="polite"
                        >
                            <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-blue-600">
                                <Icon name="clock" className="h-4 w-4" />
                            </span>
                            <div className="min-w-0 text-right sm:text-center">
                                <p className="truncate text-xs font-semibold tabular-nums text-gray-900 sm:hidden">{timeLabel}</p>
                                <div className="hidden sm:block">
                                    <p className="truncate text-[11px] font-semibold uppercase tracking-wide text-gray-500">{dayLabel}</p>
                                    <p className="truncate text-xs text-gray-600">
                                        <span className="tabular-nums">{dateLabel}</span>
                                        <span className="mx-1.5 text-gray-300" aria-hidden>
                                            ·
                                        </span>
                                        <span className="font-semibold tabular-nums text-gray-900">{timeLabel}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            <button
                                type="button"
                                className="flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
                                onClick={() => setUserMenuOpen((v) => !v)}
                            >
                                <Icon name="user-circle" className="h-8 w-8" />
                                <span className="hidden sm:inline">Account</span>
                                <Icon name="chevron-down" />
                            </button>
                            {userMenuOpen ? (
                                <>
                                    <button type="button" className="fixed inset-0 z-40 cursor-default" aria-label="Close" onClick={() => setUserMenuOpen(false)} />
                                    <div className="absolute right-0 z-50 mt-2 w-52 rounded-xl border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-black/5">
                                        <a href="/my/profile" className="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                            <Icon name="user-circle" className="h-4 w-4" />
                                            Profile
                                        </a>
                                        <form
                                            method="post"
                                            action="/logout"
                                            className="border-t border-gray-100"
                                            onSubmit={(e) => {
                                                e.preventDefault();
                                                axios.post('/logout').then(() => {
                                                    window.location.href = '/login';
                                                });
                                            }}
                                        >
                                            <button type="submit" className="flex w-full items-center gap-2 px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">
                                                Log out
                                            </button>
                                        </form>
                                    </div>
                                </>
                            ) : null}
                        </div>
                        </div>
                    </header>

                    <main className="min-h-0 flex-1 overflow-y-auto bg-gradient-to-b from-gray-50/80 to-gray-100 p-4 sm:p-6">{children}</main>
                </div>
            </div>

        </div>
    );
}
