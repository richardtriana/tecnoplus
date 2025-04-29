<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdjustmentNoteReasonsTable extends Migration
{
    public function up()
    {
        Schema::create('adjustment_note_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // Ej: "1", "2", etc.
            $table->string('description', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adjustment_note_reasons');
    }
}
