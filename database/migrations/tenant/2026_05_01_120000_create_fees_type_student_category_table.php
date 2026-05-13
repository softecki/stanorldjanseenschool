<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fees_types') || ! Schema::hasTable('student_categories')) {
            return;
        }

        if (Schema::hasTable('fees_type_student_category')) {
            return;
        }

        Schema::create('fees_type_student_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fees_type_id')->constrained('fees_types')->cascadeOnDelete();
            $table->foreignId('student_category_id')->constrained('student_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['fees_type_id', 'student_category_id'], 'fees_type_student_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_type_student_category');
    }
};
