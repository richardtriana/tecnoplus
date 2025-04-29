<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganizationType;

class OrganizationTypeController extends Controller
{
    /**
     * Muestra una lista de todos los Organization Types.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $organizationTypes = OrganizationType::all();
        return response()->json([
            'organizationTypes' => $organizationTypes,
            'message' => 'Organization types retrieved successfully.'
        ], 200);
    }
}
