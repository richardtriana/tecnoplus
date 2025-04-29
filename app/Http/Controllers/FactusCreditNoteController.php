<?php

namespace App\Http\Controllers;

use App\Services\FactusApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FactusCreditNoteController extends Controller
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Listar notas de crédito con filtros y paginación.
     *
     * La API de Factus para notas de crédito soporta filtros como:
     * filter[identification], filter[names], filter[number],
     * filter[prefix], filter[reference_code] y filter[status].
     * Además se deben enviar los parámetros "page" y "per_page".
     */
    public function getCreditNotes(Request $request)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            // Leer filtros y parámetros de paginación de la request
            $filter = $request->input('filter', []);
            if (is_string($filter)) {
                $filter = json_decode($filter, true);
            }
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);

            // Preparar el array de parámetros a enviar
            $params = [
                'filter'   => $filter,
                'page'     => $page,
                'per_page' => $perPage,
            ];
            $query = http_build_query($params);
            $url = $this->factusApiService->getBaseUrl() . "/v1/credit-notes";
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
            throw new Exception("Error al consultar notas de crédito: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ver detalle de una nota de crédito (por número)
     */
    public function showCreditNote($number)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            $url = $this->factusApiService->getBaseUrl() . "/v1/credit-notes/{$number}";
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get($url);

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Solicitud exitosa',
                    'data' => $response->json()['data'] ?? []
                ]);
            }
            throw new Exception("Error al obtener nota de crédito: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar PDF de una nota de crédito
     */
    public function downloadCreditNotePdf($number)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            $url = $this->factusApiService->getBaseUrl() . "/v1/credit-notes/download-pdf/{$number}";
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get($url);

            if ($response->successful()) {
                $jsonData = $response->json();
                if (!isset($jsonData['data']['pdf_base_64_encoded'])) {
                    throw new Exception("No se encontró la propiedad pdf_base_64_encoded en la respuesta de Factus");
                }
                return response()->json([
                    'message' => 'Descarga exitosa',
                    'data' => [
                        'file_name' => $jsonData['data']['file_name'] ?? 'nota_credito',
                        'pdf_base_64_encoded' => $jsonData['data']['pdf_base_64_encoded'],
                    ]
                ]);
            }
            throw new Exception("Error al descargar PDF: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar XML de una nota de crédito
     */
    public function downloadCreditNoteXml($number)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            $url = $this->factusApiService->getBaseUrl() . "/v1/credit-notes/download-xml/{$number}";
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get($url);

            if ($response->successful()) {
                $jsonData = $response->json();
                if (!isset($jsonData['data']['xml_base_64_encoded'])) {
                    throw new Exception("No se encontró la propiedad xml_base_64_encoded en la respuesta de Factus");
                }
                return response()->json([
                    'message' => 'Descarga exitosa',
                    'data' => [
                        'file_name' => $jsonData['data']['file_name'] ?? 'nota_credito',
                        'xml_base_64_encoded' => $jsonData['data']['xml_base_64_encoded'],
                    ]
                ]);
            }
            throw new Exception("Error al descargar XML: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una nota de crédito no validada (por reference_code)
     */
    public function deleteCreditNote($referenceCode)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }
            $url = $this->factusApiService->getBaseUrl() . "/v1/credit-notes/reference/{$referenceCode}";
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->delete($url);
                
            if ($response->successful()) {
                return response()->json([
                    'message' => $response->json()['message'] ?? "Nota crédito eliminada con éxito"
                ]);
            }
            throw new Exception("Error al eliminar nota crédito: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
