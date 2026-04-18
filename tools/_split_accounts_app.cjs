/**
 * Splits accounts/AccountsAppPages.jsx → AccountsModuleShared.jsx + accounts/pages/*.jsx + barrel.
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPath = path.join(root, 'resources', 'js', 'spa', 'accounts', 'AccountsAppPages.jsx');
const modDir = path.join(root, 'resources', 'js', 'spa', 'accounts');
const pagesDir = path.join(modDir, 'pages');

const lines = fs.readFileSync(srcPath, 'utf8').split('\n');

const sharedRaw = lines.slice(0, 240).join('\n');
let shared = sharedRaw.replace(
    /const xhrJson = \{[^}]+\};\s*\n/,
    "import { xhrJson } from '../api/xhrJson';\n\n",
);
shared = shared.replace(/^const (btnPrimary|btnGhost|inputClass) =/gm, 'export const $1 =');
shared = shared.replace(/^function /gm, 'export function ');

fs.mkdirSync(pagesDir, { recursive: true });
fs.writeFileSync(path.join(modDir, 'AccountsModuleShared.jsx'), `${shared}\n`, 'utf8');

const exportStarts = [];
lines.forEach((l, i) => {
    if (l.trim().startsWith('export function ')) exportStarts.push(i);
});
exportStarts.push(lines.length);

const pageHeader = `import React, { useEffect, useMemo, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
    AccountCard,
    AccountEmptyState,
    AccountPageHeader,
    AccountTable,
    AccountTD,
    AccountTH,
    AccountTHead,
    AccountTR,
} from '../components/AccountUi';
import { xhrJson } from '../../api/xhrJson';
import {
    AccountFullPageLoader,
    AccountsCrudListPage,
    AccountsHomePageComponent,
    AccountsPageShell,
    AccountsSectionHeader,
    AccountsSimpleFormPage,
    btnGhost,
    btnPrimary,
    extractRows,
    inputClass,
} from '../AccountsModuleShared';
`;

const names = [];
for (let i = 0; i < exportStarts.length - 1; i++) {
    const m = lines[exportStarts[i]].trim().match(/^export function (\w+)/);
    if (!m) throw new Error(`Bad export at ${exportStarts[i] + 1}`);
    if (exportStarts[i] < 240) continue;
    names.push(m[1]);
    const block = lines.slice(exportStarts[i], exportStarts[i + 1]).join('\n');
    fs.writeFileSync(path.join(pagesDir, `${m[1]}.jsx`), `${pageHeader}\n${block}\n`, 'utf8');
}

const barrel = `${names.map((n) => `export { ${n} } from './pages/${n}';`).join('\n')}\n`;
fs.writeFileSync(srcPath, barrel, 'utf8');
console.log('AccountsAppPages split:', names.length, 'pages');
