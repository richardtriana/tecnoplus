<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdjustmentNoteReason;

class AdjustmentNoteReasonController extends Controller
{
    /**
     * Muestra la lista de todos los motivos de Nota de Ajuste.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $reasons = AdjustmentNoteReason::all();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'reasons' => $reasons
        ]);
    }

    /**
     * Muestra un motivo de Nota de Ajuste específico.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $reason = AdjustmentNoteReason::find($id);

        if (!$reason) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Motivo de nota de ajuste no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'code'   => 200,
            'reason' => $reason
        ]);
    }
}
