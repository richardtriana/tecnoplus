<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\Configuration;
use App\Models\DetailOrder;
use App\Models\Order;
use App\Models\PaymentCredit;
use App\Models\Product;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDF;

class OrderController extends Controller
{

	public function __construct()
	{
		$this->middleware('can:order.index')->only('index', 'generatePdf', 'generatePaymentPdf');
		$this->middleware('can:order.store')->only('store', 'creditByClient', 'payCreditByClient');
		$this->middleware('can:order.update')->only('update');
		$this->middleware('can:order.delete')->only('destroy');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$user_id =  $request->user_id ? $request->user_id  :  Auth::user()->id;
		$sign_user_id =  $user_id == '-1' ? '<>' : '=';

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
			$orders = $orders
				->where('created_at', '>=', $from);
		}

		if ($to != '') {
			$orders = $orders
				->where('created_at', '<=', $to);
		}

		if ($from == '' && $to == '') {
			$orders = $orders
				->where('created_at', '>=', $today);
		}

		if ($status != '') {
			$orders = $orders
				->whereIn('state', [$status]);
		}

		$orders = $orders
			->where('user_id', $sign_user_id, $user_id)
			->with('user:id,name','table:id,table')
			->orderByDesc('id')
			->paginate(10);

		$totalOrders = $this->getTotalOrders($request);

		return response()->json([
			'status' => 'success',
			'code' => 200,
			'orders' => $orders,
			'totalOrders' => $totalOrders->orders
		]);
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
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$user_id =  Auth::user()->id;
		$box = Box::find($request->box_id);
		$bill_number = $this->generateBillNumber($request);

		$order = new Order;
		$order->client_id = $request->id_client;
		$order->user_id = $user_id;
		$order->table_id = $request->table_id ?? $request->table_id;
		$order->no_invoice = $bill_number;
		$order->total_paid = $request->total_tax_inc;
		$order->total_iva_inc = $request->total_tax_inc;
		$order->total_iva_exc = $request->total_tax_exc;
		$order->total_discount = $request->total_discount;
		$order->total_cost_price_tax_inc = $request->total_cost_price_tax_inc;
		$order->observations = $request->observations;
		$order->payment_methods = ($request->payment_methods);
		$order->box_id = $box->id;
		$order->bill_number = $bill_number;

		if ($request->state == 4) {
			$order->state = 2;
			$order->invoiced_by = $user_id;
			$order->payment_date = $request->payment_date ? $request->payment_date : date('Y-m-d h:i:s');
		}
		if ($request->state == 6 || $request->state == 5) {
			$order->state = 5;
		}
		if ($request->state != 4 && $request->state != 6) {
			$order->state = $request->state;
			if ($request->state == 2) {
				$order->invoiced_by = $user_id;
				$order->payment_date = $request->payment_date  ? $request->payment_date :  date('Y-m-d h:i:s');
			}
		}
		$order->save();
		if ($order->state == 5) {
			if ($request->pay_payment > 0) {
				PaymentCredit::create([
					'user_id' => $user_id,
					'order_id' => $order->id,
					'pay' => $request->pay_payment
				]);
			}
		}

		foreach ($request->productsOrder as $details_order) {
			$new_detail = new DetailOrderController;
			$new_detail = $new_detail->store($details_order, $order->id);

			$product_controller = new ProductController;
			if ($order->state == 2 ||   $order->state == 5) {
				$product = Product::find($details_order['product_id']);
				if ($product['type'] == 3) {
					$product_controller->searchKitById(1, $details_order['product_id'], $details_order['quantity'], 1);
				} else {
					$product_controller->updateStockByBarcode(1, $details_order['barcode'], $details_order['quantity']);
				}
			}
		}

		$print = new PrintOrderController();
		if ($request->state == 4 || $request->state == 6) {
			$print->printTicket($order->id, $request->cash, $request->change);
		} else if ($request->state == 1) {
			$print->printTicketRecently($order->id);
		} else {
			$print->openBox($order->id);
		}

		if ($request->table_id) {
			$table = Table::find($request->table_id);
			if ($request->state == 1) {
				$table->state = 'occupied';
			} else {
				$table->state = 'free';
			}
			$table->save();
		}


		return $order->id;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\Order  $order
	 * @return \Illuminate\Http\Response
	 */
	public function show(Order $order)
	{
		$details  = Order::find($order->id);
		return [
			'order_information' => $details, 
			'order_details' => $details->detailOrders()->get(), 
			'user' => $details->user()->first()];
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\Order  $order
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Order $order)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\Order  $order
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$user_id =  Auth::user()->id;
		$box = Box::find($request->box_id);
		$bill_number = $this->generateBillNumber($request);
		$order = Order::find($id);
		$table = $order->table();

		$array1 = $order->detailOrders()->select('product_id', 'quantity')->get();
		$updatedProducts = collect($request->productsOrder);

		foreach ($array1 as $op) {
			foreach ($updatedProducts as $np) {
				if ($op['product_id'] == $np['product_id']) {
					$np['quantity'] =  $np['quantity'] - $op['quantity'] ;
				}
			}
		}

		$order->client_id = $request->id_client;
		$order->table_id = $request->table_id ?? NULL;
		$order->total_paid = $request->total_tax_inc;
		$order->total_iva_inc = $request->total_tax_inc;
		$order->total_iva_exc = $request->total_tax_exc;
		$order->total_cost_price_tax_inc = $request->total_cost_price_tax_inc;
		$order->total_discount = $request->total_discount;
		$order->observations = $request->observations;
		$order->payment_methods = ($request->payment_methods);
		if ($order->state == 3) {
			$order->bill_number = $bill_number;
		}

		if ($request->state == 4) {
			$order->state = 2;
			$order->invoiced_by = $user_id;
			$order->payment_date = $request->payment_date  ? $request->payment_date : date('Y-m-d h:i:s');
		}
		if ($request->state == 6) {
			$order->state = 5;
		}
		if ($request->state != 4 && $request->state != 6) {
			$order->state = $request->state;
			if ($request->state == 2) {
				$order->payment_date = $request->payment_date  ? $request->payment_date : date('Y-m-d h:i:s');
			}
		}

		$order->update();

		if ($order->state == 5) {
			if ($request->pay_payment > 0) {
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
		}

		foreach ($request->productsOrder as $details_order) {

			DetailOrder::updateOrCreate(
				[
					'order_id' => $id, 'product_id' => $details_order['product_id'], 'barcode' => $details_order['barcode']
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
					'product' => $details_order['product']
				]
			);
		}

		$print = new PrintOrderController();
		if ($request->state == 4 || $request->state == 6) {
			$print = $print->printTicket($order->id, $request->cash, $request->change);
		} else if ($request->state == 1) {
			$print->printTicketRecently($order->id, $updatedProducts);
		} else {
			$print->openBox($order->id);
		}

		if ($order->table_id) {
			$table = Table::find($order->table_id);
			if ($request->state == 1) {
				$table->state = 'occupied';
			} else {
				$table->state = 'free';
			}
			$table->save();

			return $table;
		}
		
		return $order;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\Order  $order
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Order $order)
	{
		$details_order = $order->detailOrders()->get();
		if ($order->state == 2 ||   $order->state == 5) {
			foreach ($details_order as $detail_order) {
				$product_controller = new ProductController;
				$product = Product::find($detail_order['product_id']);

				if ($product->type == 3) {
					$product_controller->searchKitById(2, $detail_order['product_id'], $detail_order['quantity']);
				} else {
					$product_controller->updateStockByBarcode(2, $detail_order['barcode'], $detail_order['quantity']);
				}
			}
		}
		$order->delete();
	}

	public function getTotalOrders(Request $request)
	{
		$user_id =  $request->user_id ? $request->user_id  :  Auth::user()->id;
		$sign_user_id =  $user_id == '-1' ? '<>' : '=';

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
			$orders = $orders
				->where('created_at', '>=', $from);
		}

		if ($to != '') {
			$orders = $orders
				->where('created_at', '<=', $to);
		}

		if ($from == '' && $to == '') {
			$orders = $orders
				->where('created_at', '>=', $today);
		}

		if ($status != '') {
			$orders = $orders
				->whereIn('state', [$status]);
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
		$latestOrder = Order::where('box_id', $request->box_id)->where('state', '<>', '3')->orderByDesc('id')->first();
		$box = Box::find($request->box_id);
		$dateValidate = Carbon::now()->toDateString();

		if ($request->state != 3) {
			if ($latestOrder) {

				$lastConsecutive = str_replace($box->prefix, '', $latestOrder->bill_number);
				$lastConsecutive = intval($lastConsecutive);


				$consecutiveBox = $box->consecutiveBox()->where([
					['from_nro', '<=', $lastConsecutive],
					['until_nro', '>=', $lastConsecutive]
				])
					->where('until_date', '>=', $dateValidate)
					->orderBy('from_nro')
					->first();

				if ($consecutiveBox && $consecutiveBox->until_nro > $lastConsecutive) {
					$bill_number = $box->prefix . ($lastConsecutive + 1);
				} else {
					$continue = $box->consecutiveBox()
						->where('until_nro', '>', $lastConsecutive)
						->where('from_date', '<=', $dateValidate)
						->where('until_date', '>=', $dateValidate)
						->where('id', '!=', isset($consecutiveBox->id) ? $consecutiveBox->id : null)
						->orderBy('from_nro')
						->first();

					if (!$continue) {
						return abort(500);
					}

					$bill_number = $box->prefix . $continue->from_nro;
				}
			} else {
				$continue = $box->consecutiveBox()
					->where('from_date', '<=', $dateValidate)
					->where('until_date', '>=', $dateValidate)
					->orderBy('from_nro')
					->first();

				if (!$continue) {
					return abort(500);
				}
				$bill_number = $box->prefix . $continue->from_nro;
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

			$from_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->from_date);
			$until_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->until_date);

			$data['consecutive_expires'] = "Vence: " . $until_date->toDateString() . " Meses Vig. :  " . ($until_date->month - $from_date->month);
		}

		$pdf = PDF::loadView('templates.order', $data);

		$pdf = $pdf->download('prueba.pdf');

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

			$from_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->from_date);
			$until_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->until_date);

			$data['consecutive_expires'] = "Vence: " . $until_date->toDateString() . " Meses Vig. :  " . ($until_date->month - $from_date->month);
		}

		$pdf = PDF::loadView('templates.payments-credit', $data);

		$pdf = $pdf->download('prueba.pdf');

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

	public function payCreditByClient(Request $request)
	{
		$validate = Validator::make($request->all(), [
			'id_client' => 'required|integer|exists:clients,id',
			'pay_payment' =>  'required|numeric|min:1'
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
			->select('o.id', 'o.total_paid', "o.payment_methods", DB::raw('SUM(pc.pay) as  paid_payment'))
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

		$user_id =  Auth::user()->id;

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
	}
}
