# EduSoft

**EduSoft** is a school management system developed by **Softecki Group Company Limited** (Softecki Group). It helps primary and secondary schools, colleges, and training centres run day-to-day operations—academics, fees, finance, communication, and reporting—from one platform.

EduSoft combines a **Laravel 9** backend (REST-style JSON APIs, with Blade retained where legacy screens remain) and a **React (Vite) single-page application** for the staff admin experience, optional **multi-tenant (SaaS)** deployment, and a **public-facing website** for parents and visitors.

---

## Table of contents

1. [About EduSoft & Softecki Group](#about-edusoft--softecki-group)
2. [What you get (benefits)](#what-you-get-benefits)
3. [How the product works](#how-the-product-works)
4. [Technology stack](#technology-stack)
5. [Architecture overview](#architecture-overview)
6. [Feature areas](#feature-areas)
7. [Admin SPA: routes and modules](#admin-spa-routes-and-modules)
8. [UI components and shared frontend](#ui-components-and-shared-frontend)
9. [Backend structure](#backend-structure)
10. [Fees: quarters (optional split)](#fees-quarters-optional-split)
11. [Installation and configuration](#installation-and-configuration)
12. [Database migrations](#database-migrations)
13. [Building the frontend](#building-the-frontend)
14. [Notifications and queues](#notifications-and-queues)
15. [License and support](#license-and-origins)

---

## About EduSoft & Softecki Group

| | |
|--|--|
| **Product** | EduSoft — school management system |
| **Publisher** | Softecki Group Company Limited |
| **Focus** | Academic structure, student records, fee billing and collection, accounting, examinations, attendance, communication (SMS/notice), certificates, reporting, and optional multi-school SaaS |

For licensing, deployment support, or customisation, contact **Softecki Group Company Limited**.

---

## What you get (benefits)

| Benefit | Description |
|--------|-------------|
| **Single operational hub** | Staff manage students, fees, exams, attendance, accounts, and communication from one authenticated admin app. |
| **Modern admin UI** | Primary workflows use a React SPA with consistent tables, forms, paging, and actions—faster navigation than full page reloads for many screens. |
| **Public web presence** | Marketing-style pages (home, about, news, events, notices, contact, results, online admission, policies, landing) for families and prospects. |
| **Role-based access** | Laravel permissions gate routes and actions; sensitive operations can be demo-protected. |
| **Financial visibility** | Chart of accounts, income/expense, cash, invoices, payments, deposits, suppliers, products/items, and reporting—including fees collection and reconciliation-style reports. |
| **Academic continuity** | Classes, sections, subjects, routines, exams, marks registers, and printable-style reports (marksheet, merit list, duplicates, boarding lists, etc.). |
| **Multi-school SaaS (optional)** | Stancl Tenancy supports isolated tenant databases when `APP_SAAS=true`; single-school mode uses tenant migrations on one database. |

---

## How the product works

1. **Browser** loads the Laravel app. Authenticated staff are served the **SPA shell** (Vite-built JS/CSS) mounted on a route such as `/app` (see your `routes/web.php` and Blade entry).
2. **React Router** (`resources/js/spa/App.jsx`) maps URL paths to **page components**. Each page calls **Laravel HTTP routes** with `Accept: application/json` (see `resources/js/spa/api/xhrJson.js`) so controllers return **JSON** (`meta`, `data`, messages) instead of Blade for those requests.
3. **Business logic** lives in **Controllers** (thin) + **Repositories** + **Models** + **Form requests** for validation. Fees, students, and accounts follow this pattern extensively.
4. **Session context** (e.g. current academic session via `setting('session')`) scopes many fee and master lists so data stays relevant to the active year.
5. **Parents and students** can use dedicated panel routes (some fully bespoke React pages, others JSON-backed `EndpointDataPage` placeholders while legacy APIs are bridged).

---

## Technology stack

| Layer | Technologies |
|-------|----------------|
| Backend | PHP 8+, Laravel 9, Eloquent, migrations, policies/middleware |
| Admin UI | React 19, React Router 7, Axios, Tailwind CSS 4, Vite 3 |
| Auth | Laravel session / UI patterns; JWT package present for API-style use where configured |
| Documents | DomPDF, PhpSpreadsheet / Maatwebsite Excel |
| Payments | Stripe, PayPal (Srmklive), school-specific gateways in settings |
| Messaging | Twilio SDK; SMS templates and campaigns in-app |
| Multi-tenancy | `stancl/tenancy` |
| Optional | AWS S3 filesystem, reCAPTCHA, QR codes, image intervention |

---

## Architecture overview

```
┌─────────────────────────────────────────────────────────────┐
│                        Browser                               │
├──────────────────────────┬──────────────────────────────────┤
│   Public React routes    │   Admin React + AdminLayout       │
│   (/, /news, /login…)   │   (/dashboard, /students, …)      │
└────────────┬─────────────┴──────────────┬───────────────────┘
             │                            │
             ▼                            ▼
┌────────────────────────────  Laravel routes  ────────────────────────────┐
│  web.php, fees.php, …  →  Controllers  →  Repositories  →  Models / DB     │
│  Middleware: auth, permissions, tenancy, XSS, subscription, …            │
└──────────────────────────────────────────────────────────────────────────┘
             │
             ▼
┌────────────────────────────  MySQL (per tenant or single DB)  ──────────┘
```

- **`resources/js/spa/`** — React source: `App.jsx`, feature folders (`fees/`, `students/`, `accounts/`, …), `layout/AdminLayout.jsx`, `ui/UiKit.jsx`, `public/` for the marketing site.
- **`public/build/`** — Vite output (manifest + hashed assets); production serves these.

---

## Feature areas

### Public website (unauthenticated visitors)

- **Home** with sliders, counters, gallery, news, events (when configured).
- **About, news list & detail, events list & detail, notices list & detail.**
- **Contact**, **exam results lookup**, **CMS-style dynamic pages** (`/page/:slug`).
- **Online admission** and **admission fee** payment flow.
- **Landing** and **privacy policy** pages.

### Student information

- Student CRUD, profile, categories, **bulk upload**, **fees update** tools.
- **Parents / guardians** management.
- **Promote students** between classes/sessions.
- **Deleted student history** for audit/recovery awareness.

### Fees

- **Groups** → **Types** → **Masters** (amount, session, fines) with optional **Quarters** tab: custom Q1–Q4 amounts per master (total = sum); otherwise assignments use **amount ÷ 4** per quarter when due-date rules apply.
- **Assignments** link students to fee structures; **collections** record payments; **cancelled** collections, **transactions**, **online transactions**, **amendments**.

### Examinations

- Marks grades, exam assign, marks register (create/edit/view), examination settings hub.

### Academic structure

- **Classes**, **sections**, **class setup**, **subjects**, **subject assign**, **shifts**, **class rooms**, **class routines**, **exam routines**, **time schedules**.

### Attendance

- Index, reports, notifications.

### Accounts (double-entry style operations)

- **Accounting home**, **chart of accounts**, **account heads**, **payment methods**, **income**, **expense**, **cash**, **deposits**, **payments**, **transactions**, **suppliers**, **invoices**, **products**, **items**, **accounting dashboard**.
- **Bank accounts** (institution accounts).

### Reports

- Dedicated pages for **marksheet**, **merit list**, **duplicate students**, **boarding students**, **account** summaries, **fees collection**, **fees summary**, **outstanding breakdown**, **students**, **fees by year** (with student drill-down), **bank reconciliation** (report + process UI).
- Additional **ReportEntryPage** routes for progress card, due fees, routines, accounting income/expense/P&L, cashbook, audit log, etc.

### Communication

- Hub plus **notice board**, **SMS templates**, **SMS mail**, **SMS campaigns**.

### Certificates & ID cards

- Certificate CRUD + generate; alternate **certificate UI** flow; **ID card** list/create/edit/generate.

### Staff & access control

- **Staff hub**, **users**, **roles**, **departments**, **designations**, **salary batch processing**.

### Settings

- **Settings home**; **general**, **notification**, **storage**, **task schedulers**, **software update**, **reCAPTCHA**, **SMS**, **payment gateway**, **email (SMTP)**.
- Reference data: **genders**, **religions**, **sessions**, **blood groups**, **languages** (and terms).

### Library, homework, goods, orders, live class

- **Library**, **homework**, **goods**, **orders**, **Google Meet** live class lists.

### Panels (parent / student)

- Dashboards, attendance, routines, subjects, homework, fees, marksheet, notices, books, online exams (where implemented)—mix of SPA pages and JSON hub pages.

### Legacy / developer utilities

- **BackendViewHub**, **CommonViewHub**, **ErrorsHub**, **FrontendHub**, **PanelHub**, **GenericMigratedPage**, **EndpointDataPage** for incremental migration from Blade to SPA.

---

## Admin SPA: routes and modules

The source of truth for URLs is **`resources/js/spa/App.jsx`**. High-level groupings:

| Path prefix (examples) | Module folder (under `spa/`) |
|-------------------------|--------------------------------|
| `/dashboard` | `dashboard/` |
| `/students`, `/parents`, `/categories`, `/promote`, `/deleted-history` | `students/` |
| `/collections`, `/assignments`, `/types`, `/groups`, `/masters`, `/transactions`, … | `fees/` |
| `/examination/*` | `examination/` |
| `/classes`, `/sections`, `/subjects`, `/class-rooms`, … | `academic/` |
| `/accounting`, `/chart-of-accounts`, `/income`, `/expense`, … | `accounts/` + `AccountExtraPages` |
| `/attendance/*` | `attendance/` |
| `/communication/*` | `communication/` |
| `/reports/*` | `reports/` |
| `/settings`, `/settings/*` | `settings/` |
| `/staff/*`, `/users`, `/roles` | `staff/` |
| `/certificate*`, `/idcard` | `certificate/`, `idcard/` |
| `/homework`, `/library`, `/goods`, `/orders`, `/liveclass/gmeet` | respective folders |
| `/banks-accounts`, `/blood-groups` | `banks/`, `settings/BloodGroup*` |
| `/my/profile`, `/my/password` | `profile/` |
| `/login`, `/register`, … | `auth/` |

Legacy **`/fees/...`** paths often **`Navigate`** to the shorter SPA paths (e.g. `/collections`).

---

## UI components and shared frontend

| Asset | Role |
|-------|------|
| **`resources/js/spa/ui/UiKit.jsx`** | Shared controls: buttons, tables, pagers, action icon groups, loaders, etc. |
| **`resources/js/spa/layout/AdminLayout.jsx`** | Shell: sidebar navigation, header, user menu, permission-aware links. |
| **`resources/js/spa/fees/FeesModuleShared.jsx`** | `EntityListPage`, `EntityViewPage`, `normalizeFeesPagedList`, and other fees list patterns (`skipLayout` for tabbed pages). |
| **`resources/js/spa/public/PublicLayout.jsx`** | Public site chrome, footer, subscribe form. |
| **`resources/js/spa/public/PublicUi.jsx`** | Section headers, cards, CTA bands, feature grids for marketing pages. |

Styling is primarily **Tailwind** utility classes.

---

## Backend structure

| Path | Role |
|------|------|
| `app/Http/Controllers/` | HTTP layer; JSON + redirect responses. |
| `app/Repositories/` | Query and transaction orchestration. |
| `app/Models/` | Eloquent models (incl. `Fees/`, `StudentInfo/`, `Accounts/`, …). |
| `app/Http/Requests/` | Input validation. |
| `app/Interfaces/` | Contracts for repositories. |
| `routes/web.php`, `routes/fees.php`, … | Route registration; fees routes often grouped with middleware. |
| `database/migrations/` | Central migrations. |
| `database/migrations/tenant/` | **Tenant** migrations (school-specific schema; fees master quarters, assign children, etc.). |

---

## Fees: quarters (optional split)

- Table **`fees_master_quarters`** stores up to four rows per **fee master** (Q1–Q4 amounts).
- **Saving quarters** updates the master’s **`amount`** to the **sum** of the four quarters.
- When **assigning** students to fees, if the master has **all four** quarter rows, **`FeesMasterQuarter::resolvedQuarterAmounts`** feeds `quater_one` … `quater_four` on `fees_assign_childrens`; otherwise the system uses **total ÷ 4** (when the existing due-date threshold logic applies).

---

## Installation and configuration

1. **Requirements:** PHP 8+, Composer, Node.js + npm, MySQL/MariaDB.
2. Clone the project and run:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

3. Configure **`.env`**: database, `APP_URL`, mail, optional `APP_SAAS`, `APP_MAIN_APP_URL`, cache/queue drivers, payment keys, etc.
4. Run **migrations** (see below) and seed if you use project seeders.
5. **Link storage** if you serve uploads: `php artisan storage:link`.

---

## Database migrations

### Single-school (typical local / one database)

- Set `APP_SAAS=false` in `.env`.
- For tenant schema on one database (as used in this project for school tables):

```bash
php artisan migrate:fresh --seed --path=database/migrations/tenant
```

- `CACHE_DRIVER=array` is often used locally.
- Notifications: by default jobs may run synchronously; with `NOTIFICATION_JOB=queue`, queue workers should process notification jobs.

### SaaS (multi-tenant)

- Set `APP_SAAS=true` and `APP_MAIN_APP_URL` to your central domain.
- Central + module migrations depend on your `nwidart/laravel-modules` setup, for example:

```bash
php artisan migrate:fresh --path=modules/MainApp/database/migrations
php artisan module:seed MainApp
```

Or module migrate-fresh variants:

```bash
php artisan module:migrate-fresh --seed MainApp
```

Adjust commands to match your deployment playbook.

### PHP binary alias

If `php` is not on PATH, use your local binary (e.g. `php8`) the same way.

---

## Building the frontend

```bash
npm install
npm run dev
# production asset build:
npm run build
```

Vite writes to **`public/build/`**. Ensure your Laravel layout references the Vite manifest so the SPA loads the latest bundle.

---

## Notifications and queues

- When **`NOTIFICATION_JOB=queue`**, run a **queue worker** (`php artisan queue:work`) so mail/SMS notifications are delivered reliably.
- Otherwise many notifications run inline (simpler for small installs).

---

## License and support

**EduSoft** is proprietary software by **Softecki Group Company Limited**. Redistribution, white-labelling, and hosting terms are governed by your agreement with Softecki Group.

The codebase may include **third-party** Laravel, npm, and template-derived assets; comply with their respective licenses when distributing or modifying bundles.

**Product support:** Softecki Group Company Limited (per your service or licence agreement).

**Internal development:** use your issue tracker and code review process for bugs and features.

**SPA conventions:** Axios + `xhrJson`, repository-shaped JSON responses, and `AdminLayout` for staff screens.
