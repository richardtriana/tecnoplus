<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToTaxesTable extends Migration
{
    public function up()
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
