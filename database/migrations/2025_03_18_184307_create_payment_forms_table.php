<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentFormsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_forms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // Ej: "1", "2"
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_forms');
    }
}
