<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnviadoDianToNumberingRangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('numbering_ranges', function (Blueprint $table) {
            // Agrega la columna booleana "enviado_dian" con valor por defecto false
            $table->boolean('enviado_dian')->default(false)->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('numbering_ranges', function (Blueprint $table) {
            $table->dropColumn('enviado_dian');
        });
    }
}
