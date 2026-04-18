/**
 * Splits students/StudentModulePages.jsx → StudentModuleShared.jsx + students/pages/*.jsx + barrel.
 * Run: node tools/_split_student_module.cjs
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPath = path.join(root, 'resources', 'js', 'spa', 'students', 'StudentModulePages.jsx');
const modDir = path.join(root, 'resources', 'js', 'spa', 'students');
const pagesDir = path.join(modDir, 'pages');

const lines = fs.readFileSync(srcPath, 'utf8').split('\n');

const sharedHeader = `import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../layout/AdminLayout';
import { xhrJson } from '../api/xhrJson';

`;

const sharedBody = lines.slice(6, 29).join('\n').replace(/^function /gm, 'export function ');
fs.mkdirSync(pagesDir, { recursive: true });
fs.writeFileSync(path.join(modDir, 'StudentModuleShared.jsx'), `${sharedHeader}${sharedBody}\n`, 'utf8');

const exportStarts = [];
lines.forEach((l, i) => {
    if (l.trim().startsWith('export function ')) exportStarts.push(i);
});
exportStarts.push(lines.length);

const pageHeader = `import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import axios from 'axios';
import { AdminLayout } from '../../layout/AdminLayout';
import { xhrJson } from '../../api/xhrJson';
import { FullPageLoader, mappedClassOptions } from '../StudentModuleShared';
`;

const names = [];
for (let i = 0; i < exportStarts.length - 1; i++) {
    const m = lines[exportStarts[i]].trim().match(/^export function (\w+)/);
    if (!m) throw new Error(`Bad export line ${exportStarts[i] + 1}`);
    names.push(m[1]);
    const a = exportStarts[i];
    const b = exportStarts[i + 1];
    const block = lines.slice(a, b).join('\n');
    fs.writeFileSync(path.join(pagesDir, `${m[1]}.jsx`), `${pageHeader}\n${block}\n`, 'utf8');
}

const barrel = `${names.map((n) => `export { ${n} } from './pages/${n}';`).join('\n')}\n`;
fs.writeFileSync(srcPath, barrel, 'utf8');
console.log('Student module split:', names.length, 'pages');
