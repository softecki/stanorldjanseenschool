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
        Schema::create('goods', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name'); // Name of the good (e.g., maize, beans)
            $table->string('unit')->default('kg'); // Unit of measurement
            $table->text('description')->nullable(); // Description of the good
            $table->integer('stocked_amount')->default(0); // Total stocked amount
            $table->integer('available_stock')->default(0); // Current available stock
            $table->integer('consumed_amount')->default(0); // Amount consumed
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods');
    }
};
