<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('barcode');
            $table->float('discount_percentage', 20, 4);
            $table->float('discount_price', 20, 4);
            $table->float('price_tax_exc', 20, 4);
            $table->float('price_tax_inc', 20, 4);
            $table->float('price_tax_inc_total', 20, 4);
            $table->float('cost_price_tax_inc', 20, 4);
            $table->float('cost_price_tax_inc_total', 20, 4);
            $table->float('quantity', 20, 4);
            $table->string('product');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products');

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
        Schema::dropIfExists('detail_orders');
    }
}
