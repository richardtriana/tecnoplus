<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKitProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kit_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_parent_id')->references('id')->on('products');
            $table->foreignId('product_child_id')->references('id')->on('products');
            $table->string('product')->nullable();
            $table->string('barcode')->nullable();
            $table->float('quantity', 20, 2)->default(1);
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
        Schema::dropIfExists('kit_products');
    }
}
