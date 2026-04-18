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
        Schema::create('cash_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('total_amount_collected')->nullable();
            $table->string('total_amount_deposited')->nullable();
            $table->string('total_amount_remained')->nullable();
            $table->timestamp('last_collected_date')->nullable();
            $table->timestamp('last_deposited_date')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_collected_history', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('amount_collected')->nullable();
            $table->string('source')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
        Schema::create('cash_deposits_history', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('amount_deposited')->nullable();
            $table->string('account_id')->nullable();
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
        Schema::dropIfExists('cash_deposits');
        Schema::dropIfExists('cash_collected_history');
        Schema::dropIfExists('cash_deposits_history');
    }
};
