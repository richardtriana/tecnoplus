<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoxNumberingRangeTable extends Migration
{
    public function up()
    {
        Schema::create('box_numbering_range', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('box_id');
            $table->unsignedBigInteger('numbering_range_id');
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('box_id')->references('id')->on('boxes')->onDelete('cascade');
            $table->foreign('numbering_range_id')->references('id')->on('numbering_ranges')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('box_numbering_range');
    }
}
