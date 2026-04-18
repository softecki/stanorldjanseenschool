<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (!Schema::hasColumn('incomes', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('id')->constrained('accounting_accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('incomes', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->after('amount')->constrained('accounting_payment_methods')->nullOnDelete();
            }
            if (!Schema::hasColumn('incomes', 'reference')) {
                $table->string('reference')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('incomes', 'recorded_by')) {
                $table->foreignId('recorded_by')->nullable()->after('description')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('id')->constrained('accounting_accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('expenses', 'payment_method_id')) {
                $table->foreignId('payment_method_id')->nullable()->after('amount')->constrained('accounting_payment_methods')->nullOnDelete();
            }
            if (!Schema::hasColumn('expenses', 'vendor')) {
                $table->string('vendor')->nullable()->after('description');
            }
            if (!Schema::hasColumn('expenses', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('expenses', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('expenses', 'expense_status')) {
                $table->string('expense_status', 20)->default('pending'); // pending, approved, paid
            }
        });
    }

    public function down()
    {
        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            }
            if (Schema::hasColumn('incomes', 'payment_method_id')) {
                $table->dropForeign(['payment_method_id']);
                $table->dropColumn('payment_method_id');
            }
            if (Schema::hasColumn('incomes', 'recorded_by')) {
                $table->dropForeign(['recorded_by']);
                $table->dropColumn('recorded_by');
            }
            if (Schema::hasColumn('incomes', 'reference')) {
                $table->dropColumn('reference');
            }
        });
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            }
            if (Schema::hasColumn('expenses', 'payment_method_id')) {
                $table->dropForeign(['payment_method_id']);
                $table->dropColumn('payment_method_id');
            }
            if (Schema::hasColumn('expenses', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            foreach (['vendor', 'approved_at', 'expense_status'] as $col) {
                if (Schema::hasColumn('expenses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
