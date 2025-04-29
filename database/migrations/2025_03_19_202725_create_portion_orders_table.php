<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortionOrdersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('portion_orders', function (Blueprint $table) {
            $table->id();
            $table->text('detail')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->dateTime('date');
            $table->string('movement'); // "in", "out", "adjustment"
            $table->timestamps();

            // Clave foránea para user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('portion_orders');
    }
}
