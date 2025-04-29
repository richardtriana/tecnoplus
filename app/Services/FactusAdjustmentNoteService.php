<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\FactusApiService;

class FactusAdjustmentNoteService
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Envía la Nota de Ajuste a Factus.
     *
     * @param  \App\Models\AdjustmentNote  $adjustmentNote
     * @return array|null  La respuesta de Factus o null en caso de error.
     */
    public function sendAdjustmentNote($adjustmentNote)
    {
        // Cargar el documento soporte relacionado
        $supportDocument = $adjustmentNote->supportDocument;
        if (!$supportDocument) {
            Log::error("No se encontró el documento soporte asociado a la nota de ajuste.");
            return null;
        }

        // Mapear los ítems de la nota de ajuste para enviar solo los campos requeridos
        $items = [];
        foreach ($adjustmentNote->items as $item) {
            $withholdingTaxes = [];
            if (!empty($item->withholding_taxes)) {
                if (is_string($item->withholding_taxes)) {
                    $withholdingTaxes = json_decode($item->withholding_taxes, true);
                } else {
                    $withholdingTaxes = $item->withholding_taxes;
                }
            }
            $items[] = [
                'code_reference'    => $item->code_reference,
                'name'              => $item->name,
                'quantity'          => (int) $item->quantity,
                'discount_rate'     => (float) $item->discount_rate,
                'price'             => (float) $item->price,
                'unit_measure_id'   => $item->unit_measure_id ? (int) $item->unit_measure_id : 70,
                'standard_code_id'  => $item->standard_code_id ? (int) $item->standard_code_id : 1,
                'withholding_taxes' => $withholdingTaxes,
            ];
        }

        // Armar el payload para enviar a Factus
        // Omitimos el campo "numbering_range_id" para que Factus utilice el único rango activo
        $payload = [
            'reference_code'          => $adjustmentNote->reference_code,
            'payment_method_code'     => $adjustmentNote->payment_method_code ?? '10',
            // Utilizamos el factus_support_document_id del comprobante asociado
            'support_document_id'     => (int) $supportDocument->factus_support_document_id,
            'correction_concept_code' => $adjustmentNote->correction_concept_code,
            'observation'             => $adjustmentNote->observation ?? "",
            'items'                   => $items,
        ];

        // Obtener el token de acceso a Factus
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

        // Definir el endpoint para la Nota de Ajuste
        $endpoint = config('factus.adjustment_note_endpoint', 'https://api-sandbox.factus.com.co/v1/adjustment-notes/validate');

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("Nota de ajuste enviada a Factus correctamente", $response->json());
                return $response->json();
            } else {
                Log::error("Error al enviar Nota de Ajuste a Factus", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error("Excepción al enviar Nota de Ajuste a Factus", ['error' => $e->getMessage()]);
            return null;
        }
    }
}
