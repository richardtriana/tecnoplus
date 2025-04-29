<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Muestra la lista de métodos de pago.
     * GET /api/payment_methods
     */
    public function index()
    {
        $methods = PaymentMethod::all(); // O PaymentMethod::where(...)->get()
        return response()->json([
            'payment_methods' => $methods
        ], 200);
    }

    /**
     * (Opcional) Muestra un método de pago específico.
     * GET /api/payment_methods/{id}
     */
    public function show($id)
    {
        $method = PaymentMethod::find($id);
        if (!$method) {
            return response()->json([
                'message' => 'Método de pago no encontrado'
            ], 404);
        }
        return response()->json($method, 200);
    }

    // (Opcional) Métodos store, update, destroy si necesitas CRUD completo
}
