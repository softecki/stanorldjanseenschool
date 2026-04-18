/**
 * One-time splitter: monolithic AccountExtraPages.jsx → AccountExtraShared.jsx + extraPages/*.jsx + barrel.
 * If AccountExtraPages.jsx is already a barrel (re-exports only), do not run — restore the monolith from git first.
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const srcPath = path.join(root, 'resources', 'js', 'spa', 'accounts', 'AccountExtraPages.jsx');
const modDir = path.join(root, 'resources', 'js', 'spa', 'accounts');
const pagesDir = path.join(modDir, 'extraPages');

const raw = fs.readFileSync(srcPath, 'utf8');
if (!raw.includes('function AccountListPage') && !raw.includes('export function AccountListPage')) {
    console.error('AccountExtraPages.jsx looks like a barrel already; aborting.');
    process.exit(1);
}

const lines = raw.split('\n');

let shared = lines.slice(0, 84).join('\n').replace(
    /const xhrJson = \{[^}]+\};\s*\n/,
    "import { xhrJson } from '../api/xhrJson';\n\n",
);
shared = shared.replace(/^function /gm, 'export function ');

fs.mkdirSync(pagesDir, { recursive: true });
fs.writeFileSync(path.join(modDir, 'AccountExtraShared.jsx'), `${shared}\n`, 'utf8');

const exportStarts = [];
lines.forEach((l, i) => {
    if (l.trim().startsWith('export function ')) exportStarts.push(i);
});
exportStarts.push(lines.length);

const listHeader = `import React from 'react';
import { AccountListPage } from '../AccountExtraShared';
`;

const formHeader = `import React from 'react';
import { AcademicFormPage } from '../../academic/AcademicPages';
`;

const names = [];
for (let i = 0; i < exportStarts.length - 1; i++) {
    const m = lines[exportStarts[i]].trim().match(/^export function (\w+)/);
    if (!m) throw new Error(`Bad export at ${exportStarts[i] + 1}`);
    names.push(m[1]);
    const block = lines.slice(exportStarts[i], exportStarts[i + 1]).join('\n');
    const header = m[1].endsWith('FormPage') ? formHeader : listHeader;
    fs.writeFileSync(path.join(pagesDir, `${m[1]}.jsx`), `${header}\n${block}\n`, 'utf8');
}

const barrel = `${names.map((n) => `export { ${n} } from './extraPages/${n}';`).join('\n')}\n`;
fs.writeFileSync(srcPath, barrel, 'utf8');
console.log('AccountExtraPages split:', names.length);
