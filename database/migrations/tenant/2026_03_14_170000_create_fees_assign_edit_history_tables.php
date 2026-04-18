<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fees_assign_edit_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_fees_assign_id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('classes_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('fees_group_id')->nullable();
            $table->timestamp('history_created_at');
            $table->timestamps();
        });

        Schema::create('fees_assign_children_edit_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fees_assign_edit_history_id')->constrained('fees_assign_edit_history')->cascadeOnDelete();
            $table->unsignedBigInteger('original_fees_assign_children_id');
            $table->unsignedBigInteger('fees_assign_id')->nullable();
            $table->unsignedBigInteger('fees_master_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->decimal('fees_amount', 16, 2)->nullable();
            $table->decimal('paid_amount', 16, 2)->nullable();
            $table->decimal('remained_amount', 16, 2)->nullable();
            $table->string('control_number')->nullable();
            $table->decimal('quater_one', 16, 2)->nullable();
            $table->decimal('quater_two', 16, 2)->nullable();
            $table->decimal('quater_three', 16, 2)->nullable();
            $table->decimal('quater_four', 16, 2)->nullable();
            $table->timestamp('history_created_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fees_assign_children_edit_history');
        Schema::dropIfExists('fees_assign_edit_history');
    }
};
