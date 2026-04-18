/**
 * Splits monolithic *Pages.jsx into *ModuleShared.jsx (top-level helpers) + <Stem>/pages/*.jsx + barrel.
 * Run: node tools/splitSpaBarrelModules.cjs
 */
const fs = require('fs');
const path = require('path');

const SPA = path.join(__dirname, '..', 'resources', 'js', 'spa');
const xhrRe = /\r?\nconst xhrJson = \{[\s\S]*?\};\r?\n/;

function pagesFolderName(srcFile) {
    const base = path.basename(srcFile, '.jsx');
    return base.endsWith('Pages') ? base.slice(0, -'Pages'.length) : base;
}

function listExportFunctionStarts(lines) {
    const out = [];
    lines.forEach((line, i) => {
        if (/^\s*export function \w+/.test(line)) out.push(i);
    });
    return out;
}

function extractSharedExports(sharedText) {
    const names = [];
    for (const line of sharedText.split('\n')) {
        const m = line.match(/^\s*export function (\w+)/);
        if (m) names.push(m[1]);
    }
    return names;
}

function nestDepthToModRoot(nest) {
    return nest.split(/[/\\]/).filter(Boolean).length + 1;
}

function nestDepthToSpa(nest) {
    return nest.split(/[/\\]/).filter(Boolean).length + 2;
}

function splitOne({ relDir, srcFile, sharedFile, pageHeaderLines }) {
    const modDir = path.join(SPA, relDir);
    const srcPath = path.join(modDir, srcFile);
    const sharedPath = path.join(modDir, sharedFile);
    const nest = pagesFolderName(srcFile);
    const pagesDir = path.join(modDir, nest, 'pages');
    const upMod = '../'.repeat(nestDepthToModRoot(nest));
    const upSpa = '../'.repeat(nestDepthToSpa(nest));

    if (!fs.existsSync(srcPath)) {
        console.warn('Missing', srcPath);
        return;
    }
    const text = fs.readFileSync(srcPath, 'utf8');
    if (!/\bexport function\b/.test(text)) {
        console.log('Skip (no export function):', relDir, srcFile);
        return;
    }
    if (/from ['"]\.\//.test(text) && !/\bexport function\b/.test(text.trim().split('\n').slice(0, 5).join('\n'))) {
        /* heuristic: already re-export barrel */
    }

    const lines = text.split(/\r?\n/);
    const exportIdx = listExportFunctionStarts(lines);
    if (!exportIdx.length) {
        console.warn('No exports', srcPath);
        return;
    }
    const first = exportIdx[0];
    const preambleLines = lines.slice(0, first);
    const preambleRaw = preambleLines.join('\n');
    const hasHelpers = preambleLines.some((l) => /^\s*function \w+/.test(l));

    let pagePrefix;

    if (hasHelpers) {
        let sharedBody = preambleRaw.replace(xhrRe, `\nimport { xhrJson } from '../api/xhrJson';\n`);
        sharedBody = sharedBody.replace(/^function /gm, 'export function ');
        fs.mkdirSync(path.dirname(sharedPath), { recursive: true });
        fs.writeFileSync(sharedPath, `${sharedBody.trimEnd()}\n`, 'utf8');
        const sharedExportNames = extractSharedExports(sharedBody);
        const sharedBase = sharedFile.replace(/\.jsx$/, '');
        let header = pageHeaderLines
            .join('\n')
            .replace(/__SHARED_IMPORTS__/g, sharedExportNames.join(', '))
            .replace(/__SHARED_MOD__/g, `${upMod}${sharedBase}`)
            .replace(/__API_XHR__/g, `${upSpa}api/xhrJson`)
            .replace(/__SPA_SHARED_UI__/g, `${upSpa}shared/UiStates`);
        pagePrefix = `${header.trimEnd()}\n`;
    } else {
        const xhrPath = xhrRe.test(preambleRaw) ? `${upSpa}api/xhrJson` : null;
        let preamble = preambleRaw;
        if (xhrPath) preamble = preamble.replace(xhrRe, `\nimport { xhrJson } from '${xhrPath}';\n`);
        pagePrefix = `${preamble.trimEnd()}\n`;
    }

    fs.mkdirSync(pagesDir, { recursive: true });
    const names = [];
    for (let i = 0; i < exportIdx.length; i++) {
        const start = exportIdx[i];
        const end = i + 1 < exportIdx.length ? exportIdx[i + 1] : lines.length;
        const block = lines.slice(start, end).join('\n');
        const m = lines[start].trim().match(/^export function (\w+)/);
        if (!m) throw new Error(`Bad export at ${start + 1} in ${srcPath}`);
        names.push(m[1]);
        fs.writeFileSync(path.join(pagesDir, `${m[1]}.jsx`), `${pagePrefix}\n${block}\n`, 'utf8');
    }

    const barrelRel = `./${nest.replace(/\\/g, '/')}/pages/`;
    const barrel = `${names.map((n) => `export { ${n} } from '${barrelRel}${n}';`).join('\n')}\n`;
    fs.writeFileSync(srcPath, barrel, 'utf8');
    console.log(relDir, srcFile, '->', names.length, 'pages', hasHelpers ? `+ ${sharedFile}` : '(preamble-only)');
}

const MODULES = [
    {
        relDir: 'backend',
        srcFile: 'BackendPages.jsx',
        sharedFile: 'BackendModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'academic',
        srcFile: 'AcademicPages.jsx',
        sharedFile: 'AcademicModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'examination',
        srcFile: 'ExaminationPages.jsx',
        sharedFile: 'ExaminationModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'certificate',
        srcFile: 'CertificatePages.jsx',
        sharedFile: 'CertificateModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useMemo, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'banks',
        srcFile: 'BankAccountsPages.jsx',
        sharedFile: 'BankAccountsModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'settings',
        srcFile: 'BloodGroupPages.jsx',
        sharedFile: 'BloodGroupModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'settings',
        srcFile: 'GenderPages.jsx',
        sharedFile: 'GenderModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'settings',
        srcFile: 'ReligionPages.jsx',
        sharedFile: 'ReligionModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'settings',
        srcFile: 'SessionPages.jsx',
        sharedFile: 'SessionModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate, useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'gmeet',
        srcFile: 'GmeetPages.jsx',
        sharedFile: null,
    },
    {
        relDir: 'homework',
        srcFile: 'HomeworkPages.jsx',
        sharedFile: null,
    },
    {
        relDir: 'idcard',
        srcFile: 'IdCardPages.jsx',
        sharedFile: null,
    },
    {
        relDir: 'languages',
        srcFile: 'LanguagePages.jsx',
        sharedFile: null,
    },
    {
        relDir: 'library',
        srcFile: 'LibraryPages.jsx',
        sharedFile: null,
    },
    {
        relDir: 'goods',
        srcFile: 'GoodsPages.jsx',
        sharedFile: 'GoodsModuleShared.jsx',
        pageHeaderLines: [
            "import React from 'react';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
        ],
    },
    {
        relDir: 'orders',
        srcFile: 'OrderPages.jsx',
        sharedFile: 'OrdersModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link, useNavigate } from 'react-router-dom';",
            "import { EmptyState, confirmDelete } from '__SPA_SHARED_UI__';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'panels',
        srcFile: 'PanelPages.jsx',
        sharedFile: 'PanelListModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useMemo, useState } from 'react';",
            "import axios from 'axios';",
            "import { Link } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'panels',
        srcFile: 'PanelProfilePages.jsx',
        sharedFile: 'PanelProfileModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useState } from 'react';",
            "import axios from 'axios';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
    {
        relDir: 'panels',
        srcFile: 'PanelOnlineExamPages.jsx',
        sharedFile: 'PanelOnlineExamModuleShared.jsx',
        pageHeaderLines: [
            "import React, { useEffect, useMemo, useState } from 'react';",
            "import axios from 'axios';",
            "import { useParams } from 'react-router-dom';",
            "import { __SHARED_IMPORTS__ } from '__SHARED_MOD__';",
            "import { xhrJson } from '__API_XHR__';",
        ],
    },
];

function splitPreambleOnly(cfg) {
    const modDir = path.join(SPA, cfg.relDir);
    const srcPath = path.join(modDir, cfg.srcFile);
    const nest = pagesFolderName(cfg.srcFile);
    const pagesDir = path.join(modDir, nest, 'pages');
    const upSpa = '../'.repeat(nestDepthToSpa(nest));

    const text = fs.readFileSync(srcPath, 'utf8');
    if (!/\bexport function\b/.test(text)) {
        console.log('Skip:', cfg.relDir, cfg.srcFile);
        return;
    }
    const lines = text.split(/\r?\n/);
    const exportIdx = listExportFunctionStarts(lines);
    const first = exportIdx[0];
    const preambleRaw = lines.slice(0, first).join('\n');
    const pagePrefix = xhrRe.test(preambleRaw)
        ? `${preambleRaw.replace(xhrRe, `\nimport { xhrJson } from '${upSpa}api/xhrJson';\n`).trimEnd()}\n`
        : `${preambleRaw.trimEnd()}\n`;

    fs.mkdirSync(pagesDir, { recursive: true });
    const names = [];
    for (let i = 0; i < exportIdx.length; i++) {
        const start = exportIdx[i];
        const end = i + 1 < exportIdx.length ? exportIdx[i + 1] : lines.length;
        const block = lines.slice(start, end).join('\n');
        const m = lines[start].trim().match(/^export function (\w+)/);
        names.push(m[1]);
        fs.writeFileSync(path.join(pagesDir, `${m[1]}.jsx`), `${pagePrefix}\n${block}\n`, 'utf8');
    }
    const barrelRel = `./${nest.replace(/\\/g, '/')}/pages/`;
    const barrel = `${names.map((n) => `export { ${n} } from '${barrelRel}${n}';`).join('\n')}\n`;
    fs.writeFileSync(srcPath, barrel, 'utf8');
    console.log(cfg.relDir, cfg.srcFile, '->', names.length, 'pages (preamble-only)');
}

for (const m of MODULES) {
    if (m.sharedFile === null) splitPreambleOnly(m);
    else splitOne(m);
}
