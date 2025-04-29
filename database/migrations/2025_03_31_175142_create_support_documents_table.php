<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('support_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('factus_support_document_id')->nullable();
            // Datos locales:
            $table->string('reference_code')->unique();
            $table->unsignedBigInteger('numbering_range_id')->nullable();
            $table->string('payment_method_code')->default('10');
            $table->text('observation')->nullable();
            // Relación con proveedor: se almacena el id y se crea clave foránea
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->foreign('provider_id')->references('id')->on('suppliers')->onDelete('set null');
            // Datos generales del documento retornados por Factus:
            $table->string('number')->nullable();  // Número del documento factus
            $table->tinyInteger('status')->nullable();
            $table->text('qr')->nullable();
            $table->text('cuds')->nullable();
            $table->string('validated')->nullable();
            $table->decimal('discount_rate', 8, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('gross_value', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->json('errors')->nullable();
            $table->text('qr_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('support_documents');
    }
}
