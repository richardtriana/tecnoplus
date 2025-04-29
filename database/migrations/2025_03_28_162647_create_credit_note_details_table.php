<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditNoteDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('credit_note_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Relación con la nota crédito
            $table->unsignedBigInteger('credit_note_id');
            
            // Campos de los ítems enviados (según lo requerido por Factus)
            $table->string('code_reference');
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('discount_rate', 8, 2);
            $table->decimal('price', 12, 2);
            $table->string('tax_rate'); // Ej.: "19.00"
            $table->integer('unit_measure_id');
            $table->integer('standard_code_id');
            $table->tinyInteger('is_excluded'); // 0 o 1
            $table->integer('tribute_id');
            $table->json('withholding_taxes')->nullable();

            // Campos adicionales que pueden venir en la respuesta de Factus para cada ítem
            $table->string('discount')->nullable();
            $table->string('gross_value')->nullable();
            $table->string('taxable_amount')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('total')->nullable();

            $table->timestamps();

            // Llave foránea
            $table->foreign('credit_note_id')->references('id')->on('credit_notes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_note_details');
    }
}
