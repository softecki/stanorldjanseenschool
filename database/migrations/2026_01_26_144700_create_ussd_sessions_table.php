<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ussd_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique()->index(); // USSD session ID
            $table->string('phone_number')->index();
            $table->string('current_menu')->nullable(); // Current menu state
            $table->text('session_data')->nullable(); // JSON data for session state
            $table->string('status')->default('active'); // active, completed, terminated
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();

            $table->index(['phone_number', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_sessions');
    }
};

