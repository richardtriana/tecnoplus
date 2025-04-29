<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\CreditNoteDetail;
use App\Models\Order;
use App\Models\NumberingRange;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Services\FactusApiService;
use App\Services\FactusCreditNoteService;

class CreditNoteController extends Controller
{
    protected $factusApiService;

    public function __construct(FactusApiService $factusApiService)
    {
        $this->factusApiService = $factusApiService;
    }

    /**
     * Almacena localmente la nota crédito y sus detalles, y la envía a Factus para validación (si corresponde).
     *
     * Método: POST
     * Endpoint local: /api/credit-notes/validate
     *
     * Se espera que la solicitud incluya, de ser posible:
     * - order_id: ID de la orden.
     * - client_id: ID del cliente.
     *
     * Si no se envían, se intentará obtenerlos a partir de bill_id.
     */
    public function store(Request $request)
    {
        // Registrar el payload recibido para depuración
        Log::info('Payload recibido en CreditNoteController::store', $request->all());

        // Si no se envían order_id y client_id, intentar obtenerlos a partir de bill_id.
        if (!$request->has('order_id') || !$request->has('client_id')) {
            $order = Order::find($request->bill_id);
            if ($order) {
                $request->merge([
                    'order_id'  => $order->id,
                    'client_id' => $order->client_id,
                ]);
            } else {
                Log::warning('No se encontró la orden para bill_id: ' . $request->bill_id);
            }
        }

        // Normalizar "items": asegurar que cada ítem tenga "withholding_taxes" como array
        $items = $request->input('items', []);
        if (!is_array($items)) {
            $items = json_decode($items, true) ?? [];
        }
        foreach ($items as $index => $item) {
            if (!isset($item['withholding_taxes']) || is_null($item['withholding_taxes'])) {
                $items[$index]['withholding_taxes'] = [];
            }
            if (!is_array($items[$index]['withholding_taxes'])) {
                $temp = json_decode($items[$index]['withholding_taxes'], true);
                $items[$index]['withholding_taxes'] = is_array($temp) ? $temp : [];
            }
        }
        $request->merge(['items' => $items]);

        // Validar datos
        $validator = \Validator::make($request->all(), [
            'order_id'                => 'nullable|integer',
            'client_id'               => 'nullable|integer',
            'numbering_range_id'      => 'required|integer',
            'correction_concept_code' => 'required',
            'customization_id'        => 'required',
            'bill_id'                 => 'nullable|integer',
            'reference_code'          => 'required|string',
            'payment_method_code'     => 'required|string',
            'items'                   => 'required|array|min:1',
            'items.*.code_reference'  => 'required|string',
            'items.*.name'            => 'required|string',
            'items.*.quantity'        => 'required|integer|min:1',
            'items.*.discount_rate'   => 'required|numeric',
            'items.*.price'           => 'required|numeric',
            'items.*.tax_rate'        => 'required|string',
            'items.*.unit_measure_id' => 'required|integer',
            'items.*.standard_code_id'=> 'required|integer',
            'items.*.is_excluded'     => 'required|integer',
            'items.*.tribute_id'      => 'required|integer',
            'items.*.withholding_taxes'=> 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Asegurarse de que cada ítem tenga withholding_taxes definido
        $normalizedItems = [];
        foreach ($request->input('items') as $item) {
            if (!isset($item['withholding_taxes']) || is_null($item['withholding_taxes'])) {
                $item['withholding_taxes'] = [];
            }
            $normalizedItems[] = $item;
        }
        $request->merge(['items' => $normalizedItems]);

        // Iniciar transacción para guardar la nota crédito y sus detalles
        DB::beginTransaction();
        try {
            // Guardar en credit_notes
            $creditNote = new CreditNote();
            $creditNote->order_id = $request->order_id;
            $creditNote->client_id = $request->client_id;
            $creditNote->numbering_range_id = $request->numbering_range_id;
            $creditNote->correction_concept_code = $request->correction_concept_code;
            $creditNote->customization_id = $request->customization_id;
            $creditNote->bill_id = $request->bill_id;
            $creditNote->reference_code = $request->reference_code;
            $creditNote->observation = $request->observation ?? '';
            $creditNote->payment_method_code = $request->payment_method_code;
            $creditNote->items = json_encode($request->items);
            $creditNote->created_at = Carbon::now();
            $creditNote->updated_at = Carbon::now();
            $creditNote->save();

            // Guardar cada detalle en credit_note_details
            foreach ($request->items as $item) {
                $detail = new CreditNoteDetail();
                $detail->credit_note_id = $creditNote->id;
                $detail->code_reference = $item['code_reference'];
                $detail->name = $item['name'];
                $detail->quantity = $item['quantity'];
                $detail->discount_rate = $item['discount_rate'];
                $detail->price = $item['price'];
                $detail->tax_rate = $item['tax_rate'];
                $detail->unit_measure_id = $item['unit_measure_id'];
                $detail->standard_code_id = $item['standard_code_id'];
                $detail->is_excluded = $item['is_excluded'];
                $detail->tribute_id = $item['tribute_id'];
                $detail->withholding_taxes = json_encode($item['withholding_taxes']);
                $detail->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al guardar nota crédito localmente: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error al guardar nota crédito localmente'], 500);
        }

        // Preparar payload para Factus
        $payload = [
            'numbering_range_id'      => (int)$request->numbering_range_id,
            'correction_concept_code' => (int)$request->correction_concept_code,
            'customization_id'        => (int)$request->customization_id,
            'bill_id'                 => ($request->bill_id !== null ? (int)$request->bill_id : null),
            'reference_code'          => $request->reference_code,
            'observation'             => $request->observation ?? "",
            'payment_method_code'     => $request->payment_method_code,
            'items'                   => $request->items
        ];

        // Para notas sin factura (customization_id == 22) según la documentación,
        // se debe enviar un bill_id predeterminado (por ejemplo, 1111111).
        if ((int)$request->customization_id === 22) {
            $payload['bill_id'] = 1111111;
            // Y eliminamos order_id y client_id.
            unset($payload['order_id']);
            unset($payload['client_id']);
        }

        // Log del payload que se enviará a Factus
        Log::info("Payload enviado a Factus", $payload);

        // Consultar el rango de numeración activo para "Nota Crédito" con enviado_dian = 1
        $activeRanges = NumberingRange::where('document', 'Nota Crédito')
            ->where('is_active', 1)
            ->where('enviado_dian', 1)
            ->get();
        if ($activeRanges->count() === 1) {
            // Si hay solo un rango activo, se omite el campo para que Factus asuma el único rango
            unset($payload['numbering_range_id']);
        } else {
            // Si hay más de un rango activo, validar que el rango enviado sea válido
            $range = NumberingRange::find($request->numbering_range_id);
            if (!$range || (int)$range->enviado_dian !== 1) {
                Log::error("Rango de numeración inválido para envío a Factus. ID recibido: " . $request->numbering_range_id);
                return response()->json([
                    'status'  => 'error',
                    'message' => 'El campo id rango de numeración es inválido.'
                ], 422);
            }
            $payload['numbering_range_id'] = (int)$range->id;
        }

        // Obtener token de acceso para Factus
        try {
            $tokenData = $this->factusApiService->getToken();
        } catch (Exception $e) {
            Log::error("Error obteniendo token de Factus: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error obteniendo token de Factus'], 500);
        }
        $accessToken = $tokenData['access_token'] ?? null;
        if (!$accessToken) {
            Log::error("Token de acceso no recibido de Factus.");
            return response()->json(['status' => 'error', 'message' => 'Token no recibido'], 500);
        }

        // Endpoint para validar Nota Crédito en Factus
        $endpoint = config('factus.endpoint_credit_note', 'https://api-sandbox.factus.com.co/v1/credit-notes/validate');

        // Enviar la nota crédito a Factus
        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($endpoint, $payload);

            if ($response->successful()) {
                // Extraer los datos de la respuesta de Factus para almacenarlos en el registro local
                $factusResponse = $response->json();
                $data = $factusResponse['data']['credit_note'] ?? [];
                
                // Actualizar campos del registro con la respuesta de Factus
                $creditNote->number          = $data['number'] ?? null;
                $creditNote->status          = $data['status'] ?? null;
                $creditNote->send_email      = $data['send_email'] ?? null;
                $creditNote->qr              = $data['qr'] ?? null;
                $creditNote->cude            = $data['cude'] ?? null;
                $creditNote->validated       = $data['validated'] ?? null;
                $creditNote->discount_rate   = $data['discount_rate'] ?? null;
                $creditNote->discount        = $data['discount'] ?? null;
                $creditNote->gross_value     = $data['gross_value'] ?? null;
                $creditNote->taxable_amount  = $data['taxable_amount'] ?? null;
                $creditNote->tax_amount      = $data['tax_amount'] ?? null;
                $creditNote->total           = $data['total'] ?? null;
                $creditNote->errors          = isset($data['errors']) ? json_encode($data['errors']) : null;
                $creditNote->qr_image        = $data['qr_image'] ?? null;
                $creditNote->factus_response = json_encode($factusResponse);
                $creditNote->save();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Nota Crédito creada y validada con éxito',
                    'data'    => $factusResponse
                ], 201);
            } else {
                Log::error("Error al enviar nota crédito a Factus", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Error al enviar nota crédito a Factus'
                ], $response->status());
            }
        } catch (Exception $e) {
            Log::error("Excepción al enviar nota crédito a Factus: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Excepción al enviar nota crédito a Factus'
            ], 500);
        }
    }
}
