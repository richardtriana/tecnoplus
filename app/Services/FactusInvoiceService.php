<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use Carbon\Carbon;

class FactusInvoiceService
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Envía la factura a Factus si el comprobante seleccionado tiene "enviado_dian": 1.
     *
     * @param Order $order
     * @return array La respuesta de Factus
     * @throws Exception Cuando Factus devuelve un error o no se debe enviar
     */
    public function sendInvoiceToFactus(Order $order)
    {
        // 1. Verificar si el comprobante (rango de numeración) tiene "enviado_dian": 1.
        $numberingRange = $order->getNumberingRange();
        if (!$numberingRange || (int)$numberingRange->enviado_dian !== 1) {
            Log::info("Factura no enviada a Factus: comprobante con enviado_dian != 1");
            throw new Exception('Comprobante no marcado para envío a DIAN');
        }

        // 2. Obtener la información completa del cliente.
        $client = $order->client;
        if (!$client) {
            Log::error("No se encontró información del cliente para la orden {$order->id}");
            throw new Exception('Cliente no encontrado');
        }

        // Mapear los datos del cliente al formato que Factus requiere.
        $customerData = [
            'identification'             => $client->document,
            'dv'                         => $client->div_verification,
            'company'                    => $client->razon_social ?: "{$client->first_name} {$client->first_lastname}",
            'trade_name'                 => $client->razon_social ?: "{$client->first_name} {$client->first_lastname}",
            'names'                      => trim("{$client->first_name} {$client->second_name} {$client->first_lastname} {$client->second_lastname}"),
            'address'                    => $client->address,
            'email'                      => $client->email,
            'phone'                      => $client->phone,
            'legal_organization_id'      => $client->organization_type_id,
            'tribute_id'                 => $client->client_tribute_id,
            'identification_document_id' => $client->identity_document_type_id,
            'municipality_id'            => $client->municipality_id,
        ];

        // 3. Mapear los items de la orden a partir de la relación detailOrders.
        $items = [];
        foreach ($order->detailOrders as $item) {
            $product = \App\Models\Product::find($item->product_id);
            $taxRate = "0.00";
            if ($product && isset($product->tax->percentage)) {
                $taxRate = number_format($product->tax->percentage, 2, '.', '');
            }
            $items[] = [
                'code_reference'    => $item->barcode,
                'name'              => $item->product,
                'quantity'          => (int)$item->quantity,
                'discount_rate'     => (float)$item->discount_percentage,
                'price'             => (float)$item->price_tax_inc,
                'tax_rate'          => $taxRate,
                'unit_measure_id'   => 70,
                'standard_code_id'  => 1,
                'is_excluded'       => 0,
                'tribute_id'        => 1,
                'withholding_taxes' => []
            ];
        }

        // 4. Generar el reference_code concatenando el prefijo y el número actual.
        $referenceCode = $numberingRange->prefix . $numberingRange->current;

        // 5. Armar el payload para Factus.
        $invoiceData = [
            'document'            => '01',
            'reference_code'      => $referenceCode,
            'observation'         => $order->observations ?? "",
            'payment_method_code' => $this->mapPaymentMethod($order->payment_method_id),
            'customer'            => $customerData,
            'items'               => $items,
        ];

        // Incluir la forma de pago si se ha definido.
        if (isset($order->payment_form_id)) {
            $invoiceData['payment_form'] = $this->mapPaymentForm($order->payment_form_id);
        }

        // Si la forma de pago es crédito (payment_form_id == 2), enviar fecha de vencimiento.
        if ($order->payment_form_id == 2) {
            $dueDate = $order->payment_due_date ?: Carbon::now()->addDays(30)->toDateString();
            $invoiceData['payment_due_date'] = $dueDate;
        }

        // Filtrar rangos Factus activos.
        $factusRanges = $order->box->numberingRanges->where('enviado_dian', 1);
        if ($factusRanges->count() > 1) {
            $invoiceData['numbering_range_id'] = (int)$numberingRange->id;
        }

        // 6. Obtener el token de acceso usando FactusApiService.
        try {
            $tokenData = $this->factusApiService->getToken();
        } catch (Exception $e) {
            Log::error("Error al obtener token de Factus: " . $e->getMessage());
            throw new Exception('No se pudo obtener token de Factus');
        }
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            Log::error("Token de acceso no recibido de Factus.");
            throw new Exception('Token inválido de Factus');
        }

        // 7. Enviar la factura a Factus.
        $endpoint = config('factus.endpoint', 'https://api-sandbox.factus.com.co/v1/bills/validate');
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $invoiceData);

            if ($response->successful()) {
                Log::info("Factura enviada a Factus correctamente", $response->json());
                return $response->json();
            }

            // Si no fue successful, lanzamos excepción con detalle.
            $status = $response->status();
            $body   = $response->body();
            Log::error("Error al enviar factura a Factus", ['status' => $status, 'body' => $body]);
            throw new Exception(json_encode(['status' => $status, 'body' => $body]));

        } catch (Exception $e) {
            Log::error("Excepción al enviar factura a Factus", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Envía datos de prueba directamente a Factus.
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function sendTestInvoice(array $data)
    {
        $tokenData = $this->factusApiService->getToken();
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            throw new Exception("No se pudo obtener el token de acceso de Factus.");
        }

        $endpoint = config('factus.endpoint', 'https://api-sandbox.factus.com.co/v1/bills/validate');
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $data);

            if ($response->successful()) {
                Log::info("Factura de prueba enviada a Factus correctamente", $response->json());
                return $response->json();
            }

            Log::error("Error al enviar factura de prueba a Factus", [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            throw new Exception("Error en el envío de prueba: " . $response->body());

        } catch (Exception $e) {
            Log::error("Excepción en sendTestInvoice: " . $e->getMessage());
            throw new Exception("Excepción en el envío de prueba: " . $e->getMessage());
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

        return isset($mapping[$paymentMethodId])
            ? (string)$mapping[$paymentMethodId]
            : "10";
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

        return isset($mapping[$paymentFormId])
            ? (string)$mapping[$paymentFormId]
            : "1";
    }
}
