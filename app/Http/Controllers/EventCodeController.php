<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventCode;

class EventCodeController extends Controller
{
    /**
     * Retorna la lista de tipos de evento.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Obtiene todos los registros del modelo EventCode
        $eventCodes = EventCode::all();

        return response()->json([
            'status'  => 'OK',
            'message' => 'Tipos de eventos obtenidos correctamente',
            'data'    => $eventCodes
        ], 200);
    }
}
