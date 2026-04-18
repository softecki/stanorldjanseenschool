<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transportations', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('liters_per_week')->nullable();
            $table->string('distance_per_week')->nullable();
            $table->timestamps();
        });
        Schema::create('filling_history', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('liters')->nullable();
            $table->string('amount')->nullable();
            $table->foreignId('upload_id')->nullable()->constrained('uploads')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transportations');
        Schema::dropIfExists('filling_history');
    }
};
