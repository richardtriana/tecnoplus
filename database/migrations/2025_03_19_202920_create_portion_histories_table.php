<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('portion_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portion_id');
            $table->string('movement'); // "in", "out", "adjustment"
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('portion_histories');
    }
}
