<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archives fees_assign_children rows removed during student edit (soft audit trail).
     * Name matches project convention requested as "delete_student".
     */
    public function up(): void
    {
        if (Schema::hasTable('delete_student')) {
            return;
        }

        Schema::create('delete_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('context', 128)->default('student_edit');
            $table->unsignedBigInteger('original_fees_assign_children_id')->nullable();
            $table->unsignedBigInteger('fees_assign_id')->nullable();
            $table->unsignedBigInteger('fees_master_id')->nullable();
            $table->decimal('fees_amount', 16, 2)->nullable();
            $table->decimal('paid_amount', 16, 2)->nullable();
            $table->decimal('remained_amount', 16, 2)->nullable();
            $table->longText('row_json')->nullable();
            $table->timestamp('archived_at');
            $table->timestamps();

            $table->index('student_id');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delete_student');
    }
};
