/**
 * Splits communication/CommunicationPages.jsx → CommunicationModuleShared.jsx + pages/*.jsx + barrel.
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPath = path.join(root, 'resources', 'js', 'spa', 'communication', 'CommunicationPages.jsx');
const modDir = path.join(root, 'resources', 'js', 'spa', 'communication');
const pagesDir = path.join(modDir, 'pages');

const lines = fs.readFileSync(srcPath, 'utf8').split('\n');

const sharedHeader = `import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../api/xhrJson';

`;

const sharedBody = lines.slice(6, 21).join('\n').replace(/^function /gm, 'export function ');
fs.mkdirSync(pagesDir, { recursive: true });
fs.writeFileSync(path.join(modDir, 'CommunicationModuleShared.jsx'), `${sharedHeader}${sharedBody}\n`, 'utf8');

const exportStarts = [];
lines.forEach((l, i) => {
    if (l.trim().startsWith('export function ')) exportStarts.push(i);
});
exportStarts.push(lines.length);

const pageHeader = `import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { xhrJson } from '../../api/xhrJson';
import { Shell, paginateRows } from '../CommunicationModuleShared';
`;

const names = [];
for (let i = 0; i < exportStarts.length - 1; i++) {
    const m = lines[exportStarts[i]].trim().match(/^export function (\w+)/);
    if (!m) throw new Error(`Bad export at ${exportStarts[i] + 1}`);
    names.push(m[1]);
    const block = lines.slice(exportStarts[i], exportStarts[i + 1]).join('\n');
    fs.writeFileSync(path.join(pagesDir, `${m[1]}.jsx`), `${pageHeader}\n${block}\n`, 'utf8');
}

const barrel = `${names.map((n) => `export { ${n} } from './pages/${n}';`).join('\n')}\n`;
fs.writeFileSync(path.join(modDir, 'CommunicationPages.jsx'), barrel, 'utf8');

console.log('Communication split:', names.length, 'pages');
