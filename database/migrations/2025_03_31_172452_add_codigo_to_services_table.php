<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodigoToServicesTable extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            // Agrega la columna 'codigo' después de 'id'
            $table->string('codigo', 100)->after('id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            // Elimina la columna 'codigo'
            $table->dropColumn('codigo');
        });
    }
}
