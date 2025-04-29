<?php

namespace App\Http\Controllers;

use App\Services\FactusApiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FactusInvoiceController extends Controller
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Listar facturas (o documentos soporte) con filtros y paginación.
     * 
     * Nota: La API de Factus para support-documents soporta los siguientes filtros:
     *   filter[identification], filter[names], filter[number],
     *   filter[prefix], filter[reference_code] y filter[status].
     * 
     * Desde el frontend se enviarán los filtros dentro de la clave "filter", junto a
     * los parámetros "page" y "per_page".
     */
    public function getBills(Request $request)
    {
        try {
            // 1. Obtener token de acceso
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            // 2. Leer filtros y parámetros de paginación desde la request
            $filter = $request->input('filter', []);
            // Si se recibe en formato JSON, decodificarlo
            if (is_string($filter)) {
                $filter = json_decode($filter, true);
            }
            $page = $request->input('page', 1);       // Valor por defecto: 1
            $perPage = $request->input('per_page', 10); // Valor por defecto: 10

            // 3. Preparar el array de parámetros a enviar
            $params = [];
            if (!empty($filter)) {
                $params['filter'] = $filter;
            }
            $params['page'] = $page;
            $params['per_page'] = $perPage;

            // Construir la query string
            $query = http_build_query($params);

            // 4. Preparar la URL (en este caso se usa /v1/bills)
            $url = $this->factusApiService->getBaseUrl() . "/v1/bills";
            if (!empty($query)) {
                $url .= "?" . $query;
            }

            // 5. Llamar a la API de Factus
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get($url);

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Solicitud exitosa',
                    'data'    => $response->json()
                ]);
            }

            throw new Exception("Error al consultar facturas en Factus: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descargar PDF de una factura.
     */
    public function downloadPdf($number)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            $url = $this->factusApiService->getBaseUrl() . "/v1/bills/download-pdf/{$number}";
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
                        'file_name' => $jsonData['data']['file_name'] ?? 'factura',
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
     * Descargar XML de una factura.
     */
    public function downloadXml($number)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }

            $url = $this->factusApiService->getBaseUrl() . "/v1/bills/download-xml/{$number}";
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
                        'file_name' => $jsonData['data']['file_name'] ?? 'factura',
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
     * Eliminar una factura no validada (por reference_code)
     */
    public function deleteBill($referenceCode)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
            $accessToken = $tokenData['access_token'] ?? null;
            if (!$accessToken) {
                throw new Exception("No se obtuvo token de acceso de Factus");
            }
            $url = $this->factusApiService->getBaseUrl() . "/v1/bills/destroy/reference/{$referenceCode}";
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->delete($url);

            if ($response->successful()) {
                return response()->json([
                    'message' => $response->json()['message'] ?? "Factura eliminada con éxito"
                ]);
            }
            throw new Exception("Error al eliminar factura: " . $response->body());
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
