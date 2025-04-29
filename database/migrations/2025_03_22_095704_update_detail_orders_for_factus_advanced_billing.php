<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDetailOrdersForFactusAdvancedBilling extends Migration
{
    public function up()
    {
        Schema::table('detail_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_orders', 'tax_rate')) {
                // Almacena el porcentaje de impuesto (por ejemplo "19.00")
                $table->string('tax_rate')->nullable()->after('discount_price');
            }
        });
    }

    public function down()
    {
        Schema::table('detail_orders', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
}
