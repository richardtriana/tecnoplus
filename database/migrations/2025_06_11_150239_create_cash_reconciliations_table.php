<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashReconciliationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cash_reconciliations', function (Blueprint $table) {
            $table->id();

            // relaciones
            $table->foreignId('box_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('opening_user_id')
                  ->constrained('users');
            $table->foreignId('closing_user_id')
                  ->nullable()
                  ->constrained('users');

            // montos de apertura y movimientos
            $table->decimal('opening_balance', 15, 2);
            $table->decimal('entries',           15, 2)->default(0);
            $table->decimal('exits',             15, 2)->default(0);
            $table->decimal('credits',           15, 2)->default(0);

            // saldo calculado (apertura + ventas en efectivo)
            $table->decimal('computed_balance', 15, 2)
                  ->nullable()
                  ->comment('opening_balance + efectivo ventas');

            // valor reportado por el cajero al cerrar
            $table->decimal('reported_balance', 15, 2)
                  ->nullable()
                  ->comment('valor ingresado al cerrar');

            // diferencia entre lo reportado y lo calculado
            $table->decimal('difference',       15, 2)
                  ->nullable()
                  ->comment('reported_balance - computed_balance');

            // estado y fechas
            $table->boolean('is_open')
                  ->default(false)
                  ->comment('true=abierta, false=cerrada');
            $table->timestamp('opened_at')
                  ->useCurrent()
                  ->comment('fecha y hora de apertura');
            $table->timestamp('closed_at')
                  ->nullable()
                  ->comment('fecha y hora de cierre');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cash_reconciliations');
    }
}
