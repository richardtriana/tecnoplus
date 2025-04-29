<?php

namespace App\Http\Controllers;

use App\Services\FactusApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FactusReceptionController extends Controller
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Cargar Factura Electrónica
     * Endpoint: POST /v1/receptions/upload
     */
    public function uploadInvoice(Request $request)
    {
        try {
            // Validar los campos requeridos
            $request->validate([
                'track_id' => 'required|string',
                'file'     => 'required|file'
            ]);

            // Obtener token de acceso
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            // Preparar la URL del endpoint
            $url = $this->factusApiService->getBaseUrl() . "/v1/receptions/upload";

            // Enviar la solicitud con el archivo y el track_id
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->attach(
                    'file',
                    fopen($request->file('file')->getRealPath(), 'r'),
                    $request->file('file')->getClientOriginalName()
                )
                ->post($url, [
                    'track_id' => $request->input('track_id')
                ]);

            // Si la respuesta es 201 (Creado), retornamos la respuesta
            if ($response->status() == 201) {
                return response()->json([
                    'message' => 'Factura cargada correctamente',
                    'data'    => $response->json()
                ], 201);
            }

            throw new Exception("Error al cargar factura: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Consultar Facturas Electrónicas
     * Endpoint: GET /v1/receptions/bills?filter[...]=...
     */
    public function getBills(Request $request)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            // Obtener filtros y construir la query string
            $filter = $request->input('filter', []);
            if (is_string($filter)) {
                $filter = json_decode($filter, true);
            }
            $query = http_build_query(['filter' => $filter]);
            $url = $this->factusApiService->getBaseUrl() . "/v1/receptions/bills";
            if (!empty($query)) {
                $url .= "?" . $query;
            }

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get($url);

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Solicitud exitosa',
                    'data'    => $response->json()
                ]);
            }

            throw new Exception("Error al obtener facturas: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Emitir Evento en una Factura Electrónica
     * Endpoint: POST /v1/receptions/bills/{bill_id}/radian/events/{event_type}
     */
    public function emitEvent($bill_id, $event_type, Request $request)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            $url = $this->factusApiService->getBaseUrl() . "/v1/receptions/bills/{$bill_id}/radian/events/{$event_type}";

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($url, $request->all());

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Evento emitido correctamente',
                    'data'    => $response->json()
                ]);
            }

            throw new Exception("Error al emitir evento: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
