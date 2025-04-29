<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberingRangesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('numbering_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('document');
            $table->string('prefix');
            $table->unsignedBigInteger('from');
            $table->unsignedBigInteger('to');
            $table->unsignedBigInteger('current');
            $table->string('resolution_number')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('technical_key')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('numbering_ranges');
    }
}
