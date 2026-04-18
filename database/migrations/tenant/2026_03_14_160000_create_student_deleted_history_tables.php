<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_deleted_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_student_id');
            $table->string('admission_no')->nullable();
            $table->integer('roll_no')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->date('dob')->nullable();
            $table->date('admission_date')->nullable();
            $table->unsignedBigInteger('student_category_id')->nullable();
            $table->unsignedBigInteger('religion_id')->nullable();
            $table->unsignedBigInteger('blood_group_id')->nullable();
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->unsignedBigInteger('parent_guardian_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->longText('upload_documents')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('previous_school')->default(0);
            $table->text('previous_school_info')->nullable();
            $table->unsignedBigInteger('previous_school_image_id')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->string('cpr_no')->nullable();
            $table->string('spoken_lang_at_home')->nullable();
            $table->string('residance_address')->nullable();
            $table->timestamp('deleted_at');
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
        });

        Schema::create('student_fees_assign_deleted_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_deleted_history_id')->constrained('student_deleted_history')->cascadeOnDelete();
            $table->unsignedBigInteger('original_fees_assign_children_id');
            $table->unsignedBigInteger('original_student_id');
            $table->unsignedBigInteger('fees_assign_id')->nullable();
            $table->unsignedBigInteger('fees_master_id')->nullable();
            $table->decimal('fees_amount', 16, 2)->nullable();
            $table->decimal('paid_amount', 16, 2)->nullable();
            $table->decimal('remained_amount', 16, 2)->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();
        });

        Schema::create('student_fees_collect_deleted_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_deleted_history_id')->constrained('student_deleted_history')->cascadeOnDelete();
            $table->unsignedBigInteger('original_fees_collect_id');
            $table->unsignedBigInteger('original_student_id');
            $table->date('date')->nullable();
            $table->tinyInteger('payment_method')->nullable();
            $table->unsignedBigInteger('fees_assign_children_id')->nullable();
            $table->unsignedBigInteger('fees_collect_by')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->decimal('amount', 16, 2)->nullable();
            $table->decimal('fine_amount', 16, 2)->nullable();
            $table->timestamp('deleted_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_fees_collect_deleted_history');
        Schema::dropIfExists('student_fees_assign_deleted_history');
        Schema::dropIfExists('student_deleted_history');
    }
};
