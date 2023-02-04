<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('tax_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('product');
            $table->string('barcode', 20)->unique();
            $table->tinyInteger('type');
            $table->tinyInteger('state')->default(1);
            $table->decimal('cost_price_tax_exc', 30, 2);
            $table->decimal('cost_price_tax_inc', 30, 2);
            $table->decimal('gain', 30, 2);
            $table->decimal('sale_price_tax_exc', 30, 2);
            $table->decimal('sale_price_tax_inc', 30, 2);
            $table->decimal('wholesale_price_tax_exc', 30, 2);
            $table->decimal('wholesale_price_tax_inc', 30, 2);
            $table->tinyInteger('stock')->default('0');
            $table->decimal('quantity', 30, 2)->nullable();
            $table->decimal('minimum', 30, 2)->nullable();
            $table->decimal('maximum', 30, 2)->nullable();

            $table->foreign('category_id')
                ->references('id')
                ->on('categories');

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands');

            $table->foreign('tax_id')
                ->references('id')
                ->on('taxes')
                ->onUpdate('cascade');

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
        Schema::dropIfExists('products');
    }
}
