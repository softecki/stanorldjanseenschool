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
        Schema::create('failed_sms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->string('phone_number', 20);
            $table->text('message');
            $table->string('reference')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 16, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->integer('status_code')->nullable();
            $table->text('error_response')->nullable();
            $table->tinyInteger('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->tinyInteger('is_sent')->default(0)->comment('0 = failed, 1 = sent');
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('phone_number');
            $table->index('is_sent');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_sms');
    }
};
