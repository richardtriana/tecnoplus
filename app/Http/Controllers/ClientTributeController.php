<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientTribute;

class ClientTributeController extends Controller
{
    /**
     * Muestra una lista de todos los Client Tributes.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $clientTributes = ClientTribute::all();
        return response()->json([
            'clientTributes' => $clientTributes,
            'message' => 'Client tributes retrieved successfully.'
        ], 200);
    }
}
