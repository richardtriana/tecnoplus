<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portion;
use App\Models\PortionHistory;
use App\Models\PortionOrder;
use App\Models\PortionOrderDetail;
use Illuminate\Support\Facades\Validator;
use DB;

class PortionOrderController extends Controller
{
    public function __construct()
    {
        // Se asume que todas las rutas requieren autenticación
        $this->middleware('auth:api');
    }

    /**
     * Lista las órdenes (opcionalmente con sus detalles).
     */
    public function index(Request $request)
    {
        // Puedes agregar filtros o paginación según sea necesario
        $orders = PortionOrder::with('details')->paginate(20);
        return response()->json(['orders' => $orders]);
    }

    /**
     * Registra una nueva orden y afecta:
     *   - La tabla principal de órdenes (portion_orders).
     *   - La tabla de detalles (portion_order_details).
     *   - La tabla de historial de porciones (portion_histories).
     *   - Actualiza la cantidad de la porción (incrementa o decrementa según el movimiento).
     *
     * Se espera que la solicitud incluya:
     *  - detail: texto (opcional)
     *  - user_id: id del usuario (obligatorio)
     *  - date: fecha y hora de la orden (obligatorio)
     *  - movement: "ingreso" o "salida" (obligatorio) para la orden (aunque en cada detalle se especifica también)
     *  - details: arreglo de objetos con:
     *       portion_id, movement ("ingreso" o "salida") y quantity.
     */
    public function store(Request $request)
    {
        // Validación de la orden y sus detalles
        $validator = Validator::make($request->all(), [
            'detail'    => 'nullable|string',
            'user_id'   => 'required|integer|exists:users,id',
            'date'      => 'required|date',
            'movement'  => 'required|in:ingreso,salida',
            'details'   => 'required|array|min:1',
            'details.*.portion_id' => 'required|integer|exists:portions,id',
            'details.*.movement'   => 'required|in:ingreso,salida',
            'details.*.quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Crear el registro de la orden
            $order = new PortionOrder();
            $order->detail    = $request->detail;
            $order->user_id   = $request->user_id;
            $order->date      = $request->date;
            $order->movement  = $request->movement;
            $order->save();

            // Procesar cada detalle de la orden
            foreach ($request->details as $detailInput) {
                // Recuperar la porción
                $portion = Portion::findOrFail($detailInput['portion_id']);

                // Actualizar la cantidad de la porción según el movimiento
                if ($detailInput['movement'] === 'ingreso') {
                    $portion->quantity += $detailInput['quantity'];
                } else if ($detailInput['movement'] === 'salida') {
                    $portion->quantity -= $detailInput['quantity'];
                    if ($portion->quantity < 0) {
                        throw new \Exception("La cantidad de la porción (ID: {$portion->id}) no puede ser negativa.");
                    }
                }
                $portion->save();

                // Registrar el detalle de la orden
                $orderDetail = new PortionOrderDetail();
                $orderDetail->portion_order_id = $order->id;
                $orderDetail->portion_id       = $detailInput['portion_id'];
                $orderDetail->movement         = $detailInput['movement'];
                $orderDetail->quantity         = $detailInput['quantity'];
                $orderDetail->save();

                // Registrar en el historial de la porción
                $history = new PortionHistory();
                $history->portion_id = $portion->id;
                $history->movement   = $detailInput['movement'];
                $history->quantity   = $detailInput['quantity'];
                $history->save();
            }

            DB::commit();
            return response()->json([
                'message' => 'Orden creada con éxito',
                'order'   => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
