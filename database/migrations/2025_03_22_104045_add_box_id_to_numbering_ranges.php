<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoxIdToNumberingRanges extends Migration
{
    public function up()
    {
        Schema::table('numbering_ranges', function (Blueprint $table) {
            $table->unsignedBigInteger('box_id')->after('prefix')->nullable();
        });
    }

    public function down()
    {
        Schema::table('numbering_ranges', function (Blueprint $table) {
            $table->dropColumn('box_id');
        });
    }
}
