<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\NumberingRange;

class FactusCreditNoteService
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Envía la Nota Crédito a Factus para validación.
     *
     * Se espera recibir un array con la estructura requerida por Factus, por ejemplo:
     *
     * {
     *   "numbering_range_id": 5,
     *   "correction_concept_code": 2,
     *   "customization_id": 20,
     *   "bill_id": 514,
     *   "reference_code": "5",
     *   "observation": "",
     *   "payment_method_code": "10",
     *   "items": [
     *     {
     *       "code_reference": "123456",
     *       "name": "Aspirina",
     *       "quantity": 1,
     *       "discount_rate": 0,
     *       "price": 80000,
     *       "tax_rate": "19.00",
     *       "unit_measure_id": 70,
     *       "standard_code_id": 1,
     *       "is_excluded": 0,
     *       "tribute_id": 1,
     *       "withholding_taxes": []
     *     }
     *   ]
     * }
     *
     * @param array $creditNoteData
     * @return array La respuesta de Factus en caso de éxito.
     * @throws Exception En caso de error al obtener el token o al enviar la solicitud.
     */
    public function sendCreditNote(array $creditNoteData)
    {
        // Obtener el token de acceso usando el servicio FactusApiService
        try {
            $tokenData = $this->factusApiService->getToken();
        } catch (Exception $e) {
            Log::error("Error obteniendo token para nota crédito: " . $e->getMessage());
            throw new Exception("Error obteniendo token de Factus");
        }
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            Log::error("Token de acceso no recibido para nota crédito.");
            throw new Exception("Token de acceso no recibido");
        }

        // Validar y mapear el campo numbering_range_id
        if (isset($creditNoteData['numbering_range_id'])) {
            $range = NumberingRange::find($creditNoteData['numbering_range_id']);
            if (!$range || (int)$range->enviado_dian !== 1) {
                throw new Exception("El campo id rango de numeración es inválido.");
            }
            // Si el ID local es 2, se debe enviar 5 a Factus.
            $factusNumberingId = ($range->id == 2) ? 5 : $range->id;
            $creditNoteData['numbering_range_id'] = (int)$factusNumberingId;
        } else {
            throw new Exception("El campo numbering_range_id es obligatorio.");
        }

        // Convertir customization_id a entero y separar la lógica según su valor
        $customizationId = (int)$creditNoteData['customization_id'];
        $creditNoteData['customization_id'] = $customizationId;

        if ($customizationId === 22) {
            // Nota sin factura: asignar un valor predeterminado a bill_id (por ejemplo, 1111111)
            $creditNoteData['bill_id'] = 1111111;
            // Además, eliminamos order_id y client_id ya que no son requeridos.
            unset($creditNoteData['order_id']);
            unset($creditNoteData['client_id']);
        } else {
            if (isset($creditNoteData['order_id']) && !is_null($creditNoteData['order_id'])) {
                $creditNoteData['order_id'] = (int)$creditNoteData['order_id'];
            } else {
                unset($creditNoteData['order_id']);
            }
            if (isset($creditNoteData['client_id']) && !is_null($creditNoteData['client_id'])) {
                $creditNoteData['client_id'] = (int)$creditNoteData['client_id'];
            } else {
                unset($creditNoteData['client_id']);
            }
            if (isset($creditNoteData['bill_id']) && !is_null($creditNoteData['bill_id'])) {
                $creditNoteData['bill_id'] = (int)$creditNoteData['bill_id'];
            } else {
                unset($creditNoteData['bill_id']);
            }
        }

        // Log para ver el payload final que se enviará a Factus
        Log::info("Payload enviado a Factus", $creditNoteData);

        // Obtener el endpoint para Nota Crédito desde la configuración
        $endpoint = config('factus.endpoint_credit_note', 'https://api-sandbox.factus.com.co/v1/credit-notes/validate');

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $creditNoteData);

            if ($response->successful()) {
                Log::info("Nota Crédito enviada a Factus correctamente", $response->json());
                return $response->json();
            } else {
                Log::error("Error al enviar Nota Crédito a Factus", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                throw new Exception("Error al enviar Nota Crédito: " . $response->body());
            }
        } catch (Exception $e) {
            Log::error("Excepción al enviar Nota Crédito a Factus: " . $e->getMessage());
            throw new Exception("Excepción al enviar Nota Crédito: " . $e->getMessage());
        }
    }

    /**
     * Envía datos de prueba directamente a Factus.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function sendTestCreditNote(array $data)
    {
        try {
            $tokenData = $this->factusApiService->getToken();
        } catch (Exception $e) {
            throw new Exception("No se pudo obtener el token de acceso de Factus.");
        }
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            throw new Exception("No se pudo obtener el token de acceso de Factus.");
        }
        $endpoint = config('factus.endpoint_credit_note', 'https://api-sandbox.factus.com.co/v1/credit-notes/validate');
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $data);

            if ($response->successful()) {
                Log::info("Nota de crédito de prueba enviada a Factus correctamente", $response->json());
                return $response->json();
            } else {
                Log::error("Error al enviar nota de crédito de prueba a Factus", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                throw new Exception("Error en el envío: " . $response->body());
            }
        } catch (Exception $e) {
            Log::error("Excepción en sendTestCreditNote: " . $e->getMessage());
            throw new Exception("Excepción en el envío: " . $e->getMessage());
        }
    }

    /**
     * Mapear el payment_method_id de la orden al payment_method_code que requiere Factus.
     *
     * @param int|null $paymentMethodId
     * @return string
     */
    protected function mapPaymentMethod($paymentMethodId)
    {
        $mapping = [
            1  => 10,
            2  => 42,
            3  => 20,
            4  => 47,
            5  => 71,
            6  => 72,
            7  => 1,
            8  => 49,
            9  => 48,
            10 => 'ZZZ',
        ];

        return isset($mapping[$paymentMethodId]) ? (string)$mapping[$paymentMethodId] : "10";
    }

    /**
     * Mapear el payment_form_id de la orden al código que requiere Factus.
     *
     * @param int|null $paymentFormId
     * @return string
     */
    protected function mapPaymentForm($paymentFormId)
    {
        $mapping = [
            1 => 1,
            2 => 2,
        ];

        return isset($mapping[$paymentFormId]) ? (string)$mapping[$paymentFormId] : "1";
    }
}
