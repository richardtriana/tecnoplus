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
        // Si es una instalación nueva, esta migración creará la tabla completa.
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('tax_id');

            // Datos básicos
            $table->string('product');
            $table->string('barcode', 20)->unique();

            // Tipo (fijado en 1 para "Por Unidad / Pieza") y estado
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('state')->default(1);

            // Precios y cálculos
            $table->decimal('cost_price_tax_exc', 30, 2);
            $table->decimal('cost_price_tax_inc', 30, 2);
            $table->decimal('gain', 30, 2)->default(0);
            $table->decimal('sale_price_tax_exc', 30, 2)->default(0);
            $table->decimal('sale_price_tax_inc', 30, 2);
            $table->decimal('sale_tax_value', 30, 2)->default(0);
            $table->decimal('wholesale_price_tax_exc', 30, 2)->default(0);
            $table->decimal('wholesale_price_tax_inc', 30, 2)->default(0);

            // Inventario
            $table->tinyInteger('stock')->default(0);
            $table->decimal('quantity', 30, 2)->nullable();
            $table->decimal('minimum', 30, 2)->nullable();
            $table->decimal('maximum', 30, 2)->nullable();

            // Nueva columna: Fecha de vencimiento
            $table->date('expiration_date')->nullable();

            // Nuevos campos de relaciones
            $table->unsignedBigInteger('measurement_unit_id')->nullable();
            $table->unsignedBigInteger('product_identification_standard_id')->nullable();

            // Indica si se usan porciones/ingredientes
            $table->boolean('uses_portions')->default(false);

            $table->timestamps();

            // Llaves foráneas
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');

            $table->foreign('tax_id')
                  ->references('id')
                  ->on('taxes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('measurement_unit_id')
                  ->references('id')
                  ->on('measurement_units')
                  ->onDelete('set null');

            $table->foreign('product_identification_standard_id')
                  ->references('id')
                  ->on('product_identification_standards')
                  ->onDelete('set null');
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
