<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTotalCostPriceTaxIncInOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('total_cost_price_tax_inc', 20, 2)->default(0.0)->change();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Si necesitas revertir, podrías quitar el default o establecerlo a otro valor
            $table->float('total_cost_price_tax_inc', 20, 2)->change();
        });
    }
}
