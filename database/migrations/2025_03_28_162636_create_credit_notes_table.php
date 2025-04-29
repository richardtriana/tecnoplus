<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditNotesTable extends Migration
{
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            // Relaciones con la orden y el cliente (el cliente es el mismo de la orden)
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            
            // Datos enviados en la solicitud
            $table->integer('numbering_range_id');
            $table->string('correction_concept_code');  // Ej.: "2" (anulación de factura electrónica)
            $table->string('customization_id');         // "20": con factura; "22": sin factura
            $table->integer('bill_id')->nullable();     // Permitido nulo en caso de notas sin factura
            $table->string('reference_code');           // Código único para la nota crédito
            $table->text('observation')->nullable();
            $table->string('payment_method_code');
            $table->json('items')->nullable();          // Se puede guardar el array de ítems (opcional)
            
            // Respuesta de Factus (almacenada de forma parcial en campos individuales)
            $table->json('factus_response')->nullable();
            $table->string('number')->nullable();       // Número asignado a la nota (ej.: "NC76")
            $table->integer('status')->nullable();        // Estado asignado a la nota (ej.: 1)
            $table->tinyInteger('send_email')->nullable();       // 0 o 1
            $table->string('qr')->nullable();                    // URL de consulta QR
            $table->string('cude')->nullable();                  // CUDE
            $table->string('validated')->nullable();             // Fecha validada (ej.: "30-03-2025 12:03:14 PM")
            $table->string('discount_rate')->nullable();         // Ej.: "0.00"
            $table->string('discount')->nullable();              // Ej.: "0.00"
            $table->string('gross_value')->nullable();           // Ej.: "43277.31"
            $table->string('taxable_amount')->nullable();        // Ej.: "43277.31"
            $table->string('tax_amount')->nullable();            // Ej.: "8222.69"
            $table->string('total')->nullable();                 // Ej.: "51500.00"
            $table->json('errors')->nullable();                  // Array de errores de validación de Factus
            $table->text('qr_image')->nullable();                // Imagen en base64

            $table->timestamps();

            // Llaves foráneas
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            // Asegúrate de que la tabla clients exista o ajusta según corresponda
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_notes');
    }
}
