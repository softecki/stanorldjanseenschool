<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Split allocation of a fees_collect transaction across quarters / assign lines.
     * Used by FeesCollectRepository (processOtherFees, checkAdmissionFees, etc.).
     */
    public function up(): void
    {
        if (Schema::hasTable('fees_tracking')) {
            return;
        }

        Schema::create('fees_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fees_collect_id')->index();
            $table->decimal('amount', 16, 2)->default(0);
            $table->unsignedBigInteger('fees_assign_children_id')->index();
            /** @see FeesCollectRepository — raw INSERT uses camelCase identifier */
            $table->unsignedTinyInteger('statusId')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_tracking');
    }
};
