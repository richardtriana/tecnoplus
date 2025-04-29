<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentNoteItemsTable extends Migration
{
    public function up()
    {
        Schema::create('adjustment_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adjustment_note_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('code_reference')->nullable();
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('discount_rate', 8, 2)->default(0);
            $table->decimal('price', 15, 2);
            $table->unsignedBigInteger('unit_measure_id')->nullable();
            $table->unsignedBigInteger('standard_code_id')->nullable();
            // Campo total para el ítem (opcional, si lo necesitas)
            $table->decimal('total', 15, 2)->default(0);
            $table->text('withholding_taxes')->nullable();
            $table->timestamps();

            $table->foreign('adjustment_note_id')->references('id')->on('adjustment_notes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('adjustment_note_items');
    }
}
