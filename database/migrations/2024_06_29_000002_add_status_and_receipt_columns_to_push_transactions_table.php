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
        Schema::table('push_transactions', function (Blueprint $table) {
            $table->string('payment_status')->nullable();
            $table->string('settlement_status')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->string('settlement_receipt')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->dateTime('settlement_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('push_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'settlement_status',
                'payment_receipt',
                'settlement_receipt',
                'payment_date',
                'settlement_date',
            ]);
        });
    }
};
