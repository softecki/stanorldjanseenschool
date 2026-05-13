<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional class link for a fee type (nullable; "None" in UI maps to null).
     */
    public function up(): void
    {
        if (! Schema::hasTable('fees_types')) {
            return;
        }
        if (Schema::hasColumn('fees_types', 'class_id')) {
            return;
        }
        Schema::table('fees_types', function (Blueprint $table) {
            $table->unsignedBigInteger('class_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('fees_types') || ! Schema::hasColumn('fees_types', 'class_id')) {
            return;
        }
        Schema::table('fees_types', function (Blueprint $table) {
            $table->dropColumn('class_id');
        });
    }
};
