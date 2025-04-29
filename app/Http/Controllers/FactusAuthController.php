<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FactusAuthController extends Controller
{
    public function getToken()
    {
        // Definimos los parámetros de la solicitud usando las credenciales del .env
        $data = [
            'grant_type'    => 'password',
            'client_id'     => env('FACTUS_CLIENT_ID'),
            'client_secret' => env('FACTUS_CLIENT_SECRET'),
            'username'      => env('FACTUS_USERNAME'),
            'password'      => env('FACTUS_PASSWORD'),
        ];

        try {
            // Realizamos la solicitud POST al endpoint de Factus
            $response = Http::asForm()->post('https://api-sandbox.factus.com.co/oauth/token', $data);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("Token obtenido correctamente", $data);
                return response()->json($data);
            } else {
                $errorMessage = 'No se pudo obtener el token: ' . $response->body();
                Log::error($errorMessage);
                return response()->json([
                    'error' => 'No se pudo obtener el token',
                    'message' => $response->body()
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Excepción al obtener token: " . $e->getMessage());
            return response()->json([
                'error' => 'Error en la solicitud',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        if (!$refreshToken) {
            return response()->json(['error' => 'El refresh token es requerido.'], 400);
        }

        $data = [
            'grant_type'    => 'refresh_token',
            'client_id'     => env('FACTUS_CLIENT_ID'),
            'client_secret' => env('FACTUS_CLIENT_SECRET'),
            'refresh_token' => $refreshToken,
        ];

        try {
            $response = Http::asForm()->post('https://api-sandbox.factus.com.co/oauth/token', $data);
            if ($response->successful()) {
                $data = $response->json();
                Log::info("Token refrescado correctamente", $data);
                return response()->json($data);
            } else {
                $errorMessage = 'No se pudo refrescar el token: ' . $response->body();
                Log::error($errorMessage);
                return response()->json([
                    'error' => 'No se pudo refrescar el token',
                    'message' => $response->body(),
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Excepción al refrescar token: " . $e->getMessage());
            return response()->json([
                'error' => 'Error en la solicitud',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
