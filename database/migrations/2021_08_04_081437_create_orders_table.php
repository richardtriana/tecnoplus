<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id');
      $table->foreignId('client_id')->nullable();
      $table->string('bill_number');
      $table->string('no_invoice')->rand(1, 10);
      $table->float('total_paid', 20, 2);
      $table->float('total_iva_inc', 20, 2);
      $table->float('total_iva_exc', 20, 2);
      $table->float('total_discount', 20, 2);
      $table->float('total_cost_price_tax_inc', 20, 2);
      $table->tinyInteger('state')->default('1');
      $table->dateTime('payment_date')->nullable();
      // $table->text('payment_methods')->nullable();

      $table->foreign('user_id')
        ->references('id')
        ->on('users');

      $table->foreign('client_id')
        ->references('id')
        ->on('clients');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('orders');
  }
}
