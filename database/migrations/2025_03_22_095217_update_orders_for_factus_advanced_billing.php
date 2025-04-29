<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersForFactusAdvancedBilling extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            // Campo para el código único de referencia (reference_code)
            $table->string('reference_code')->nullable()->after('no_invoice');
            // Campo para el ID del rango de numeración utilizado en Factus
            $table->unsignedBigInteger('numbering_range_id')->nullable()->after('reference_code');
            // Campo opcional para almacenar el código de documento (por ejemplo "01")
            $table->string('document_code')->nullable()->after('numbering_range_id');
            // Campos para almacenar la respuesta de Factus (datos del objeto "bill")
            // Se reutiliza bill_number si lo deseas; de lo contrario, se puede ajustar.
            // Se agrega el campo CUFE
            $table->string('cufe')->nullable()->after('bill_number');
            // Almacena la URL o dato del QR
            $table->text('qr')->nullable()->after('cufe');
            // Fecha y hora de validación (tal como "validated" en la respuesta)
            $table->timestamp('validated')->nullable()->after('qr');
            // Almacenar la imagen del QR (puede ser texto largo con base64)
            $table->text('qr_image')->nullable()->after('validated');
            // Campo para el código del método de pago enviado por Factus (por ejemplo "10")
            $table->string('payment_method_code')->nullable()->after('observations');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'reference_code',
                'numbering_range_id',
                'document_code',
                'cufe',
                'qr',
                'validated',
                'qr_image',
                'payment_method_code'
            ]);
        });
    }
}
