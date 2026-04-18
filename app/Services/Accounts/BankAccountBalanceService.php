<?php

namespace App\Services\Accounts;

use Illuminate\Support\Facades\DB;

/**
 * Updates bank account balance when money goes in (fee collection, income)
 * or out (expense). Use the same connection as your tenant/app so that
 * bank_accounts table is the one you use (central or tenant).
 */
class BankAccountBalanceService
{
    /**
     * Credit the bank account (money in) - e.g. fee collection, other income.
     * Uses default DB connection (tenant or central depending on your setup).
     */
    public static function credit(?int $bankAccountId, float $amount): bool
    {
        if (!$bankAccountId || $amount <= 0) {
            return false;
        }
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('bank_accounts') || !\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'balance')) {
                return true; // skip if migrations not run yet
            }
            DB::table('bank_accounts')
                ->where('id', $bankAccountId)
                ->increment('balance', $amount);
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Debit the bank account (money out) - e.g. expense.
     * If balance column missing, skips update. Otherwise decrements (allows negative if balance < amount).
     */
    public static function debit(?int $bankAccountId, float $amount): bool
    {
        if (!$bankAccountId || $amount <= 0) {
            return false;
        }
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('bank_accounts') || !\Illuminate\Support\Facades\Schema::hasColumn('bank_accounts', 'balance')) {
                return true;
            }
            DB::table('bank_accounts')
                ->where('id', $bankAccountId)
                ->decrement('balance', $amount);
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * Reverse a previous credit (e.g. when deleting a fee collection or income).
     */
    public static function reverseCredit(?int $bankAccountId, float $amount): bool
    {
        return self::debit($bankAccountId, $amount);
    }

    /**
     * Reverse a previous debit (e.g. when deleting an expense).
     */
    public static function reverseDebit(?int $bankAccountId, float $amount): bool
    {
        return self::credit($bankAccountId, $amount);
    }
}
