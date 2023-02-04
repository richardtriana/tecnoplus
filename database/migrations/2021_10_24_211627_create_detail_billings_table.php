<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_billings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_id');
            $table->unsignedBigInteger('product_id');
            $table->string('barcode');
            $table->float('discount_percentage', 20, 4);
            $table->float('discount_price', 20, 4);
            $table->float('price_tax_exc', 20, 4);
            $table->float('price_tax_inc', 20, 4);
            $table->float('price_tax_inc_total', 20, 4);
            $table->float('quantity', 20, 4);
            $table->string('product');

            $table->foreign('billing_id')
                ->references('id')
                ->on('billings')
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
        Schema::dropIfExists('detail_billings');
    }
}
