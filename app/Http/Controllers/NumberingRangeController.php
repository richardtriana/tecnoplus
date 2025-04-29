<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NumberingRange; // Asegúrate de tener este modelo y su namespace correcto

class NumberingRangeController extends Controller
{
    /**
     * Muestra la lista de comprobantes (numbering_ranges).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = NumberingRange::query();

        if ($request->has('document') && !empty($request->document)) {
            $query->where('document', $request->document);
        }
    // Filtro por is_active
    if ($request->has('is_active') && $request->is_active !== '') {
        $query->where('is_active', $request->is_active);
    }
    
        $ranges = $query->get();

        return response()->json($ranges);
    }

    /**
     * Almacena un nuevo comprobante.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'document'      => 'required|string',
            'prefix'        => 'required|string',
            'from'          => 'required|numeric',
            'to'            => 'required|numeric',
            'current'       => 'required|numeric',
            'enviado_dian'  => 'sometimes|boolean',
        ]);
    
        // Si no se envía enviado_dian, lo establecemos en false por defecto
        $data['enviado_dian'] = $data['enviado_dian'] ?? false;
    
        $range = NumberingRange::create($data);
    
        return response()->json($range, 201);
    }
    

    /**
     * Muestra un comprobante en particular.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $range = NumberingRange::findOrFail($id);

        return response()->json($range);
    }

    /**
     * Actualiza un comprobante existente.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $range = NumberingRange::findOrFail($id);
    
        $data = $request->validate([
            'document'     => 'required|string',
            'prefix'       => 'required|string',
            'from'         => 'required|numeric',
            'to'           => 'required|numeric',
            'current'      => 'required|numeric',
            'enviado_dian' => 'sometimes|boolean', // <-- Agregamos esta línea
        ]);
    
        // Si no viene enviado_dian en la petición, no lo forzamos a false,
        // pero si deseas que se ponga en false si no viene, podrías hacer:
        // $data['enviado_dian'] = $data['enviado_dian'] ?? false;
    
        $range->update($data);
    
        return response()->json($range);
    }
    
        public function creditNotes(Request $request)
    {
        // Filtrar rangos para notas de crédito y activos (ajusta según tus criterios)
        $ranges = NumberingRange::where('document', 'credit_note')
                    ->where('is_active', 1)
                    ->get();

        return response()->json(['ranges' => $ranges]);
    }
}
