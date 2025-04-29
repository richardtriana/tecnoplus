<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IdentityDocumentType;

class IdentityDocumentTypeController extends Controller
{
    /**
     * Muestra una lista de todos los Identity Document Types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $identityDocumentTypes = IdentityDocumentType::all();
        return response()->json([
            'identityDocumentTypes' => $identityDocumentTypes,
            'message' => 'Identity document types retrieved successfully.'
        ], 200);
    }
}
