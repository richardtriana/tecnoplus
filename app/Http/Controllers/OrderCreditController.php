<?php

namespace App\Http\Controllers;

use App\Models\OrderCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderCreditController extends Controller
{
    /**
     * Display a paginated list of credits, con filtros y totales.
     */
    public function index(Request $request)
    {
        // 1) Parámetros de filtrado / paginación
        $perPage      = $request->get('per_page', 15);
        $status       = $request->get('status', 'pending');            // estado: pending, paid...
        $clientFilter = $request->get('client', '');                   // string a buscar en cliente
        $from         = $request->get('from', null);                   // fecha desde (YYYY-MM-DD)
        $to           = $request->get('to', null);                     // fecha hasta (YYYY-MM-DD)

        // 2) Construir query con los filtros
        $query = OrderCredit::with(['order.client', 'payments'])
            ->when($status, function($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($clientFilter, function($q) use ($clientFilter) {
                $q->whereHas('order.client', function($q2) use ($clientFilter) {
                    $q2->where('razon_social', 'like', "%{$clientFilter}%")
                       ->orWhere('name', 'like', "%{$clientFilter}%");
                });
            })
            ->when($from, function($q) use ($from) {
                $q->whereDate('created_at', '>=', $from);
            })
            ->when($to, function($q) use ($to) {
                $q->whereDate('created_at', '<=', $to);
            })
            ->orderByDesc('created_at');

        // 3) Obtener la página solicitada
        $paginated = $query->paginate($perPage);

        // 4) Para calcular totales, traemos todos los registros filtrados sin paginar
        $allRecords   = (clone $query)->get();
        $totalCredit  = $allRecords->sum('total_credit');
        $totalPaid    = $allRecords->sum(function($c) { return $c->payments->sum('pay'); });
        $totalBalance = $allRecords->sum('balance');

        // 5) Convertimos el paginator a array y le añadimos la sección "totals"
        $response = $paginated->toArray();
        $response['totals'] = [
            'total_credit'  => $totalCredit,
            'total_paid'    => $totalPaid,
            'total_balance' => $totalBalance,
        ];

        return response()->json($response);
    }

    /**
     * Store a newly created credit record for an order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        // 1) Cargar la orden
        $order = \App\Models\Order::findOrFail($request->order_id);

        // 2) Crear el registro de crédito
        $credit = OrderCredit::create([
            'order_id'     => $order->id,
            'total_credit' => $order->total_paid,
            'balance'      => $order->total_paid,
            'status'       => 'pending',
        ]);

        \Log::info("Created OrderCredit #{$credit->id} for Order #{$order->id}");

        return response()->json($credit, 201);
    }

    /**
     * Add a payment to an existing credit.
     */
    public function addPayment(Request $request, $creditId)
    {
        $request->validate([
            'pay' => 'required|numeric|min:0.01',
        ]);

        // 1) Cargar el crédito
        $credit = OrderCredit::findOrFail($creditId);

        // 2) Crear el pago
        $payment = \App\Models\PaymentCredit::create([
            'user_id'  => Auth::id(),
            'order_id' => $credit->order_id,
            'pay'      => $request->input('pay'),
        ]);

        // 3) Actualizar saldo y estado
        $credit->balance -= $payment->pay;
        $credit->status  = $credit->balance > 0 ? 'pending' : 'paid';
        $credit->save();

        \Log::info(
            "Applied payment of {$payment->pay} to OrderCredit #{$credit->id}. " .
            "New balance: {$credit->balance}"
        );

        return response()->json([
            'credit'  => $credit,
            'payment' => $payment,
        ]);
    }

    /**
     * Display a single credit with its payments.
     */
    public function show($creditId)
    {
        $credit = OrderCredit::with(['order.client', 'payments'])
                             ->findOrFail($creditId);

        return response()->json($credit);
    }

    /**
     * Remove a credit record.
     */
    public function destroy($creditId)
    {
        $credit = OrderCredit::findOrFail($creditId);
        $credit->delete();

        return response()->json(null, 204);
    }
}
