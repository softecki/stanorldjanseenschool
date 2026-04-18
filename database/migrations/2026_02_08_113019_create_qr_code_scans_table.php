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
        Schema::create('qr_code_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('control_number');
            $table->dateTime('scan_date');
            $table->integer('scan_month');
            $table->integer('scan_year');
            $table->boolean('is_before_april')->default(false);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->boolean('is_paid_complete')->default(false);
            $table->timestamps();

            $table->index('student_id');
            $table->index('control_number');
            $table->index('scan_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qr_code_scans');
    }
};
