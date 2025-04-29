<?php

namespace App\Http\Controllers;

use App\Models\ProductIdentificationStandard;
use Illuminate\Http\Request;

class ProductIdentificationStandardController extends Controller
{
    /**
     * Retorna la lista completa de estándares de identificación.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $standards = ProductIdentificationStandard::all();
        return response()->json($standards);
    }
}
