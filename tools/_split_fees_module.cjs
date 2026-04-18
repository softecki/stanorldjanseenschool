/**
 * Splits fees/FeesModulePages.jsx → FeesModuleShared.jsx + pages/*.jsx + barrel re-exports.
 * Run: node tools/_split_fees_module.cjs
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPath = path.join(root, 'resources', 'js', 'spa', 'fees', 'FeesModulePages.jsx');
const feesDir = path.join(root, 'resources', 'js', 'spa', 'fees');
const pagesDir = path.join(feesDir, 'pages');

const raw = fs.readFileSync(srcPath, 'utf8');
const lines = raw.split('\n');

const sharedA = lines.slice(6, 232).join('\n');
const sharedB = lines.slice(774, 989).join('\n');

const sharedHeader = `import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../api/xhrJson';

`;

let sharedBody = `${sharedA}\n\n${sharedB}\n`;
sharedBody = sharedBody.replace(/^function /gm, 'export function ');

fs.mkdirSync(pagesDir, { recursive: true });
fs.writeFileSync(path.join(feesDir, 'FeesModuleShared.jsx'), `${sharedHeader}${sharedBody}`, 'utf8');

const exportStarts = [];
lines.forEach((l, i) => {
    if (l.trim().startsWith('export function ')) exportStarts.push(i);
});
exportStarts.push(lines.length);

const normIdx = lines.findIndex((l) => l.trim().startsWith('function normalizeFeesTransactionRows'));
if (normIdx === -1) throw new Error('normalizeFeesTransactionRows anchor not found');

const names = [];
for (let i = 0; i < exportStarts.length - 1; i++) {
    const m = lines[exportStarts[i]].trim().match(/^export function (\w+)/);
    if (!m) throw new Error(`Bad export at ${exportStarts[i] + 1}`);
    names.push(m[1]);
}

const pageHeaderFull = `import React, { useEffect, useMemo, useRef, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../api/xhrJson';
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
`;

const pageHeaderTransactions = `import React from 'react';
import { TransactionsListPage } from '../FeesModuleShared';
`;

for (let i = 0; i < exportStarts.length - 1; i++) {
    const a = exportStarts[i];
    let b = exportStarts[i + 1];
    if (a < normIdx && b > normIdx) b = normIdx;
    const name = names[i];
    const block = lines.slice(a, b).join('\n');
    const filePath = path.join(pagesDir, `${name}.jsx`);
    const tx = name === 'FeesTransactionsPage' || name === 'FeesOnlineTransactionsPage' || name === 'FeesAmendmentsPage';
    const header = tx ? pageHeaderTransactions : pageHeaderFull;
    fs.writeFileSync(filePath, `${header}\n${block}\n`, 'utf8');
}

const barrel = `${names.map((n) => `export { ${n} } from './pages/${n}';`).join('\n')}\n`;
fs.writeFileSync(srcPath, barrel, 'utf8');

console.log('Done.', names.length, 'pages + FeesModuleShared.');
