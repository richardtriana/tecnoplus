<?php

namespace App\Http\Controllers;

use App\Services\FactusApiService;
use Illuminate\Http\Request;
use Exception;

class FactusTokenController extends Controller
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    public function getToken(Request $request)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            return response()->json($tokenData);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'No se pudo obtener el token',
                'error'   => $e->getMessage()
            ], 400);
        }
    }
}
