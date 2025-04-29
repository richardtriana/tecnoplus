<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeasurementUnitsTable extends Migration
{
    public function up()
    {
        Schema::create('measurement_units', function (Blueprint $table) {
            $table->id(); // Esto crea una columna unsignedBigInteger 'id'
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('measurement_units');
    }
}
