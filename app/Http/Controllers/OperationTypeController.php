<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OperationType;

class OperationTypeController extends Controller
{
    /**
     * Retorna la lista de tipos de operación.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $types = OperationType::all();
        return response()->json($types);
    }
}
