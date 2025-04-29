<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentFieldsToOrders extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_form_id')->nullable()->after('observations');
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('payment_form_id');
            $table->json('factus_response')->nullable()->after('payment_method_id');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_form_id', 'payment_method_id', 'factus_response']);
        });
    }
}
