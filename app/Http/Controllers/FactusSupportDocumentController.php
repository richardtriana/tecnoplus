<?php

namespace App\Http\Controllers;

use App\Services\FactusApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FactusSupportDocumentController extends Controller
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Listar documentos soporte con filtros (si aplica)
     */
    public function getSupportDocuments(Request $request)
    {
        try {
            $tokenData   = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }
            
            $filter  = $request->input('filter', []);
            if (is_string($filter)) {
                $filter = json_decode($filter, true);
            }
            $page    = $request->input('page', 1);
            $perPage = $request->input('per_page', 10);
            $from    = $request->input('from', '');
            $to      = $request->input('to', '');
            
            // Incluir en la query string los filtros y parámetros de paginación
            $query = http_build_query([
                'filter'   => $filter,
                'page'     => $page,
                'per_page' => $perPage,
                'from'     => $from,
                'to'       => $to
            ]);
            
            $url = $this->factusApiService->getBaseUrl() . "/v1/support-documents";
            if (!empty($query)) {
                $url .= "?" . $query;
            }
            
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->acceptJson()
                ->get($url);
            
            if ($response->successful()) {
                return response()->json([
                    'message' => 'Solicitud exitosa',
                    'data'    => $response->json()
                ]);
            }
            throw new Exception("Error al consultar documentos soporte: " . $response->body());
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ver documento soporte por número
     */
    public function showSupportDocument($number)
    {
        try {
            $tokenData   = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }
            $url = $this->factusApiService->getBaseUrl() . "/v1/support-documents/show/{$number}";
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->acceptJson()
                ->get($url);
            
            if ($response->successful()) {
                return response()->json([
                    'message' => 'Solicitud exitosa',
                    'data'    => $response->json()['data'] ?? []
                ]);
            }
            throw new Exception("Error al obtener documento soporte: " . $response->body());
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar PDF de un documento soporte
     */
    public function downloadSupportDocumentPdf($number)
    {
        try {
            $tokenData   = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }
            $url = $this->factusApiService->getBaseUrl() . "/v1/support-documents/download-pdf/{$number}";
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->acceptJson()
                ->get($url);
            
            if ($response->successful()) {
                $jsonData = $response->json();
                if (!isset($jsonData['data']['pdf_base_64_encoded'])) {
                    throw new Exception("No se encontró la propiedad pdf_base_64_encoded en la respuesta de Factus");
                }
                return response()->json([
                    'message' => 'Descarga exitosa',
                    'data'    => [
                        'file_name'          => $jsonData['data']['file_name'] ?? 'documento_soporte',
                        'pdf_base_64_encoded'=> $jsonData['data']['pdf_base_64_encoded']
                    ]
                ]);
            }
            throw new Exception("Error al descargar PDF: " . $response->body());
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar XML de un documento soporte
     */
    public function downloadSupportDocumentXml($number)
    {
        try {
            $tokenData   = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }
            $url = $this->factusApiService->getBaseUrl() . "/v1/support-documents/download-xml/{$number}";
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->acceptJson()
                ->get($url);
            if ($response->successful()) {
                $jsonData = $response->json();
                if (!isset($jsonData['data']['xml_base_64_encoded'])) {
                    throw new Exception("No se encontró la propiedad xml_base_64_encoded en la respuesta de Factus");
                }
                return response()->json([
                    'message' => 'Descarga exitosa',
                    'data'    => [
                        'file_name'           => $jsonData['data']['file_name'] ?? 'documento_soporte',
                        'xml_base_64_encoded' => $jsonData['data']['xml_base_64_encoded']
                    ]
                ]);
            }
            throw new Exception("Error al descargar XML: " . $response->body());
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un documento soporte no validado (por reference_code)
     */
    public function deleteSupportDocument($referenceCode)
    {
        try {
            $tokenData   = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }
            $url = $this->factusApiService->getBaseUrl() . "/v1/support-documents/reference/{$referenceCode}";
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->delete($url);
            if ($response->successful()) {
                return response()->json([
                    'message' => $response->json()['message'] ?? 'Documento soporte eliminado con éxito'
                ]);
            }
            throw new Exception("Error al eliminar documento soporte: " . $response->body());
        } catch(Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
