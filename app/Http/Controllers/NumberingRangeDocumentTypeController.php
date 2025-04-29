<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NumberingRangeDocumentType;

class NumberingRangeDocumentTypeController extends Controller
{
    /**
     * Display a listing of the numbering range document types.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Se obtienen todos los registros de la tabla numbering_range_document_types
        $documentTypes = NumberingRangeDocumentType::all();

        return response()->json($documentTypes);
    }
}
