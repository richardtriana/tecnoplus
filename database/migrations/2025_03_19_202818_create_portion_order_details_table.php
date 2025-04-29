<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortionOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('portion_order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portion_order_id');
            $table->unsignedBigInteger('portion_id');
            $table->string('movement'); // Por ejemplo: "ingreso", "salida", etc.
            $table->integer('quantity')->default(0); // Nueva columna para la cantidad
            $table->timestamps();

            $table->foreign('portion_order_id')
                  ->references('id')->on('portion_orders')
                  ->onDelete('cascade');

            $table->foreign('portion_id')
                  ->references('id')->on('portions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('portion_order_details');
    }
}
