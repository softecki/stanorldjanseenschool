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
        Schema::create('sms_campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('quarter')->comment('1=Q1, 2=Q2, 3=Q3, 4=Q4');
            $table->integer('total_students');
            $table->integer('sent_count');
            $table->integer('failed_count');
            $table->timestamps();
            
            $table->index('quarter');
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
        Schema::dropIfExists('sms_campaign_logs');
    }
};
