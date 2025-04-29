<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientTributesTable extends Migration
{
    public function up()
    {
        Schema::create('client_tributes', function (Blueprint $table) {
            $table->id(); // Por ejemplo: 18, 21, etc.
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_tributes');
    }
}
