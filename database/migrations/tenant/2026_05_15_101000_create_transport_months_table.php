<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-month transport billing rows (one per months_list entry per fees_assign_children line).
     */
    public function up(): void
    {
        if (Schema::hasTable('transport_months')) {
            return;
        }

        Schema::create('transport_months', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fee_assign_children_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedTinyInteger('month');
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('1');
            $table->string('state', 20)->default('1');
            $table->timestamps();

            $table->index('student_id');
            $table->index('fee_assign_children_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_months');
    }
};
