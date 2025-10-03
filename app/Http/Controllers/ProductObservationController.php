<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductObservation;
use Illuminate\Http\Request;

class ProductObservationController extends Controller
{
    /**
     * Listar todas las observaciones de un producto.
     */
    public function index(Product $product)
    {
        // Carga las observaciones relacionadas y las devuelve
        $observations = $product->observations()->orderBy('created_at', 'desc')->get();
        return response()->json([
            'observations' => $observations
        ]);
    }

    /**
     * Crear una nueva observación para un producto.
     */
    public function store(Request $request, Product $product)
    {
        // Validar input
        $validated = $request->validate([
            'observation' => 'required|string'
        ]);

        // Crear y devolver la observación recién creada
        $observation = $product->observations()->create([
            'observation' => $validated['observation']
        ]);

        return response()->json([
            'observation' => $observation
        ], 201);
    }

    /**
     * Eliminar una observación por su ID.
     */
    public function destroy($id)
    {
        $observation = ProductObservation::findOrFail($id);
        $observation->delete();

        // 204 No Content porque ya no hay nada que devolver
        return response()->json(null, 204);
    }
}
