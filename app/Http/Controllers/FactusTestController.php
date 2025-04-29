<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\FactusInvoiceService;
use Exception;

class FactusTestController extends Controller
{
    protected $factusInvoiceService;

    public function __construct(FactusInvoiceService $factusInvoiceService)
    {
        $this->factusInvoiceService = $factusInvoiceService;
    }

    /**
     * Endpoint de prueba que espera recibir un order_id en el payload.
     * Ejemplo de payload:
     * {
     *   "order_id": 5
     * }
     *
     * Obtiene la orden y la envía a Factus usando sendInvoiceToFactus.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        try {
            $result = $this->factusInvoiceService->sendInvoiceToFactus($order);

            if ($result) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Factura enviada a Factus',
                    'data'    => $result
                ], 200);
            } else {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No se pudo enviar la factura a Factus o no aplica (comprobante con enviado_dian != 1)'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al enviar la factura a Factus',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint de prueba directa que espera la estructura completa de datos de prueba.
     * Ejemplo de payload:
     * {
     *   "invoice": { ... },
     *   "textInfo": { ... },
     *   "productsInvoiceList": [ ... ]
     * }
     *
     * Este método envía los datos recibidos directamente a Factus usando sendTestInvoice.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testDirect(Request $request)
    {
        $data = $request->all();

        try {
            $result = $this->factusInvoiceService->sendTestInvoice($data);
            return response()->json([
                'status'  => 'success',
                'message' => 'Factura de prueba enviada a Factus',
                'data'    => $result
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al enviar la factura de prueba a Factus',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
