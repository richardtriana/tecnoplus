<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NumberingRange;

class NumberingRangeController extends Controller
{
    /**
     * Lista todos los registros, opcionalmente filtrando por document.
     */
    public function index(Request $request)
    {
        $query = NumberingRange::query();

        if ($request->has('document') && !empty($request->document)) {
            $query->where('document', $request->document);
        }

        $ranges = $query->get();

        return response()->json($ranges);
    }

    /**
     * Crea un nuevo registro.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'document'      => 'required|string',
            'prefix'        => 'required|string',
            'from'          => 'required|numeric',
            'to'            => 'required|numeric',
            'current'       => 'required|numeric',
            // El campo enviado_dian es opcional, pero si viene, debe ser booleano
            'enviado_dian'  => 'sometimes|boolean',
        ]);

        // Si no se envía, por defecto será false
        $data['enviado_dian'] = $data['enviado_dian'] ?? false;

        $range = NumberingRange::create($data);

        return response()->json([
            'message' => 'Voucher creado exitosamente',
            'data'    => $range,
        ], 201);
    }

    /**
     * Muestra un registro en particular.
     */
    public function show($id)
    {
        $range = NumberingRange::findOrFail($id);

        return response()->json($range);
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, $id)
    {
        $range = NumberingRange::findOrFail($id);

        $data = $request->validate([
            'document'      => 'required|string',
            'prefix'        => 'required|string',
            'from'          => 'required|numeric',
            'to'            => 'required|numeric',
            'current'       => 'required|numeric',
            'enviado_dian'  => 'sometimes|boolean',
        ]);

        // Si no se envía, no alteramos su valor; si se envía, se asigna
        // Pero si quieres que sea false por defecto si no se envía, haz:
        // $data['enviado_dian'] = $data['enviado_dian'] ?? false;

        $range->update($data);

        return response()->json([
            'message' => 'Voucher actualizado exitosamente',
            'data'    => $range,
        ]);
    }

    /**
     * Elimina un registro.
     */
    public function destroy($id)
    {
        $range = NumberingRange::findOrFail($id);
        $range->delete();

        return response()->json([
            'message' => 'Voucher eliminado exitosamente'
        ], 204);
    }
}
