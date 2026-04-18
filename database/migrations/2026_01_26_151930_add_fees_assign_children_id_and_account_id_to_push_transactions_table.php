<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('push_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('push_transactions', 'fees_assign_children_id')) {
                $table->unsignedBigInteger('fees_assign_children_id')->nullable()->after('is_processed');
            }
            if (!Schema::hasColumn('push_transactions', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->default(0)->after('fees_assign_children_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('push_transactions', 'fees_assign_children_id')) {
                $table->dropColumn('fees_assign_children_id');
            }
            if (Schema::hasColumn('push_transactions', 'account_id')) {
                $table->dropColumn('account_id');
            }
        });
    }
};

