<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFactusFieldsToOrdersTable extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Agrega columnas para almacenar datos específicos de la respuesta de Factus.
            $table->unsignedBigInteger('factus_bill_id')->nullable()->after('factus_response')->comment('ID de la factura devuelta por Factus');
            $table->string('factus_status')->nullable()->after('factus_bill_id')->comment('Estado de la factura (ej. 1 = Received/Validated)');
            $table->string('factus_bill_number')->nullable()->after('factus_status')->comment('Número de factura devuelto por Factus');
            $table->timestamp('factus_validated')->nullable()->after('factus_bill_number')->comment('Fecha y hora de validación de la factura en Factus');
            $table->text('factus_qr')->nullable()->after('factus_validated')->comment('QR devuelto por Factus');
        });
    }

    /**
     * Revierte la migración.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'factus_bill_id',
                'factus_status',
                'factus_bill_number',
                'factus_validated',
                'factus_qr'
            ]);
        });
    }
}
