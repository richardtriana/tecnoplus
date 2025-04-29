<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionCode;

class CorrectionCodeController extends Controller
{
    /**
     * Retorna la lista de códigos de corrección.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $codes = CorrectionCode::all();
        return response()->json($codes);
    }
}
