<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductIdentificationStandardsTable extends Migration
{
    public function up()
    {
        Schema::create('product_identification_standards', function (Blueprint $table) {
            $table->id(); // Este id corresponderá a los valores: 1, 2, 3, 4
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_identification_standards');
    }
}
