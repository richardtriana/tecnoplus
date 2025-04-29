<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class FactusReceptionsService
{
    protected $baseUrl;
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
        $this->baseUrl = $this->factusApiService->getBaseUrl();
    }

    /**
     * Obtiene el token de acceso usando el servicio principal.
     *
     * @return string
     * @throws Exception
     */
    protected function getAccessToken()
    {
        $tokenData = $this->factusApiService->getToken();
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            throw new Exception("No se obtuvo token de acceso de Factus");
        }
        return $accessToken;
    }

    /**
     * Cargar una factura electrónica.
     *
     * @param string $trackId CUFE de la factura
     * @param string $filePath Ruta del archivo a cargar
     * @param string $fileName Nombre del archivo
     * @return array
     * @throws Exception
     */
    public function uploadInvoice(string $trackId, string $filePath, string $fileName)
    {
        $accessToken = $this->getAccessToken();
        $url = $this->baseUrl . "/v1/receptions/upload";

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->attach('file', fopen($filePath, 'r'), $fileName)
            ->post($url, [
                'track_id' => $trackId
            ]);

        if ($response->status() == 201) {
            return $response->json();
        }

        throw new Exception("Error al cargar factura: " . $response->body());
    }

    /**
     * Consulta las facturas electrónicas usando filtros.
     *
     * @param array $filters Filtros para la consulta
     * @return array
     * @throws Exception
     */
    public function getBills(array $filters = [])
    {
        $accessToken = $this->getAccessToken();
        $query = http_build_query(['filter' => $filters]);
        $url = $this->baseUrl . "/v1/receptions/bills";
        if (!empty($query)) {
            $url .= "?" . $query;
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception("Error al obtener facturas: " . $response->body());
    }

    /**
     * Emite un evento sobre una factura electrónica.
     *
     * @param int|string $billId ID de la factura electrónica
     * @param string $eventType Código del evento a emitir
     * @param array $data Datos de la persona y otros datos requeridos para el evento
     * @return array
     * @throws Exception
     */
    public function emitEvent($billId, string $eventType, array $data)
    {
        $accessToken = $this->getAccessToken();
        $url = $this->baseUrl . "/v1/receptions/bills/{$billId}/radian/events/{$eventType}";

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post($url, $data);

        if ($response->successful()) {
            return $response->json();
        }

        throw new Exception("Error al emitir evento: " . $response->body());
    }
}
