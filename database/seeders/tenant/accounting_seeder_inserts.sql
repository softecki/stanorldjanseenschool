-- AccountingSeeder equivalent: run after accounting_accounts and accounting_payment_methods tables exist (tenant DB).
-- Run in your tenant database.

-- ============================================
-- 1. Payment methods (accounting_payment_methods)
-- ============================================
INSERT IGNORE INTO accounting_payment_methods (name, description, is_active, created_at, updated_at) VALUES
('Cash', 'Cash payment', 1, NOW(), NOW()),
('Bank', 'Bank transfer', 1, NOW(), NOW()),
('M-Pesa', 'M-Pesa mobile money', 1, NOW(), NOW()),
('Airtel Money', 'Airtel Money', 1, NOW(), NOW()),
('Tigo Pesa', 'Tigo Pesa', 1, NOW(), NOW()),
('Control Number', 'Payment via control number', 1, NOW(), NOW());

-- ============================================
-- 2. Chart of accounts (accounting_accounts)
-- ============================================
INSERT IGNORE INTO accounting_accounts (name, code, type, parent_id, status, description, created_at, updated_at) VALUES
('Income', 'INC', 'income', NULL, 1, NULL, NOW(), NOW()),
('School Fees', 'INC-SF', 'income', NULL, 1, NULL, NOW(), NOW()),
('Transport Fees', 'INC-TF', 'income', NULL, 1, NULL, NOW(), NOW()),
('Admission Fees', 'INC-AF', 'income', NULL, 1, NULL, NOW(), NOW()),
('Uniform Sales', 'INC-US', 'income', NULL, 1, NULL, NOW(), NOW()),
('Other Income', 'INC-OT', 'income', NULL, 1, NULL, NOW(), NOW()),
('Expenses', 'EXP', 'expense', NULL, 1, NULL, NOW(), NOW()),
('Salaries', 'EXP-SAL', 'expense', NULL, 1, NULL, NOW(), NOW()),
('Transport Fuel', 'EXP-FUEL', 'expense', NULL, 1, NULL, NOW(), NOW()),
('Maintenance', 'EXP-MNT', 'expense', NULL, 1, NULL, NOW(), NOW()),
('Utilities', 'EXP-UT', 'expense', NULL, 1, NULL, NOW(), NOW()),
('Office Supplies', 'EXP-OF', 'expense', NULL, 1, NULL, NOW(), NOW()),
('Assets', 'AST', 'asset', NULL, 1, NULL, NOW(), NOW()),
('Bank Account', 'AST-BNK', 'asset', NULL, 1, NULL, NOW(), NOW()),
('Cash on Hand', 'AST-CSH', 'asset', NULL, 1, NULL, NOW(), NOW()),
('Mobile Money', 'AST-MOB', 'asset', NULL, 1, NULL, NOW(), NOW()),
('Liabilities', 'LIA', 'liability', NULL, 1, NULL, NOW(), NOW()),
('Loans', 'LIA-LN', 'liability', NULL, 1, NULL, NOW(), NOW()),
('Payables', 'LIA-PAY', 'liability', NULL, 1, NULL, NOW(), NOW());
