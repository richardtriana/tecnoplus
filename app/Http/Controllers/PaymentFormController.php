<?php

namespace App\Http\Controllers;

use App\Models\PaymentForm;
use Illuminate\Http\Request;

class PaymentFormController extends Controller
{
    /**
     * Muestra la lista de formas de pago.
     * GET /api/payment_forms
     */
    public function index()
    {
        $forms = PaymentForm::all(); // O PaymentForm::where(...)->get() si deseas filtrar
        return response()->json([
            'payment_forms' => $forms
        ], 200);
    }

    /**
     * (Opcional) Muestra una forma de pago específica.
     * GET /api/payment_forms/{id}
     */
    public function show($id)
    {
        $form = PaymentForm::find($id);
        if (!$form) {
            return response()->json([
                'message' => 'Forma de pago no encontrada'
            ], 404);
        }
        return response()->json($form, 200);
    }

    // (Opcional) Métodos store, update, destroy si necesitas CRUD completo
}
