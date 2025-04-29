<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use Illuminate\Http\Request;

class MeasurementUnitController extends Controller
{
    /**
     * Retorna la lista completa de unidades de medida.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $units = MeasurementUnit::all();
        return response()->json($units);
    }
}
