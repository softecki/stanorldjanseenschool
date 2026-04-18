<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fees_collects', function (Blueprint $table) {
            if (!Schema::hasColumn('fees_collects', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('session_id')->comment('bank_accounts.id');
            }
        });

        Schema::table('incomes', function (Blueprint $table) {
            if (!Schema::hasColumn('incomes', 'bank_account_id')) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('income_head')->comment('bank_accounts.id - account that received this income');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'bank_account_id')) {
                $table->unsignedBigInteger('bank_account_id')->nullable()->after('expense_head')->comment('bank_accounts.id - account that was debited for this expense');
            }
        });
    }

    public function down()
    {
        Schema::table('fees_collects', function (Blueprint $table) {
            if (Schema::hasColumn('fees_collects', 'account_id')) {
                $table->dropColumn('account_id');
            }
        });
        Schema::table('incomes', function (Blueprint $table) {
            if (Schema::hasColumn('incomes', 'bank_account_id')) {
                $table->dropColumn('bank_account_id');
            }
        });
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'bank_account_id')) {
                $table->dropColumn('bank_account_id');
            }
        });
    }
};
