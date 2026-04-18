<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounting_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounting_accounts')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('sessions')->cascadeOnDelete();
            $table->decimal('amount', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['account_id', 'session_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounting_budgets');
    }
};
