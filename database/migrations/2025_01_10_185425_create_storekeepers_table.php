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
        Schema::create('storekeepers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('stock_overview', function (Blueprint $table) {
            $table->id();
            $table->string('goods_name');
            $table->decimal('stocked_amount', 10, 2);
            $table->decimal('available_stock', 10, 2);
            $table->decimal('consumed_amount', 10, 2);
            $table->string('unit_of_measure')->default('kg'); // Optional
            $table->string('category')->nullable(); // Optional
            $table->timestamps(); // Includes 'created_at' and 'updated_at'
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('goods_name');
            $table->decimal('quantity_ordered', 10, 2);
            $table->string('unit_of_measure')->default('kg');
            $table->enum('status', ['new', 'processed', 'delivered', 'completed', 'unpaid', 'paid'])->default('new');
            $table->string('supplier_name')->nullable();
            $table->timestamp('order_date')->useCurrent();
            $table->timestamp('delivery_date')->nullable();
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->text('comments')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Foreign key for user
            $table->timestamps(); // Includes 'created_at' and 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storekeepers');
        Schema::dropIfExists('stock_overview');
        Schema::dropIfExists('orders');
    }
};
