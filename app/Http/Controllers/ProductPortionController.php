<?php

namespace App\Http\Controllers;

use App\Models\ProductPortion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductPortionController extends Controller
{
    /**
     * Muestra la lista de porciones asociadas a productos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Puedes filtrar por product_id si es necesario:
        if ($request->has('product_id')) {
            $portions = ProductPortion::where('product_id', $request->product_id)->get();
        } else {
            $portions = ProductPortion::all();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $portions
        ], 200);
    }

    /**
     * Almacena una nueva porción asociada a un producto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'product_id' => 'required|integer|exists:products,id',
            'portion_id' => 'required|integer|exists:portions,id',
            'quantity'   => 'required|numeric|min:0'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors()
            ], 400);
        }

        // Evitar duplicados: si ya existe, se actualiza la cantidad.
        $productPortion = ProductPortion::updateOrCreate(
            [
                'product_id' => $request->product_id,
                'portion_id' => $request->portion_id
            ],
            ['quantity' => $request->quantity]
        );

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Porción asociada correctamente',
            'data'    => $productPortion
        ], 200);
    }

    /**
     * Actualiza la cantidad de una porción asociada a un producto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  ID del registro en product_portions
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'quantity'   => 'required|numeric|min:0'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors()
            ], 400);
        }

        $productPortion = ProductPortion::find($id);
        if (!$productPortion) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Registro no encontrado'
            ], 404);
        }

        $productPortion->quantity = $request->quantity;
        $productPortion->save();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Porción actualizada correctamente',
            'data'    => $productPortion
        ], 200);
    }

    /**
     * Elimina la asociación de porción con el producto.
     *
     * @param  int  $id  ID del registro en product_portions
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $productPortion = ProductPortion::find($id);
        if (!$productPortion) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Registro no encontrado'
            ], 404);
        }

        $productPortion->delete();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Porción eliminada correctamente'
        ], 200);
    }
}
