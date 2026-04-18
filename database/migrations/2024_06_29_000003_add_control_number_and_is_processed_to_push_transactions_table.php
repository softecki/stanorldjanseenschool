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
            $table->string('control_number')->nullable();
            $table->boolean('is_processed')->default(0);
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
            $table->dropColumn(['control_number', 'is_processed']);
        });
    }
};
