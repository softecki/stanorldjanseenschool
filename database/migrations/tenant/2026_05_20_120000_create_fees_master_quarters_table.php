<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees_master_quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fees_master_id')->constrained('fees_masters')->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['fees_master_id', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_master_quarters');
    }
};
