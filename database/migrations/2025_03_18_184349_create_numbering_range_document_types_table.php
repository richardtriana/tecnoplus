<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberingRangeDocumentTypesTable extends Migration
{
    public function up()
    {
        Schema::create('numbering_range_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // Ej: "21", "22", etc.
            $table->string('description', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('numbering_range_document_types');
    }
}
