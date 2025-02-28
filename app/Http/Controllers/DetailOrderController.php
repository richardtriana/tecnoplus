<?php

namespace App\Http\Controllers;

use App\Models\DetailOrder;
use App\Models\Order;
use Illuminate\Http\Request;

class DetailOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:order.index')->only('index');
        $this->middleware('can:order.store')->only('store');
        $this->middleware('can:order.update')->only('update');
        $this->middleware('can:order.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created DetailOrder in storage.
     *
     * @param  array  $request  Array con datos del detalle (similar a request).
     * @param  int    $order_id ID de la orden a la que pertenece el detalle.
     * @return \Illuminate\Http\Response
     */
    public function store($request, $order_id)
    {
        // Crea y asigna cada campo a DetailOrder
        $detail = new DetailOrder;
        $detail->order_id = $order_id;
        $detail->product_id = $request['product_id'];
        $detail->barcode = $request['barcode'];
        $detail->discount_percentage = $request['discount_percentage'];
        $detail->discount_price = $request['discount_price'];
        $detail->price_tax_exc = $request['price_tax_exc'];
        $detail->price_tax_inc = $request['price_tax_inc'];
        $detail->price_tax_inc_total = $request['price_tax_inc_total'];
        $detail->cost_price_tax_inc = $request['cost_price_tax_inc'];
        $detail->cost_price_tax_inc_total = $request['cost_price_tax_inc_total'];
        $detail->quantity = $request['quantity'];
        $detail->product = $request['product'];

        // Asigna las observaciones si existen en el request
        $detail->observaciones = isset($request['observaciones']) ? $request['observaciones'] : null;

        // Guarda el registro
        $detail->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DetailOrder  $detailOrder
     * @return \Illuminate\Http\Response
     */
    public function show(DetailOrder $detailOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DetailOrder  $detailOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(DetailOrder $detailOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DetailOrder  $detailOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DetailOrder $detailOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $detailOrder = DetailOrder::find($id);
        $updateOrder = $detailOrder->replicate();

        $order = Order::find($detailOrder->order_id);
        $array1 = $order->detailOrders()->select('product_id', 'quantity')->get();
        $updatedProducts = collect([0 => $updateOrder]);

        $newProducts = $updatedProducts->map(function ($item) use ($array1) {
            foreach ($array1 as $op) {
                if ($op['product_id'] == $item['product_id']) {
                    $item['quantity'] = $op['quantity'] * -1;
                }
            }
            return $item;
        });

        $print = new PrintOrderController();
        $print->printTicketRecently($detailOrder->order_id, $newProducts);

        // Realiza la eliminación suave
        $detailOrder->update([
            'quantity' => $detailOrder->quantity * -1
        ]);
        $detailOrder->delete();

        return $detailOrder;
    }
}
