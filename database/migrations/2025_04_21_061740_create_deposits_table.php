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
         Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('price')->nullable();
            $table->string('cost')->nullable();
            $table->string('unit')->nullable();
            $table->string('quantity')->nullable();
            $table->timestamps();
        });

        Schema::create('stocks_in', function (Blueprint $table) {
            $table->id();
            $table->string('item_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('quantity')->nullable();
            $table->string('supplier')->nullable();
            $table->string('price')->nullable(); 
            $table->string('total_cost')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
        Schema::create('stocks_out', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('item_id')->nullable();
            $table->string('quantity')->nullable();
            $table->string('supplier')->nullable();
            $table->string('price')->nullable();
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
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('stocks_in');
        Schema::dropIfExists('stocks_out');
    }
};
