<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outstanding_fee_descriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fees_assign_children_id')->nullable();
            $table->string('source_format', 40)->nullable();
            $table->unsignedTinyInteger('line_no')->default(1); // 1..5
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index('student_id');
            $table->index('fees_assign_children_id');
            $table->unique(['student_id', 'fees_assign_children_id', 'line_no'], 'outstanding_desc_student_line_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outstanding_fee_descriptions');
    }
};

