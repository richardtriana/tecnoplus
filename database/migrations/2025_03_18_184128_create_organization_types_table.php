<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationTypesTable extends Migration
{
    public function up()
    {
        Schema::create('organization_types', function (Blueprint $table) {
            $table->id(); // Valores: 1, 2, etc.
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('organization_types');
    }
}
