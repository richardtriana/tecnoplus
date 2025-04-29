<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationTypesTable extends Migration
{
    public function up()
    {
        Schema::create('operation_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // Ej: "20", "22"
            $table->string('description', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('operation_types');
    }
}
