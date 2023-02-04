<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsecutiveBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consecutive_boxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('box_id');
            $table->integer('from_nro');
            $table->integer('until_nro');
            $table->date('from_date');
            $table->date('until_date');
            $table->timestamps();

            $table->foreign('box_id')
            ->references('id')
            ->on('boxes')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consecutive_boxes');
    }
}
