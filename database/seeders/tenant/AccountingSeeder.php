<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Accounts\AccountingAccount;
use App\Models\Accounts\PaymentMethod;

class AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPaymentMethods();
        $this->seedChartOfAccounts();
    }

    private function seedPaymentMethods(): void
    {
        $methods = [
            ['name' => 'Cash', 'description' => 'Cash payment'],
            ['name' => 'Bank', 'description' => 'Bank transfer'],
            ['name' => 'M-Pesa', 'description' => 'M-Pesa mobile money'],
            ['name' => 'Airtel Money', 'description' => 'Airtel Money'],
            ['name' => 'Tigo Pesa', 'description' => 'Tigo Pesa'],
            ['name' => 'Control Number', 'description' => 'Payment via control number'],
        ];
        foreach ($methods as $m) {
            PaymentMethod::firstOrCreate(
                ['name' => $m['name']],
                ['description' => $m['description'], 'is_active' => 1]
            );
        }
    }

    private function seedChartOfAccounts(): void
    {
        $accounts = [
            // Income
            ['name' => 'Income', 'code' => 'INC', 'type' => 'income', 'parent_id' => null],
            ['name' => 'School Fees', 'code' => 'INC-SF', 'type' => 'income', 'parent_id' => null],
            ['name' => 'Transport Fees', 'code' => 'INC-TF', 'type' => 'income', 'parent_id' => null],
            ['name' => 'Admission Fees', 'code' => 'INC-AF', 'type' => 'income', 'parent_id' => null],
            ['name' => 'Uniform Sales', 'code' => 'INC-US', 'type' => 'income', 'parent_id' => null],
            ['name' => 'Other Income', 'code' => 'INC-OT', 'type' => 'income', 'parent_id' => null],
            // Expenses
            ['name' => 'Expenses', 'code' => 'EXP', 'type' => 'expense', 'parent_id' => null],
            ['name' => 'Salaries', 'code' => 'EXP-SAL', 'type' => 'expense', 'parent_id' => null],
            ['name' => 'Transport Fuel', 'code' => 'EXP-FUEL', 'type' => 'expense', 'parent_id' => null],
            ['name' => 'Maintenance', 'code' => 'EXP-MNT', 'type' => 'expense', 'parent_id' => null],
            ['name' => 'Utilities', 'code' => 'EXP-UT', 'type' => 'expense', 'parent_id' => null],
            ['name' => 'Office Supplies', 'code' => 'EXP-OF', 'type' => 'expense', 'parent_id' => null],
            // Assets
            ['name' => 'Assets', 'code' => 'AST', 'type' => 'asset', 'parent_id' => null],
            ['name' => 'Bank Account', 'code' => 'AST-BNK', 'type' => 'asset', 'parent_id' => null],
            ['name' => 'Cash on Hand', 'code' => 'AST-CSH', 'type' => 'asset', 'parent_id' => null],
            ['name' => 'Mobile Money', 'code' => 'AST-MOB', 'type' => 'asset', 'parent_id' => null],
            // Liabilities
            ['name' => 'Liabilities', 'code' => 'LIA', 'type' => 'liability', 'parent_id' => null],
            ['name' => 'Loans', 'code' => 'LIA-LN', 'type' => 'liability', 'parent_id' => null],
            ['name' => 'Payables', 'code' => 'LIA-PAY', 'type' => 'liability', 'parent_id' => null],
        ];

        foreach ($accounts as $a) {
            AccountingAccount::firstOrCreate(
                ['code' => $a['code']],
                [
                    'name' => $a['name'],
                    'type' => $a['type'],
                    'parent_id' => $a['parent_id'],
                    'status' => 1,
                ]
            );
        }
    }
}
