<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;

class FactusSupportDocumentService
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Envía el Documento Soporte a Factus si el rango de numeración tiene "enviado_dian": 1.
     *
     * @param mixed $supportDocument  Un objeto (modelo) que contiene:
     *                                - provider: información del proveedor.
     *                                - items: los items asociados.
     *                                - getNumberingRange(): método para obtener el rango de numeración.
     *                                - payment_method_code, observation, etc.
     * @return array|null  La respuesta de Factus o null en caso de error o si no procede el envío.
     */
    public function sendSupportDocument($supportDocument)
    {
        // 1. Verificar que el comprobante (rango de numeración) tenga "enviado_dian": 1.
        $numberingRange = $supportDocument->getNumberingRange();
        if (!$numberingRange || (int)$numberingRange->enviado_dian !== 1) {
            Log::info("Documento Soporte no enviado a Factus: comprobante con enviado_dian != 1");
            return null;
        }

        // 2. Obtener la información del proveedor.
        $provider = $supportDocument->provider;
        if (!$provider) {
            Log::error("No se encontró información del proveedor para el documento soporte {$supportDocument->id}");
            return null;
        }

        // 3. Mapear los datos del proveedor al formato requerido por Factus.
        $providerData = [
            'identification_document_id' => $provider->identity_document_type_id,
            'identification'             => $provider->document,
            'dv'                         => $provider->div_verification,
            'trade_name'                 => $provider->trade_name ?? "",
            'names'                      => $provider->razon_social ?? trim("{$provider->first_name} {$provider->first_lastname}"),
            'address'                    => $provider->address,
            'email'                      => $provider->email,
            'phone'                      => $provider->phone,
            'country_code'               => $provider->country_code ?? "CO",
            'municipality_id'            => $provider->municipality_id ?? null,
        ];

        // 4. Mapear los items del documento soporte.
        $items = [];
        foreach ($supportDocument->items as $item) {
            $items[] = [
                'code_reference'    => $item->code_reference ?? $item->codigo ?? '',
                'name'              => $item->name,
                'quantity'          => (int)$item->quantity,
                'discount_rate'     => (float)$item->discount_rate,
                'price'             => (float)$item->price,
                // Si no existe, asignar un valor por defecto (70)
                'unit_measure_id'   => $item->unit_measure_id ?: 70,
                // Asignar por defecto 1 si no se envía
                'standard_code_id'  => $item->standard_code_id ?: 1,
                'withholding_taxes' => $item->withholding_taxes ? json_decode($item->withholding_taxes, true) : [],
            ];
        }

        // 5. Generar el reference_code concatenando el prefijo y el número actual.
        $referenceCode = $numberingRange->prefix . $numberingRange->current;

        // 6. Armar el payload para Factus.
        $payload = [
            'reference_code'      => $referenceCode,
            // Si existen múltiples rangos activos, se envía el id; de lo contrario, se omite el campo.
            'payment_method_code' => $supportDocument->payment_method_code ?? "10",
            'observation'         => $supportDocument->observation ?? "",
            'provider'            => $providerData,
            'items'               => $items,
        ];

        // Evaluar si hay más de un rango activo en la caja
        // (Se asume que el documento tiene relación con la caja a través de su rango de numeración)
        if ($supportDocument->box && $supportDocument->box->numberingRanges) {
            $factusRanges = $supportDocument->box->numberingRanges->where('enviado_dian', 1);
            if ($factusRanges->count() > 1 && isset($numberingRange->id)) {
                $payload['numbering_range_id'] = (int)$numberingRange->id;
            }
        }
        // En caso de que no se incluya el campo, la API de Factus usará el único rango activo

        Log::info("Payload para Factus Support Document:", $payload);

        // 7. Obtener el token de acceso a Factus.
        try {
            $tokenData = $this->factusApiService->getToken();
        } catch (Exception $e) {
            Log::error("Error al obtener token de Factus: " . $e->getMessage());
            return null;
        }
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            Log::error("Token de acceso no recibido de Factus.");
            return null;
        }

        // 8. Definir el endpoint (configurable)
        $endpoint = config('factus.support_document_endpoint', 'https://api-sandbox.factus.com.co/v1/support-documents/validate');

        // 9. Enviar la solicitud a Factus.
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("Documento Soporte enviado a Factus correctamente", $response->json());
                return $response->json();
            } else {
                Log::error("Error al enviar Documento Soporte a Factus", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Excepción al enviar Documento Soporte a Factus", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Envía datos de prueba directamente a Factus para validar el payload.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function sendTestSupportDocument(array $data)
    {
        // Obtener token de acceso
        $tokenData = $this->factusApiService->getToken();
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            throw new Exception("No se pudo obtener el token de acceso de Factus.");
        }

        // Endpoint de validación de Documento Soporte
        $endpoint = config('factus.support_document_endpoint', 'https://api-sandbox.factus.com.co/v1/support-documents/validate');

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $data);

            if ($response->successful()) {
                Log::info("Documento Soporte de prueba enviado a Factus correctamente", $response->json());
                return $response->json();
            } else {
                Log::error("Error al enviar Documento Soporte de prueba a Factus", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                throw new Exception("Error en el envío: " . $response->body());
            }
        } catch (Exception $e) {
            Log::error("Excepción en sendTestSupportDocument: " . $e->getMessage());
            throw new Exception("Excepción en el envío: " . $e->getMessage());
        }
    }
}
