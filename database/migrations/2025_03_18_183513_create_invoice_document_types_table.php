<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDocumentTypesTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // Ejemplo: "01", "03"
            $table->string('description', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_document_types');
    }
}
