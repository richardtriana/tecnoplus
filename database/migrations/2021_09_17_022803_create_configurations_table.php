<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('legal_representative', );
            $table->string('nit', 15);
            $table->string('address', 150)->default('Sin dirección');
            $table->string('email', 150)->nullable();
            $table->string('tax_regime')->nullable();
            $table->string('telephone', 15)->nullable();
            $table->string('mobile', 15)->nullable();
            $table->string('printer', 100)->nullable();
            $table->text('logo')->nullable();
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
        Schema::dropIfExists('configurations');
    }
}
