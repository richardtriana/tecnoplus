<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCreditIdOnPaymentCredits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_credits', function (Blueprint $table) {
            if (Schema::hasColumn('payment_credits', 'credit_id')) {
                $table->dropColumn('credit_id');
            }

            if (!Schema::hasColumn('payment_credits', 'order_id')) {
                $table->foreignId('order_id');
                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders')
                    ->delete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
