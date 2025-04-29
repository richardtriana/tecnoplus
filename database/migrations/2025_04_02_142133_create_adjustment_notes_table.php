<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentNotesTable extends Migration
{
    public function up()
    {
        Schema::create('adjustment_notes', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->unique();
            $table->unsignedBigInteger('numbering_range_id');
            $table->string('payment_method_code')->nullable();
            // Campo para almacenar el ID del documento soporte y su llave foránea
            $table->unsignedBigInteger('support_document_id');
            $table->foreign('support_document_id')
                  ->references('id')
                  ->on('support_documents')
                  ->onDelete('cascade');
            $table->string('correction_concept_code');
            $table->string('observation')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            // Campos para almacenar la respuesta de Factus
            $table->integer('status')->nullable();
            $table->string('number')->nullable();
            $table->text('qr')->nullable();
            $table->text('cuds')->nullable();
            $table->string('validated')->nullable();
            $table->text('qr_image')->nullable();
            $table->text('errors')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adjustment_notes');
    }
}
