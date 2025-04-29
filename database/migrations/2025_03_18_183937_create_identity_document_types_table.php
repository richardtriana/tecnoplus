<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdentityDocumentTypesTable extends Migration
{
    public function up()
    {
        Schema::create('identity_document_types', function (Blueprint $table) {
            $table->id(); // Los valores: 1, 2, 3, ...
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('identity_document_types');
    }
}
