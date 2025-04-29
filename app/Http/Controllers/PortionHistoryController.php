<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PortionHistory;

class PortionHistoryController extends Controller
{
    /**
     * Retorna el historial de una porción dado su ID, opcionalmente filtrado por fechas.
     */
    public function index($id, Request $request)
    {
        $query = PortionHistory::where('portion_id', $id);

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $histories = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'histories' => $histories,
            'message'   => ''
        ]);
    }
}
