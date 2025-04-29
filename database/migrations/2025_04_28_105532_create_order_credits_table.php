<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderCreditsTable extends Migration
{
    public function up()
    {
        Schema::create('order_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                  ->constrained()
                  ->onDelete('cascade');
            // Monto total de crédito otorgado a la orden
            $table->decimal('total_credit', 15, 2);
            // Saldo pendiente (total_credit - sum(abonos))
            $table->decimal('balance', 15, 2);
            // Estado: pendiente, parcial o pagado
            $table->enum('status', ['pending', 'partial', 'paid'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_credits');
    }
}
