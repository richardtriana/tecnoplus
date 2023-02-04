<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCreditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payment_credits')) {
            Schema::create('payment_credits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id');
                $table->foreignId('order_id');
                $table->double('pay');
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->delete('cascade');
                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->delete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_credits');
    }
}
