<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportDocumentItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('support_document_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            // Relación con el documento soporte
            $table->unsignedBigInteger('support_document_id');
            $table->foreign('support_document_id')->references('id')->on('support_documents')->onDelete('cascade');
            
            // Relación con el servicio
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');

            // Datos del item
            $table->string('code_reference'); // Código de referencia del producto o servicio
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->decimal('discount_rate', 8, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('gross_value', 12, 2)->default(0);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('is_excluded')->default(0);
            // Unidad de medida y código estándar
            $table->unsignedBigInteger('unit_measure_id')->nullable();
            $table->unsignedBigInteger('standard_code_id')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            // Retenciones se almacena en JSON
            $table->json('withholding_taxes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('support_document_items');
    }
}
