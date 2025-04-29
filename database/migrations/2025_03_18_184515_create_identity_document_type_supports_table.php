<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentityDocumentTypeSupportsTable extends Migration
{
    public function up()
    {
        Schema::create('identity_document_type_supports', function (Blueprint $table) {
            $table->id(); // Valores: 4, 5, 6, 7, 8, 9, 10, 11
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('identity_document_type_supports');
    }
}
