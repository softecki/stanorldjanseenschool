# Accounting Module Upgrade

This document describes the upgraded Accounts module for accountant-friendly workflow: **income → expenses → reports → balances**.

## What Was Added

### 1. Chart of Accounts
- **Table:** `accounting_accounts` (tenant)
- **Fields:** name, code, type (income | expense | asset | liability), parent_id, status, description
- **Routes:** `/chart-of-accounts` (index, create, store, edit, update, delete)
- Use **Chart of Accounts** to define where money belongs (e.g. School Fees, Transport Fees, Salaries, Bank Account, Cash).

### 2. Payment Methods
- **Table:** `accounting_payment_methods` (tenant)
- **Routes:** `/payment-methods` (index, create, store, edit, update, delete)
- Examples: Cash, Bank, M-Pesa, Airtel Money, Tigo Pesa, Control Number.

### 3. Income & Expense Enhancements (optional columns)
- **Incomes:** `account_id`, `payment_method_id`, `reference`, `recorded_by` (added by migration; existing data unchanged).
- **Expenses:** `account_id`, `payment_method_id`, `vendor`, `approved_by`, `approved_at`, `expense_status` (pending | approved | paid).

### 4. Budgets
- **Table:** `accounting_budgets` (tenant): account_id, session_id, amount, notes.

### 5. Audit Logs
- **Table:** `accounting_audit_logs` (tenant): user_id, action, table_name, record_id, old_values, new_values, ip_address, created_at.
- Chart of Accounts create/update/delete are logged automatically.

### 6. Financial Dashboard
- **Route:** `/accounting/dashboard`
- Shows: Total Income, Total Expenses, Fees Collected, Balance, Today Income/Expense, Recent Income & Expenses.

### 7. Daily Cashbook
- **Route:** `/accounting/cashbook?date=YYYY-MM-DD`
- Lists fee payments, other income, and expenses for the selected date with In/Out/Balance.

### 8. Reports
- **Income Report:** `/accounting/reports/income?from=&to=`
- **Expense Report:** `/accounting/reports/expense?from=&to=`
- **Profit & Loss:** `/accounting/reports/profit-loss?from=&to=`

### 9. Audit Log Viewer
- **Route:** `/accounting/audit-log`

## How to Apply

### Run tenant migrations
For each tenant (or your tenant database), run:

```bash
php artisan tenants:migrate
```

If you use a single tenant or run migrations on the tenant DB directly:

```bash
php artisan migrate
```

(This runs all migrations in `database/migrations/tenant/` if your app is configured to use tenant migrations.)

### Seed default Chart of Accounts and Payment Methods (optional)
After migrations, seed default accounts and payment methods:

```bash
php artisan db:seed --class=Database\\Seeders\\Tenant\\AccountingSeeder
```

If you use multi-tenancy (e.g. stancl/tenancy), run the seeder per tenant:

```bash
php artisan tenants:run "db:seed --class=Database\\Seeders\\Tenant\\AccountingSeeder"
```

## Menu (Accounts section)

Under **Accounts** in the sidebar you now have:

- **Financial Dashboard** – overview and quick links
- **Chart of Accounts** – manage account categories
- **Payment Methods** – manage payment methods
- **Account Head** – existing income/expense heads
- **Income** – existing income entries
- **Expense** – existing expense entries

## Permissions

New routes reuse existing permissions:

- `account_head_read` – for Chart of Accounts and Payment Methods index
- `account_head_create` – for create/store
- `account_head_update` – for edit/update
- `account_head_delete` – for delete
- `income_read` / `expense_read` – for dashboard and reports

## Next Steps (optional)

1. **Income/Expense forms** – Add dropdowns for **Account** and **Payment Method** (from `accounting_accounts` and `accounting_payment_methods`) and set `recorded_by` on create.
2. **Expense approval** – Use `expense_status`, `approved_by`, `approved_at` in your expense workflow and list views.
3. **Student Ledger** – Build a per-student fee ledger (debit/credit/balance) from `fees_assign_childrens` and `fees_collects`.
4. **Budget tracking** – Use `accounting_budgets` to set limits and compare with actual income/expense by account.
5. **Export** – Add Excel/PDF export on report pages for accountants.
