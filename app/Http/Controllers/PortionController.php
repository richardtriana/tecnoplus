<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portion;
use App\Models\PortionHistory;
use Illuminate\Support\Facades\Validator;

class PortionController extends Controller
{
    public function __construct()
    {
        // Se asume que todas las rutas requieren autenticación
        $this->middleware('auth:api');
    }

    /**
     * Lista las porciones con filtros y paginación.
     */
    public function index(Request $request)
    {
        $query = Portion::query();

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $portions = $query->paginate($perPage);

        return response()->json(['portions' => $portions]);
    }

    /**
     * Crea una nueva porción y registra el inventario inicial en el historial.
     */
    public function store(Request $request)
    {
        // Validación de datos
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'quantity'    => 'required|integer',
            'type'        => 'required|string|in:bodega,alacena',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Crear la porción
        $portion = new Portion();
        $portion->description = $request->description;
        $portion->quantity    = $request->quantity;
        $portion->type        = $request->type;
        $portion->status      = 1; // Estado activo por defecto
        $portion->save();

        // Registrar en el historial de porciones (inventario inicial)
        $history = new PortionHistory();
        $history->portion_id = $portion->id;
        $history->movement   = 'inventario inicial';
        $history->quantity   = $portion->quantity;
        $history->save();

        return response()->json([
            'message' => 'Porción creada con éxito',
            'portion' => $portion
        ], 201);
    }

    /**
     * Actualiza una porción existente; se permite editar solo descripción y tipo.
     */
    public function update(Request $request, $id)
    {
        $portion = Portion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'type'        => 'required|string|in:bodega,alacena',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $portion->description = $request->description;
        $portion->type        = $request->type;
        $portion->save();

        return response()->json([
            'message' => 'Porción actualizada con éxito',
            'portion' => $portion
        ], 200);
    }

    /**
     * Cambia el estado (activo/inactivo) de una porción.
     */
    public function changeStatus(Request $request, $id)
    {
        $portion = Portion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $portion->status = $request->status;
        $portion->save();

        return response()->json([
            'message' => 'Estado actualizado',
            'portion' => $portion
        ], 200);
    }

    /**
     * Exporta las porciones a Excel.
     * Nota: Implementa la exportación utilizando, por ejemplo, Maatwebsite\Excel.
     */
    public function exportExcel(Request $request)
    {
        return response()->json([
            'message' => 'Funcionalidad de exportación a Excel no implementada'
        ], 501);
    }
}
