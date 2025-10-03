<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashReconciliation;
use App\Models\Box;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashReconciliationController extends Controller
{
    /**
     * GET  /api/cash-reconciliations/open?box_id={id}
     * Devuelve el arqueo abierto para la caja indicada.
     */
    public function open(Request $request)
    {
        $request->validate([
            'box_id' => 'required|exists:boxes,id',
        ]);

        $reconciliation = CashReconciliation::where('box_id', $request->box_id)
            ->where('is_open', true)
            ->with(['openingUser', 'closingUser'])
            ->first();

        return response()->json($reconciliation);
    }

    /**
     * POST /api/cash-reconciliations
     * Crea un nuevo arqueo (abre la caja).
     * Usa el usuario autenticado para opening_user_id.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'box_id'          => 'required|exists:boxes,id',
            'opening_balance' => 'required|numeric',
        ]);

        // 1) Crear el arqueo
        $recon = CashReconciliation::create([
            'box_id'           => $data['box_id'],
            'opening_user_id'  => $request->user()->id,
            'closing_user_id'  => null,
            'opening_balance'  => $data['opening_balance'],
            'entries'          => 0,
            'exits'            => 0,
            'credits'          => 0,
            'computed_balance' => $data['opening_balance'],
            'reported_balance' => null,
            'difference'       => null,
            'is_open'          => true,
            'opened_at'        => Carbon::now(),
            'closed_at'        => null,
        ]);

        // 2) Marcar la caja como abierta
        Box::where('id', $data['box_id'])
            ->update(['is_open' => true]);

        $recon->load(['openingUser', 'closingUser']);

        return response()->json($recon, 201);
    }

    /**
     * PUT /api/cash-reconciliations/{id}/close
     * Cierra el arqueo actual de la caja.
     */
  public function close($id, Request $request)
{
    $data = $request->validate([
        'reported_balance' => 'required|numeric',
    ]);

    $recon = CashReconciliation::findOrFail($id);

    // Convierte opened_at a instancia Carbon
    $from = Carbon::parse($recon->opened_at);
    $to   = Carbon::now();

    // 1) Calcula TODO: ventas en efectivo (payment_form_id != 2) desde la apertura hasta ahora
    $cashEntries = Order::where('box_id', $recon->box_id)
        ->whereIn('state', [2,5])   // facturado o crédito
        ->where('payment_form_id', '<>', 2)  // ≠ crédito
        ->whereBetween('payment_date', [
            $from->toDateTimeString(),
            $to->toDateTimeString(),
        ])
        ->sum('total_iva_inc');

    // 2) Calcula ventas totales en caja = base inicial + efectivo
    $computed   = $recon->opening_balance + $cashEntries;
    $difference = $data['reported_balance'] - $computed;

    // 3) Actualiza el arqueo guardando también 'entries' y 'difference'
    $recon->update([
        'closing_user_id'   => $request->user()->id,
        'entries'           => $cashEntries,
        'computed_balance'  => $computed,
        'reported_balance'  => $data['reported_balance'],
        'difference'        => $difference,
        'is_open'           => false,
        'closed_at'         => $to,
    ]);

    // Marcar la caja como cerrada
    Box::where('id', $recon->box_id)
        ->update(['is_open' => false]);

    $recon->load(['openingUser', 'closingUser']);

    return response()->json($recon);
}


    /**
     * GET /api/cash-reconciliations/closed?box_id={id}
     * Devuelve el historial de arqueos cerrados para la caja indicada.
     */
    public function closed(Request $request)
    {
        $request->validate([
            'box_id' => 'required|exists:boxes,id',
        ]);

        $list = CashReconciliation::where('box_id', $request->box_id)
            ->where('is_open', false)
            ->with(['openingUser', 'closingUser'])
            ->orderByDesc('closed_at')
            ->get();

        return response()->json($list);
    }

    /**
     * GET /api/cash-reconciliations/session-report?box_id={id}
     * Devuelve un resumen de ventas de la sesión (forma/método de pago)
     * para el arqueo abierto de la caja.
     */
    public function sessionReport(Request $request)
    {
        $request->validate([
            'box_id' => 'required|exists:boxes,id',
        ]);

        $recon = CashReconciliation::where('box_id', $request->box_id)
            ->where('is_open', true)
            ->firstOrFail();

        // Convierte opened_at a instancia Carbon
        $from = Carbon::parse($recon->opened_at);
        $to   = Carbon::now();

        $query = Order::select(
                'orders.payment_form_id',
                'orders.payment_method_id',
                'pf.name as payment_form',
                'pm.name as payment_method',
                DB::raw('COUNT(orders.id) as number_of_orders'),
                DB::raw('SUM(CASE WHEN orders.payment_form_id = 2 THEN orders.total_iva_inc ELSE 0 END) as credit_sales'),
                DB::raw('SUM(CASE WHEN orders.payment_form_id <> 2 THEN orders.total_iva_inc ELSE 0 END) as cash_sales')
            )
            ->join('payment_forms as pf', 'pf.id', '=', 'orders.payment_form_id')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'orders.payment_method_id')
            ->where('orders.box_id', $request->box_id)
            ->whereIn('orders.state', [2,4,5]) // incluir también 4
            ->whereBetween('orders.payment_date', [
                $from->toDateTimeString(),
                $to->toDateTimeString(),
            ])
            ->groupBy('orders.payment_form_id', 'orders.payment_method_id', 'pf.name', 'pm.name')
            ->get();

        $totals = [
            'cash_sales'       => $query->sum('cash_sales'),
            'credit_sales'     => $query->sum('credit_sales'),
            'number_of_orders' => $query->sum('number_of_orders'),
        ];

        return response()->json([
            'session' => $query,
            'totals'  => $totals,
        ]);
    }

    /**
     * GET /api/cash-reconciliations/orders?box_id={id}
     * Devuelve el listado de facturas de la sesión actual,
     * filtrado por caja, forma y método de pago.
     */
    public function orders(Request $request)
    {
        $request->validate([
            'box_id'            => 'required|exists:boxes,id',
            'payment_form_id'   => 'nullable|exists:payment_forms,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
        ]);

        $recon = CashReconciliation::where('box_id', $request->box_id)
            ->where('is_open', true)
            ->firstOrFail();

        // Convierte opened_at a instancia Carbon
        $from = Carbon::parse($recon->opened_at);
        $to   = Carbon::now();

        $query = Order::with('client')
            ->where('box_id', $request->box_id)
            ->whereIn('state', [2,4])  // incluir facturar/imprimir
            ->whereBetween('payment_date', [
                $from->toDateTimeString(),
                $to->toDateTimeString(),
            ]);

        if ($request->filled('payment_form_id')) {
            $query->where('payment_form_id', $request->payment_form_id);
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        $invoices = $query->get();

        return response()->json([
            'orders' => $invoices,
        ]);
    }
}
