<?php

namespace App\Http\Controllers;
<<<<<<< HEAD
use App\Models\OrderCredit;
use App\Models\PaymentCredit;
=======

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
use App\Models\Box;
use App\Models\Configuration;
use App\Models\DetailOrder;
use App\Models\Order;
<<<<<<< HEAD
=======
use App\Models\PaymentCredit;
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
use App\Models\Product;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use Illuminate\Support\Facades\Log;
=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;
use App\Services\FactusInvoiceService;

class OrderController extends Controller
{
    const NRO_RESULTS = 15;
    protected $factusInvoiceService;

    public function __construct(FactusInvoiceService $factusInvoiceService)
    {
        $this->factusInvoiceService = $factusInvoiceService;

        $this->middleware('can:order.index')->only('index', 'generatePdf', 'generatePaymentPdf');
        $this->middleware('can:order.store')->only('store', 'creditByClient', 'payCreditByClient');
        $this->middleware('can:order.update')->only('update');
        $this->middleware('can:order.delete')->only('destroy');
    }
<<<<<<< HEAD
  
=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id = $request->user_id ? $request->user_id : Auth::user()->id;
        $sign_user_id = $user_id == '-1' ? '<>' : '=';
        $perPage = $request->nro_results ?? self::NRO_RESULTS;

        $today = date('Y-m-d');
        $from = $request->from;
        $to = $request->to;
        $status = $request->status ?? $request->status;

        $orders = Order::whereHas('client', function (Builder $query) use ($request) {
            if ($request->client != '') {
                $query->where('name', 'like', "%$request->client%");
            }
        });

        if ($request->table_id) {
            $orders = $orders->where('table_id', $request->table_id);
        }
        if ($request->no_invoice != '') {
            $orders = $orders->where('no_invoice', 'like', "%$request->no_invoice");
        }

        if ($from != '') {
            $from = Carbon::parse($from)->toDateString();
            $orders = $orders->where('created_at', '>=', $from);
        }

        if ($to != '') {
            $to = Carbon::parse($to)->addSeconds(59)->toDateString();
            $orders = $orders->where('created_at', '<=', $to);
        }

        if ($from == '' && $to == '') {
            $orders = $orders->where('created_at', '>=', $today);
        }

        if ($status != '') {
            $orders = $orders->whereIn('state', [$status]);
        }

        $orders = $orders
            ->where('user_id', $sign_user_id, $user_id)
            ->with('user:id,name', 'table:id,table')
            ->orderByDesc('id')
            ->paginate($perPage);

        $totalOrders = $this->getTotalOrders($request);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'orders' => $orders,
            'totalOrders' => $totalOrders->orders
        ]);
    }

    public function reprintList(Request $request)
    {
        $status = $request->query('status', 1);

        $orders = Order::where('state', $status)
            ->with('user:id,name', 'table:id,table')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'code'   => 200,
            'orders' => $orders,
        ]);
    }

    public function create()
    {
        //
    }

<<<<<<< HEAD
    public function store(Request $request)
    {
        $user_id     = $request->filled('id_waiter') ? $request->id_waiter : Auth::user()->id;
        $box         = Box::findOrFail($request->box_id);
        $bill_number = $this->generateBillNumber($request);
    
        // 1) Crear la orden
        $order = new Order;
        $order->client_id                = $request->id_client;
        $order->user_id                  = $user_id;
        $order->table_id                 = $request->table_id ?? null;
        $order->no_invoice               = $bill_number;
        $order->total_paid               = $request->total_tax_inc;
        $order->total_iva_inc            = $request->total_tax_inc;
        $order->total_iva_exc            = $request->total_tax_exc;
        $order->total_discount           = $request->total_discount;
        $order->total_cost_price_tax_inc = $request->total_cost_price_tax_inc ?? 0.0;
        $order->observations             = $request->observations;
        $order->payment_methods          = $request->payment_methods;
        $order->box_id                   = $box->id;
        $order->bill_number              = $bill_number;
        $order->proccess                 = $request->has('proccess') ? $request->proccess : false;
    
        // Nuevos campos Factus
        $order->reference_code      = $request->reference_code;
        $order->numbering_range_id  = $request->numbering_range_id;
        $order->document_code       = $request->document_code;
        $order->cufe                = $request->cufe;
        $order->qr                  = $request->qr;
        $order->validated           = $request->validated;
        $order->qr_image            = $request->qr_image;
        $order->payment_method_code = $request->payment_method_code;
        $order->payment_form_id     = $request->payment_form_id;
        $order->payment_method_id   = $request->payment_method_id;
        $order->factus_response     = $request->factus_response;
    
        // 2) Ajuste de estado e invocación según Forma de Pago
        //    Si es "Pago a crédito" (payment_form_id == 2), respetamos el state recibido (2 o 4)
        $order->state = $request->state;
        if (in_array($request->state, [2, 4])) {
            // Facturado o Facturar e imprimir
            $order->invoiced_by  = $user_id;
            $order->payment_date = $request->payment_date ?: now()->toDateTimeString();
        }
    
        $order->save();
    
        // 3) Si es crédito, creamos registro en order_credits
        if ((int)$order->payment_form_id === 2) {
            $orderCredit = OrderCredit::create([
                'order_id'     => $order->id,
                'client_id'    => $order->client_id,
                'total_credit' => $order->total_paid,
                'balance'      => $order->total_paid,
                'state'        => 'pending',      // o el estado que uses para "pendiente"
                'created_by'   => $user_id,
            ]);
            Log::info('order_credits insert:', $orderCredit->toArray());
    
            // Si viene un abono al crear, lo registramos también
            if ($request->filled('pay_payment') && $request->pay_payment > 0) {
                PaymentCredit::create([
                    'user_id'  => $user_id,
                    'order_id' => $order->id,
                    'pay'      => $request->pay_payment,
                ]);
            }
        }
    
        // 4) Procesar productos y ajustar stock si es facturado
        foreach ($request->productsOrder as $details_order) {
            (new DetailOrderController)->store($details_order, $order->id);
    
            if (in_array($order->state, [2, 4])) {
                $product = Product::find($details_order['product_id']);
                $pc      = new ProductController;
    
                if ($product->stock == 1) {
                    if ($product->type == 3) {
                        $pc->searchKitById(1, $product->id, $details_order['quantity'], 1);
                    } else {
                        $pc->updateStockByBarcode(1, $details_order['barcode'], $details_order['quantity']);
                    }
                }
                if ($product->uses_portions && !empty($details_order['portions'])) {
                    foreach ($details_order['portions'] as $portion) {
                        $descQty = floatval($portion['quantity']) * floatval($details_order['quantity']);
                        $pc->updatePortionStock(1, $product->id, $portion['portion_id'], $descQty);
                    }
                }
            }
        }
    
        // ================================
        // 5) Envío a Factus (solo si el comprobante lo permite)
        // ================================
        $numberingRange = $order->getNumberingRange();
        if (
            in_array($order->state, [2, 4]) &&
            $numberingRange &&
            (int)$numberingRange->enviado_dian === 1
        ) {
            try {
                $factusResponse = $this->factusInvoiceService->sendInvoiceToFactus($order);
                $order->factus_response = json_encode($factusResponse);
                if (!empty($factusResponse['data']['bill'])) {
                    $bill                      = $factusResponse['data']['bill'];
                    $order->factus_bill_id     = $bill['id']     ?? null;
                    $order->factus_status      = $bill['status'] ?? null;
                    $order->factus_bill_number = $bill['number'] ?? null;
                    $order->cufe                = $bill['cufe']   ?? null;
                    $order->qr                  = $bill['qr']     ?? null;
                    $order->qr_image            = $bill['qr_image'] ?? null;
                    if (isset($bill['validated'])) {
                        $order->factus_validated = Carbon::createFromFormat('d-m-Y h:i:s A', $bill['validated']);
                    }
                    $order->status_dian = 1;
                }
                $order->save();
            } catch (\Exception $e) {
                Log::warning("Orden {$order->id} no enviada a Factus: " . $e->getMessage());
                $order->status_dian = 0;
                $order->save();
            }
        } else {
            $order->status_dian = 0;
            $order->save();
        }
    
        // 6) Impresión / apertura de caja según estado
        try {
            $printer = new PrintOrderController();
            if (in_array($request->state, [4, 6])) {
                $printer->printTicket($order->id, $request->cash, $request->change);
            } elseif ($request->state == 1) {
                $printer->printTicketRecently($order->id);
            } else {
                $printer->openBox($order->id);
            }
        } catch (\Throwable $th) {
            // omitimos errores de impresión
        }
    
        // 7) Devolvemos el ID de la orden (y opcionalmente el registro de crédito)
        return response()->json([
            'order_id'     => $order->id,
            'order_credit' => $orderCredit ?? null,
        ], 201);
    }
    

=======
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function store(Request $request)
{
    $user_id = $request->filled('id_waiter') ? $request->id_waiter : Auth::user()->id;
    $box = Box::find($request->box_id);
    $bill_number = $this->generateBillNumber($request);

    $order = new Order;
    $order->client_id = $request->id_client;
    $order->user_id = $user_id;
    $order->table_id = $request->table_id ?? null;
    $order->no_invoice = $bill_number;
    $order->total_paid = $request->total_tax_inc;
    $order->total_iva_inc = $request->total_tax_inc;
    $order->total_iva_exc = $request->total_tax_exc;
    $order->total_discount = $request->total_discount;
    $order->total_cost_price_tax_inc = $request->total_cost_price_tax_inc ?? 0.0;
    $order->observations = $request->observations;
    $order->payment_methods = $request->payment_methods;
    $order->box_id = $box->id;
    $order->bill_number = $bill_number;
    $order->proccess = isset($request->proccess) ? $request->proccess : false;

    // Nuevos campos para Factus
    $order->reference_code = $request->reference_code;
    $order->numbering_range_id = $request->numbering_range_id;
    $order->document_code = $request->document_code;
    $order->cufe = $request->cufe;
    $order->qr = $request->qr;
    $order->validated = $request->validated;
    $order->qr_image = $request->qr_image;
    $order->payment_method_code = $request->payment_method_code;
    $order->payment_form_id = $request->payment_form_id;
    $order->payment_method_id = $request->payment_method_id;
    $order->factus_response = $request->factus_response;

    // Si el estado es 4, se transforma a 2 (facturación)
    if ($request->state == 4) {
        $order->state = 2;
        $order->invoiced_by = $user_id;
        $order->payment_date = $request->payment_date ? $request->payment_date : date('Y-m-d h:i:s');
    } elseif ($request->state == 6 || $request->state == 5) {
        $order->state = 5;
    } else {
        $order->state = $request->state;
        if ($request->state == 2) {
            $order->invoiced_by = $user_id;
            $order->payment_date = $request->payment_date ? $request->payment_date : date('Y-m-d h:i:s');
        }
    }
    $order->save();

    if ($order->state == 5 && $request->pay_payment > 0) {
        PaymentCredit::create([
            'user_id'  => $user_id,
            'order_id' => $order->id,
            'pay'      => $request->pay_payment
        ]);
    }

    // Procesar cada producto del pedido
    foreach ($request->productsOrder as $details_order) {
        $new_detail = new DetailOrderController;
        $new_detail = $new_detail->store($details_order, $order->id);

        $product_controller = new ProductController;
        if ($order->state == 2) {
            $product = Product::find($details_order['product_id']);
            if ($product->stock == 1) {
                if ($product->type == 3) {
                    $product_controller->searchKitById(1, $details_order['product_id'], $details_order['quantity'], 1);
                } else {
                    $product_controller->updateStockByBarcode(1, $details_order['barcode'], $details_order['quantity']);
                }
            }
            if ($product->uses_portions && isset($details_order['portions']) && count($details_order['portions']) > 0) {
                foreach ($details_order['portions'] as $portion) {
                    $descQty = floatval($portion['quantity']) * floatval($details_order['quantity']);
                    $product_controller->updatePortionStock(1, $details_order['product_id'], $portion['portion_id'], $descQty);
                }
            }
        }
    }

    // Enviar factura a Factus si el estado es 2 o 4 (facturación)
    if (in_array($request->state, [2, 4])) {
        try {
            $factusResponse = $this->factusInvoiceService->sendInvoiceToFactus($order);
            $order->factus_response = json_encode($factusResponse);

            if (isset($factusResponse['data'])) {
                $data = $factusResponse['data'];
                if (isset($data['bill'])) {
                    $bill = $data['bill'];
                    $order->factus_bill_id     = $bill['id']     ?? null;
                    $order->factus_status      = $bill['status'] ?? null;
                    $order->factus_bill_number = $bill['number'] ?? null;
                    if (isset($bill['validated'])) {
                        $order->factus_validated = Carbon::createFromFormat('d-m-Y h:i:s A', $bill['validated']);
                    }
                    $order->cufe     = $bill['cufe']     ?? null;
                    $order->qr       = $bill['qr']       ?? null;
                    $order->qr_image = $bill['qr_image'] ?? null;

                    // Nuevo: status_dian según el campo numérico 'status' del bill
                    $order->status_dian = (int) ($bill['status'] ?? 0);
                }
            }
            $order->save();
        } catch (\Exception $e) {
            // Se omiten los logs en esta versión
        }
    }

    try {
        $print = new PrintOrderController();
        if ($request->state == 4 || $request->state == 6) {
            $print->printTicket($order->id, $request->cash, $request->change);
        } elseif ($request->state == 1) {
            $print->printTicketRecently($order->id);
        } else {
            $print->openBox($order->id);
        }
    } catch (\Throwable $th) {
        // Se omiten los logs
    }

    return response()->json($order->id);
}


    public function show(Order $order)
    {
        $details = Order::find($order->id);
        return [
            'order_information' => $details,
            'order_details' => $details->detailOrders()->get(),
            'user' => $details->user()->first()
        ];
    }

    public function edit(Order $order)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $user_id = Auth::user()->id;
        $box = Box::find($request->box_id);
        $bill_number = $this->generateBillNumber($request);
        $order = Order::find($id);
        $table = $order->table();

        $array1 = $order->detailOrders()->select('product_id', 'quantity')->get();
        $updatedProducts = collect($request->productsOrder);
        $newProducts = $updatedProducts->map(function ($item) use ($array1) {
            foreach ($array1 as $op) {
                if ($op['product_id'] == $item['product_id']) {
                    $item['quantity'] = $item['quantity'] - $op['quantity'];
                }
            }
            return $item;
        });

        $order->client_id = $request->id_client;
        $order->table_id = $request->table_id ?? null;
        $order->total_paid = $request->total_tax_inc;
        $order->total_iva_inc = $request->total_tax_inc;
        $order->total_iva_exc = $request->total_tax_exc;
        $order->total_cost_price_tax_inc = $request->total_cost_price_tax_inc ?? 0.0;
        $order->total_discount = $request->total_discount;
        $order->observations = $request->observations;
        $order->proccess = isset($request->proccess) ? $request->proccess : false;
        $order->payment_methods = $request->payment_methods;

        // Nuevos campos para Factus
        $order->reference_code = $request->reference_code;
        $order->numbering_range_id = $request->numbering_range_id;
        $order->document_code = $request->document_code;
        $order->cufe = $request->cufe;
        $order->qr = $request->qr;
        $order->validated = $request->validated;
        $order->qr_image = $request->qr_image;
        $order->payment_method_code = $request->payment_method_code;
        $order->payment_form_id = $request->payment_form_id;
        $order->payment_method_id = $request->payment_method_id;
        $order->factus_response = $request->factus_response;

        // Si la orden era de tipo "Pedido" (estado 1) y se pasa a "Facturado" (estado 2 o 4),
        // se actualiza el bill number generando uno nuevo.
        if ($order->state == 1 && in_array($request->state, [2, 4])) {
            $order->bill_number = $bill_number;
        }
        
        // Mantenimiento de la lógica existente para otros casos
        if ($order->state == 3) {
            $order->bill_number = $bill_number;
        }

        if ($request->state == 4) {
            $order->state = 2;
            $order->invoiced_by = $user_id;
            $order->payment_date = $request->payment_date ? $request->payment_date : date('Y-m-d h:i:s');
        } elseif ($request->state == 6 || $request->state == 5) {
            $order->state = 5;
        } else {
            $order->state = $request->state;
            if ($request->state == 2) {
                $order->payment_date = $request->payment_date ? $request->payment_date : date('Y-m-d h:i:s');
            }
        }

        $order->created_at = date('Y-m-d H:i:s');
        $order->update();

        if ($order->state == 5 && $request->pay_payment > 0) {
            $paymentCredit = $order->paymentCredits->first();
            if ($paymentCredit) {
                $paymentCredit->pay = $request->pay_payment;
                $paymentCredit->update();
            } else {
                PaymentCredit::create([
                    'user_id' => $user_id,
                    'order_id' => $order->id,
                    'pay' => $request->pay_payment
                ]);
            }
        }

        foreach ($request->productsOrder as $details_order) {
            DetailOrder::updateOrCreate(
                [
                    'order_id' => $id,
                    'product_id' => $details_order['product_id'],
                    'barcode' => $details_order['barcode']
                ],
                [
                    'discount_percentage' => $details_order['discount_percentage'],
                    'discount_price' => $details_order['discount_price'],
                    'price_tax_exc' => $details_order['price_tax_exc'],
                    'price_tax_inc' => $details_order['price_tax_inc'],
                    'price_tax_inc_total' => $details_order['price_tax_inc_total'],
                    'cost_price_tax_inc' => $details_order['cost_price_tax_inc'],
                    'cost_price_tax_inc_total' => $details_order['cost_price_tax_inc_total'],
                    'quantity' => $details_order['quantity'],
                    'product' => $details_order['product'],
                    'tax_rate' => $details_order['tax_rate'] ?? null
                ]
            );
        }

        // Si el nuevo estado es 2 (facturación) se procesa el descuento de productos y porciones.
        if ($order->state == 2) {
            foreach ($request->productsOrder as $details_order) {
                $product_controller = new ProductController;
                $product = Product::find($details_order['product_id']);

                // Descontar stock del producto principal solo si "stock" es 1
                if ($product->stock == 1) {
                    if ($product->type == 3) {
                        $product_controller->searchKitById(1, $details_order['product_id'], $details_order['quantity'], 1);
                    } else {
                        $product_controller->updateStockByBarcode(1, $details_order['barcode'], $details_order['quantity']);
                    }
                }

                // Descontar stock de las porciones si el producto usa porciones,
                // sin importar el valor de "stock" en el producto principal.
                if ($product->uses_portions && isset($details_order['portions']) && count($details_order['portions']) > 0) {
                    foreach ($details_order['portions'] as $portion) {
                        $descQty = floatval($portion['quantity']) * floatval($details_order['quantity']);
                        $product_controller->updatePortionStock(1, $details_order['product_id'], $portion['portion_id'], $descQty);

                    }
                }
            }
        }

        // Enviar la factura a Factus si el estado es 2 o 4.
        if (in_array($request->state, [2, 4])) {
            try {
                $factusResponse = $this->factusInvoiceService->sendInvoiceToFactus($order);
                $order->factus_response = json_encode($factusResponse);
                if (isset($factusResponse['data'])) {
                    $data = $factusResponse['data'];
                    if (isset($data['bill'])) {
                        $bill = $data['bill'];
                        $order->factus_bill_id = $bill['id'] ?? null;
                        $order->factus_status = $bill['status'] ?? null;
                        $order->factus_bill_number = $bill['number'] ?? null;
                        if (isset($bill['validated'])) {
                            $order->factus_validated = Carbon::createFromFormat('d-m-Y h:i:s A', $bill['validated']);
                        }
                        $order->cufe = $bill['cufe'] ?? null;
                        $order->qr = $bill['qr'] ?? null;
                        $order->qr_image = $bill['qr_image'] ?? null;
                    }
                }
                $order->save();
            } catch (\Exception $e) {
                // Sin logs
            }
        }

        $print = new PrintOrderController();
        if ($request->state == 4 || $request->state == 6) {
            $print->printTicket($order->id, $request->cash, $request->change);
        } elseif ($request->state == 1) {
            $print->printTicketRecently($order->id, $newProducts);
        } else {
            $print->openBox($order->id);
        }

        return response()->json($order);
    }
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

    public function destroy(Order $order)
    {
        $details_order = $order->detailOrders()->get();
        if ($order->state == 2 || $order->state == 5) {
            foreach ($details_order as $detail_order) {
                $product_controller = new ProductController;
                $product = Product::find($detail_order['product_id']);

                // Restaurar stock del producto principal solo si "stock" es 1
                if ($product->stock == 1) {
                    if ($product->type == 3) {
                        $product_controller->searchKitById(2, $detail_order['product_id'], $detail_order['quantity']);
                    } else {
                        $product_controller->updateStockByBarcode(2, $detail_order['barcode'], $detail_order['quantity']);
                    }
                }

                // Restaurar stock de las porciones si existen en el detalle
                if (isset($detail_order['portions']) && count($detail_order['portions']) > 0) {
                    foreach ($detail_order['portions'] as $portion) {
                        $product_controller->updatePortionStock(2, $detail_order['product_id'], $portion['portion_id'], $portion['quantity']);
                    }
                }
            }
        }
        $order->update([
            'state' => 0,
            'deleted_at' => now()
        ]);
    }
    
    /**
     * Elimina un producto de la orden y actualiza el stock y totales.
     * Además, envía a impresión una comanda de eliminación para el producto removido.
     *
     * @param Request $request
     * @param int $orderId
     * @param int $detailId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeProductFromOrder(Request $request, $orderId, $detailId)
    {
        // 1. Recuperar la orden y el detalle a eliminar
        $order = Order::findOrFail($orderId);
        $detail = $order->detailOrders()->where('id', $detailId)->first();

        if (!$detail) {
            return response()->json([
                'status' => 'error',
                'message' => 'Detalle de la orden no encontrado.'
            ], 404);
        }

        // 2. Actualizar el stock si la orden está en un estado que afecta inventario
        if (in_array($order->state, [2, 5])) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $product_controller = new ProductController;
                // Restaurar stock principal solo si "stock" es 1
                if ($product->stock == 1) {
                    if ($product->type == 3) {
                        $product_controller->searchKitById(2, $detail->product_id, $detail->quantity);
                    } else {
                        $product_controller->updateStockByBarcode(2, $detail->barcode, $detail->quantity);
                    }
                }
                // Restaurar stock de porciones, si las hubiera
                if (isset($detail->portions) && count($detail->portions) > 0) {
                    foreach ($detail->portions as $portion) {
                        $descQty = floatval($portion['quantity']) * floatval($details_order['quantity']);
                        $product_controller->updatePortionStock(1, $details_order['product_id'], $portion['portion_id'], $descQty);

                    }
                }
            }
        }

        // 3. Guardar datos del detalle a eliminar para imprimir
        $detailData = (object) [
            'product_id'    => $detail->product_id,
            'quantity'      => $detail->quantity,
            'barcode'       => $detail->barcode,
            'observaciones' => $detail->observaciones
        ];

        // 4. Eliminar el detalle de la orden
        $detail->delete();

        // 5. Recalcular totales de la orden
        $newTotal = $order->detailOrders()
            ->sum(\DB::raw('quantity * price_tax_inc - quantity * price_tax_inc * (discount_percentage / 100)'));
        $order->total_paid = $newTotal;
        $order->update();

        // 6. Imprimir la comanda de eliminación para el producto removido
        $printController = new PrintOrderController();
        $printController->printRemovalTicket($order, $detailData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Producto eliminado y comanda de eliminación enviada.'
        ], 200);
    }

    public function getTotalOrders(Request $request)
    {
        $user_id = $request->user_id ? $request->user_id : Auth::user()->id;
        $sign_user_id = $user_id == '-1' ? '<>' : '=';

        $today = date('Y-m-d');
        $from = $request->from;
        $to = $request->to;
        $status = $request->status ?? $request->status;

        $orders = Order::whereHas('client', function (Builder $query) use ($request) {
            if ($request->client != '') {
                $query->where('name', 'like', "%$request->client%");
            }
        });
        if ($request->no_invoice != '') {
            $orders = $orders->where('no_invoice', 'like', "%$request->no_invoice");
        }

        if ($from != '') {
            $orders = $orders->where('created_at', '>=', $from);
        }

        if ($to != '') {
            $orders = $orders->where('created_at', '<=', $to);
        }

        if ($from == '' && $to == '') {
            $orders = $orders->where('created_at', '>=', $today);
        }

        if ($status != '') {
            $orders = $orders->whereIn('state', [$status]);
        }

        $orders = $orders
            ->selectRaw('SUM(total_paid) as total_paid')
            ->selectRaw('SUM(total_discount) as total_discount')
            ->selectRaw('SUM(total_iva_exc) as total_iva_exc')
            ->where('user_id', $sign_user_id, $user_id)
            ->first();

        return (object)['orders' => $orders];
    }

    public function generateBillNumber(Request $request)
    {
        // Obtenemos la última orden para la caja indicada (excepto las del estado 3)
        $latestOrder = Order::where('box_id', $request->box_id)
            ->where('state', '<>', '3')
            ->orderByDesc('id')
            ->first();

        $box = Box::find($request->box_id);
        $dateValidate = Carbon::now()->toDateString();

        // Obtenemos los rangos asociados a la caja usando la relación many-to-many definida en Box
        $numberingRanges = $box->numberingRanges();

        // Buscamos el rango asociado al numbering_range_id enviado
        $range = $numberingRanges->where('numbering_ranges.id', $request->numbering_range_id)->first();

        if ($request->state != 3) {
            // Si el rango es permanente (start_date y end_date son nulos), usamos el campo current
            if ($range && is_null($range->start_date) && is_null($range->end_date)) {
                $bill_number = $range->prefix . $range->current;
                $range->current = $range->current + 1;
                $range->save();
                return $bill_number;
            }

            if ($latestOrder) {
                // Se remueve el prefijo del número actual usando el prefijo del rango
                $lastConsecutive = str_replace($range->prefix, '', $latestOrder->bill_number);
                $lastConsecutive = intval($lastConsecutive);

                // Buscamos el rango que contenga el número actual
                $consecutiveRange = $numberingRanges->where([
                        ['from', '<=', $lastConsecutive],
                        ['to', '>=', $lastConsecutive]
                    ])
                    ->where('end_date', '>=', $dateValidate)
                    ->orderBy('from')
                    ->first();

                if ($consecutiveRange && $consecutiveRange->to > $lastConsecutive) {
                    $bill_number = $consecutiveRange->prefix . ($lastConsecutive + 1);
                } else {
                    $continue = $numberingRanges
                        ->where('to', '>', $lastConsecutive)
                        ->where('start_date', '<=', $dateValidate)
                        ->where('end_date', '>=', $dateValidate)
                        ->where('numbering_ranges.id', '!=', isset($consecutiveRange->id) ? $consecutiveRange->id : null)
                        ->orderBy('from')
                        ->first();

                    if (!$continue) {
                        return abort(500, 'No se encontró un rango de numeración válido.');
                    }

                    $bill_number = $continue->prefix . $continue->from;
                }
            } else {
                $continue = $numberingRanges
                    ->where('start_date', '<=', $dateValidate)
                    ->where('end_date', '>=', $dateValidate)
                    ->orderBy('from')
                    ->first();

                if (!$continue) {
                    return abort(500, 'No se encontró un rango de numeración para la fecha actual.');
                }
                $bill_number = $continue->prefix . $continue->from;
            }
        } else {
            $bill_number = strtoupper(Str::random(10));
        }

        return $bill_number;
    }

    public function generatePdf(Order $order)
    {
        $data = [
            'orderInformation' => $order,
            'orderDetails' => $order->detailOrders()->get(),
            'user' => $order->user()->first(),
            'configuration' => Configuration::first(),
            'url' => URL::to('/'),
            'consecutiveBox' => $order->consecutiveBox()
        ];

        if ($data['consecutiveBox']) {
            $from_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->start_date);
            $until_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->end_date);

            $data['consecutive_expires'] = "Vence: " . $until_date->toDateString() . " Meses Vig. :  " . ($until_date->month - $from_date->month);
        }

        $pdf = PDF::loadView('templates.order', $data);
        $pdf = $pdf->download('precuenta.pdf');

        $data = [
            'status' => 200,
            'pdf' => base64_encode($pdf),
            'message' => 'Orden generada en pdf'
        ];

        return response()->json($data);
    }

    public function generatePaymentPdf(Order $order, Request $request)
    {
        $payment_id = $request->payment_id;

        $data = [
            'creditInformation' => $order,
            'orderDetails' => $order->detailOrders()->get(),
            'user' => $order->user()->first(),
            'configuration' => Configuration::first(),
            'payment_id' => $payment_id,
            'url' => URL::to('/'),
            'consecutiveBox' => $order->consecutiveBox()
        ];

        if ($data['consecutiveBox']) {
            $from_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->start_date);
            $until_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->end_date);

            $data['consecutive_expires'] = "Vence: " . $until_date->toDateString() . " Meses Vig. :  " . ($until_date->month - $from_date->month);
        }

        $pdf = PDF::loadView('templates.payments-credit', $data);
        $pdf = $pdf->download('precuenta.pdf');

        $data = [
            'status' => 200,
            'pdf' => base64_encode($pdf),
            'message' => 'Orden generada en pdf'
        ];

        return response()->json($data);
    }

    public function creditByClient($clientId)
    {
        $orders = Order::where('client_id', $clientId)->where('state', 5)->get();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'orders' => $orders,
        ]);
    }

<<<<<<< HEAD


    public function show(Order $order)
    {
        $details = Order::find($order->id);
        return [
            'order_information' => $details,
            'order_details' => $details->detailOrders()->get(),
            'user' => $details->user()->first()
        ];
    }

    /**
     * Registra un abono sobre los créditos pendientes de un cliente.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payCreditByClient(Request $request)
    {
        $request->validate([
            'id_client'   => 'required|integer|exists:clients,id',
            'pay_payment' => 'required|numeric|min:1',
        ]);

        $amountToPay = $request->pay_payment;
        $userId      = Auth::id();

        // 1) Obtener todos los créditos pendientes de ese cliente
        $credits = OrderCredit::whereHas('order', function($q) use ($request) {
                $q->where('client_id', $request->id_client)
                  ->where('payment_form_id', 2);
            })
            ->where('status', 'pending')
            ->with('payments')
            ->orderBy('created_at')
            ->get();

        if ($credits->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'No se encontraron créditos pendientes para este cliente.',
            ], 404);
        }

        // 2) Calcular total pendiente
        $totalPending = $credits->sum(function($credit) {
            $paid = $credit->payments->sum('pay');
            return $credit->total_credit - $paid;
        });

        if ($amountToPay > $totalPending) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'El monto a abonar excede el saldo total pendiente.',
            ], 400);
        }

        // 3) Repartir abono entre los créditos pendientes
        DB::transaction(function() use ($credits, &$amountToPay, $userId) {
            foreach ($credits as $credit) {
                if ($amountToPay <= 0) break;

                $paidSoFar = $credit->payments->sum('pay');
                $pending   = $credit->total_credit - $paidSoFar;

                // Si ya está saldado, lo marcamos
                if ($pending <= 0) {
                    $credit->status = 'paid';
                    $credit->save();
                    continue;
                }

                // Cuánto aplicamos a este crédito
                $toApply = min($pending, $amountToPay);

                // Registrar el pago parcial
                PaymentCredit::create([
                    'user_id'  => $userId,
                    'order_id' => $credit->order_id,
                    'pay'      => $toApply,
                ]);

                // Actualizar saldo del crédito
                $credit->balance = $pending - $toApply;
                if ($credit->balance <= 0) {
                    $credit->status = 'paid';
                }
                $credit->save();

                // Reducir lo que aún nos queda por abonar
                $amountToPay -= $toApply;
            }
        });

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Abonos registrados correctamente.',
        ], 200);
=======
    public function payCreditByClient(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id_client' => 'required|integer|exists:clients,id',
            'pay_payment' => 'required|numeric|min:1'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Validación de datos incorrecta',
                'errors' => $validate->errors(),
            ]);
        }
        $orders = DB::table('orders as o')
            ->leftJoin('payment_credits as pc', 'pc.order_id', '=', 'o.id')
            ->select('o.id', 'o.total_paid', 'o.payment_methods', DB::raw('SUM(pc.pay) as paid_payment'))
            ->where('o.client_id', $request->id_client)
            ->where('o.state', 5)
            ->groupByRaw('id, total_paid')
            ->orderBy('o.created_at')
            ->get();

        if ($request->pay_payment > $orders->sum('total_paid')) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validación de pago incorrecta',
            ]);
        }

        $user_id = Auth::user()->id;

        foreach ($orders as $order) {
            if ($request->pay_payment > 0) {

                $payment_methods = (object)['pay_payment' => 0, 'cash' => 0];
                $order->payment_methods = $order->payment_methods ? json_decode($order->payment_methods) : $payment_methods;
                $pending = $order->total_paid - $order->paid_payment;

                if ($pending > $request->pay_payment) {
                    PaymentCredit::create([
                        'user_id' => $user_id,
                        'order_id' => $order->id,
                        'pay' => $request->pay_payment
                    ]);

                    Order::where('id', $order->id)->update([
                        'payment_methods->pay_payment' => $order->payment_methods->pay_payment += $request->pay_payment,
                        'payment_methods->cash' => $order->payment_methods->cash += $request->pay_payment
                    ]);
                    echo $order->id . " ";
                    echo 'pago total';
                    $request->pay_payment = 0;
                } else {
                    PaymentCredit::create([
                        'user_id' => $user_id,
                        'order_id' => $order->id,
                        'pay' => $pending
                    ]);
                    echo 'abono';
                    Order::where('id', $order->id)->update([
                        'state' => 2,
                        'payment_methods->pay_payment' => $order->payment_methods->pay_payment += $pending,
                        'payment_methods->cash' => $order->payment_methods->cash += $pending
                    ]);

                    $request->pay_payment = $request->pay_payment - $pending;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Abonos realizados correctamente',
        ]);
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    }

    public function ordersForKitchen(Request $request)
    {
        $perPage = $request->nro_results ?? self::NRO_RESULTS;
    
        $orders = Order::where('state', 1)
            ->where('proccess', false)
            ->with([
                'user:id,name',
                'table:id,table',
                'detailOrders'
            ])
            ->orderBy('created_at')
            ->paginate($perPage);
    
        $totalOrders = $this->getTotalOrders($request);
    
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'orders' => $orders,
            'totalOrders' => $totalOrders->orders
        ]);
    }
    
    public function prepareOrderKitchen(Order $order)
    {
        $order->proccess = true;
        $order->save();

        return [
            'order_information' => $order
        ];
    }
    
    /**
     * MÉTODO PARA IMPRIMIR LA PRECUENTA
     * Genera un PDF con la lista de productos y sus valores.
     */
    public function printPrecuenta($id)
    {
        $order = Order::findOrFail($id);
        $data = [
            'orderInformation' => $order,
            'orderDetails' => $order->detailOrders()->get(),
            'configuration' => Configuration::first(),
            'url' => URL::to('/'),
        ];

        // Generar PDF usando la vista 'templates.order'
        $pdf = PDF::loadView('templates.order', $data);
        $pdf = $pdf->download('precuenta.pdf');

        $data = [
            'status' => 200,
            'pdf' => base64_encode($pdf),
            'message' => 'Orden generada en pdf'
        ];

        return response()->json($data);
    }

    /**
     * MÉTODO PARA REIMPRIMIR ORDEN
     * Llama a printTicketRecently con $reprint = true
     */
    public function reprint($id)
    {
        $order = Order::findOrFail($id);

        try {
            $printOrder = new PrintOrderController();
            // Pasamos true como tercer parámetro para que aparezca "REIMPRESION"
            $printOrder->printTicketRecently($id, null, true);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo reimprimir la orden',
                'error' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Orden reimpresa correctamente'
        ]);
    }

    public function getOrderByBill($bill_id)
    {
        // Buscar la orden que tenga factus_bill_id igual a $bill_id
        $order = Order::where('factus_bill_id', $bill_id)
                      ->with('detailOrders') // Otras relaciones que necesites
                      ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Orden no encontrada'
            ], 404);
        }

        return response()->json([
            'order_details' => $order->detailOrders
        ]);
    }
<<<<<<< HEAD

/**
 * Reenvía la factura a DIAN (Factus) creando una nueva.
 *
 * @param  int  $id
 * @return \Illuminate\Http\JsonResponse
 */
public function resendDian($id)
{
    // 1) Recuperar la orden
    $order = Order::findOrFail($id);

    // 2) Limpiar campos de Factus para forzar nueva factura
    $order->fill([
        'cufe'                  => null,
        'qr'                    => null,
        'qr_image'              => null,
        'factus_status'         => null,
        'factus_bill_id'        => null,
        'factus_bill_number'    => null,
        'factus_response'       => null,
        'status_dian'           => 0,
    ]);

    // 3) Generar nuevo número de factura y referencia
    $fakeRequest = new Request([
        'box_id'               => $order->box_id,
        'numbering_range_id'   => $order->numbering_range_id,
        'state'                => 2, // 2 = facturado
    ]);
    $newBillNumber = $this->generateBillNumber($fakeRequest);

    $order->bill_number    = $newBillNumber;
    $order->no_invoice     = $newBillNumber;
    $order->reference_code = $newBillNumber;

    // 4) Marcar como facturado
    $order->state        = 2;
    $order->invoiced_by  = auth()->id();
    $order->payment_date = Carbon::now();

    $order->save();

    try {
        // 5) Enviar a Factus
        $response = $this->factusInvoiceService->sendInvoiceToFactus($order);

        // 6) Extraer datos de la respuesta
        $bill = data_get($response, 'data.bill', []);

        // 7) Actualizar con los datos devueltos
        $order->fill([
            'cufe'                  => $bill['cufe']            ?? null,
            'qr'                    => $bill['qr']              ?? null,
            'qr_image'              => $bill['qr_image']        ?? null,
            'factus_status'         => $bill['status']          ?? null,
            'factus_bill_id'        => $bill['id']              ?? null,
            'factus_bill_number'    => $bill['number']          ?? null,
            'factus_response'       => json_encode($response, JSON_UNESCAPED_UNICODE),
            'status_dian'           => 1,
        ]);
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Nueva factura creada y almacenada correctamente en Factus.',
            'data'    => $response,
        ], 200);

    } catch (\Exception $e) {
        Log::error("Error reenviando (nuevo) factura ID {$id}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al enviar nueva factura a Factus.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

/**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user_id     = Auth::user()->id;
        $box         = Box::find($request->box_id);
        $bill_number = $this->generateBillNumber($request);
        $order       = Order::findOrFail($id);

        // Para reimpresión de comanda
        $originalDetails = $order->detailOrders()->select('product_id', 'quantity')->get();
        $updatedProducts = collect($request->productsOrder)->map(function($item) use ($originalDetails) {
            foreach ($originalDetails as $orig) {
                if ($orig->product_id == $item['product_id']) {
                    $item['quantity'] -= $orig->quantity;
                }
            }
            return $item;
        });

        // Actualizar campos básicos
        $order->client_id                  = $request->id_client;
        $order->table_id                   = $request->table_id ?? null;
        $order->total_paid                 = $request->total_tax_inc;
        $order->total_iva_inc              = $request->total_tax_inc;
        $order->total_iva_exc              = $request->total_tax_exc;
        $order->total_cost_price_tax_inc   = $request->total_cost_price_tax_inc ?? 0.0;
        $order->total_discount             = $request->total_discount;
        $order->observations               = $request->observations;
        $order->proccess                   = $request->has('proccess') ? $request->proccess : false;
        $order->payment_methods            = $request->payment_methods;

        // Nuevos campos Factus
        $order->reference_code      = $request->reference_code;
        $order->numbering_range_id  = $request->numbering_range_id;
        $order->document_code       = $request->document_code;
        $order->cufe                = $request->cufe;
        $order->qr                  = $request->qr;
        $order->validated           = $request->validated;
        $order->qr_image            = $request->qr_image;
        $order->payment_method_code = $request->payment_method_code;
        $order->payment_form_id     = $request->payment_form_id;
        $order->payment_method_id   = $request->payment_method_id;
        $order->factus_response     = $request->factus_response;

        // Recalcular bill_number si cambia a facturado o cotización
        if (($order->state == 1 && in_array($request->state, [2,4])) || $order->state == 3) {
            $order->bill_number = $bill_number;
        }

        // Ajuste de estado e invocación de fecha/usuario
        if ($request->state == 4) {
            $order->state        = 2;
            $order->invoiced_by  = $user_id;
            $order->payment_date = $request->payment_date ?: now()->toDateTimeString();
        } elseif (in_array($request->state, [5,6])) {
            $order->state = 5;
        } else {
            $order->state = $request->state;
            if ($request->state == 2) {
                $order->payment_date = $request->payment_date ?: now()->toDateTimeString();
            }
        }

        // Mantener created_at original
        $order->created_at = $order->created_at;
        $order->save();

        // Manejar abonos en crédito
        if ($order->state == 5 && $request->pay_payment > 0) {
            $pc = $order->paymentCredits->first();
            if ($pc) {
                $pc->pay = $request->pay_payment;
                $pc->save();
            } else {
                PaymentCredit::create([
                    'user_id'  => $user_id,
                    'order_id' => $order->id,
                    'pay'      => $request->pay_payment,
                ]);
            }
        }

        // Guardar o actualizar detalles
        foreach ($request->productsOrder as $detail) {
            DetailOrder::updateOrCreate(
                ['order_id' => $order->id, 'product_id' => $detail['product_id'], 'barcode' => $detail['barcode']],
                [
                    'quantity'                 => $detail['quantity'],
                    'price_tax_exc'            => $detail['price_tax_exc'],
                    'price_tax_inc'            => $detail['price_tax_inc'],
                    'discount_percentage'      => $detail['discount_percentage'],
                    'discount_price'           => $detail['discount_price'],
                    'price_tax_inc_total'      => $detail['price_tax_inc_total'],
                    'cost_price_tax_inc'       => $detail['cost_price_tax_inc'],
                    'cost_price_tax_inc_total' => $detail['cost_price_tax_inc_total'],
                    'product'                  => $detail['product'],
                    'tax_rate'                 => $detail['tax_rate'] ?? null,
                    'observaciones'            => $detail['observaciones'] ?? null,
                ]
            );
        }

        // Ajustar stock si facturado
        if ($order->state == 2) {
            $pc = new ProductController;
            foreach ($request->productsOrder as $d) {
                $product = Product::find($d['product_id']);
                if ($product->stock == 1) {
                    if ($product->type == 3) {
                        $pc->searchKitById(1, $d['product_id'], $d['quantity'], 1);
                    } else {
                        $pc->updateStockByBarcode(1, $d['barcode'], $d['quantity']);
                    }
                }
                if ($product->uses_portions && !empty($d['portions'])) {
                    foreach ($d['portions'] as $portion) {
                        $qty = floatval($portion['quantity']) * floatval($d['quantity']);
                        $pc->updatePortionStock(1, $d['product_id'], $portion['portion_id'], $qty);
                    }
                }
            }
        }

        // ======= LÓGICA DE ENVÍO A FACTUS/DIAN PROTEGIDA =======
        $order->status_dian = 0; // por defecto
        $numberingRange    = $order->getNumberingRange();

        if (in_array($request->state, [2,4])
            && $numberingRange
            && (int)$numberingRange->enviado_dian === 1
        ) {
            try {
                $resp = $this->factusInvoiceService->sendInvoiceToFactus($order);
                $order->factus_response = json_encode($resp);
                if (!empty($resp['data']['bill'])) {
                    $bill = $resp['data']['bill'];
                    $order->factus_bill_id     = $bill['id']     ?? null;
                    $order->factus_status      = $bill['status'] ?? null;
                    $order->factus_bill_number = $bill['number'] ?? null;
                    $order->cufe                = $bill['cufe']   ?? null;
                    $order->qr                  = $bill['qr']     ?? null;
                    $order->qr_image            = $bill['qr_image'] ?? null;
                    if (isset($bill['validated'])) {
                        $order->factus_validated = Carbon::createFromFormat('d-m-Y h:i:s A', $bill['validated']);
                    }
                    $order->status_dian = 1;
                }
                $order->save();
                $result['status_dian'] = $order->status_dian;
            } catch (\Exception $e) {
                Log::warning("Orden {$order->id} no enviada a Factus: " . $e->getMessage());
                // deja status_dian en 0
            }
        } else {
            // comprobante no marcado para DIAN, status_dian queda en 0
            $order->save();
            $result['status_dian'] = 0;
        }

        // Impresión / apertura de caja
        try {
            $printer = new PrintOrderController();
            if (in_array($request->state, [4,6])) {
                $printer->printTicket($order->id, $request->cash, $request->change);
            } elseif ($request->state == 1) {
                $printer->printTicketRecently($order->id, $updatedProducts);
            } else {
                $printer->openBox($order->id);
            }
        } catch (\Throwable $th) {
            // omitimos errores de impresión
        }

        return response()->json($result);
    }
=======
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
}
