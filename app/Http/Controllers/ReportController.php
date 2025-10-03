<?php

namespace App\Http\Controllers;

use App\Models\DetailOrder;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class ReportController extends Controller
{
    const NRO_RESULTS = 15;

    public function __construct()
    {
        $this->middleware('can:report.index');
    }

<<<<<<< HEAD

	public function reportSales(Request $request)
=======
    public function reportSales(Request $request)
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    {
        $from        = $request->input('from');
        $to          = $request->input('to');
        $status      = $request->input('status');
        $noInvoice   = $request->input('no_invoice');
        $statusDian  = $request->input('status_dian');
        $perPage     = $request->input('per_page', 15);

        $query = Order::with(['client', 'paymentForm', 'paymentMethod'])
            ->when($from, function ($q, $from) {
                $q->where('orders.created_at', '>=',
                    Carbon::parse($from)->toDateTimeString()
                );
            })
            ->when($to, function ($q, $to) {
                $q->where('orders.created_at', '<=',
                    Carbon::parse($to)->addSeconds(59)->toDateTimeString()
                );
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                if ((int)$status === 2) {
                    // Estado "Facturado" agrupa 2 y 4
                    $q->whereIn('orders.state', [2, 4]);
                } else {
                    $q->where('orders.state', $status);
                }
            })
            ->when($noInvoice, function ($q, $noInvoice) {
                $q->where(function ($q2) use ($noInvoice) {
                    $q2->where('orders.bill_number', 'like', "%{$noInvoice}%")
                       ->orWhere('orders.no_invoice',   'like', "%{$noInvoice}%");
                });
            })
            ->when($statusDian !== null && $statusDian !== '', function ($q) use ($statusDian) {
                $q->where('orders.status_dian', $statusDian);
            })
            ->orderBy('orders.created_at', 'desc');
<<<<<<< HEAD

        // devuelve paginado con la estructura: current_page, data, last_page, etc.
        $paginated = $query->paginate($perPage);

        return response()->json([
            'orders' => $paginated
        ]);
    }
	


	public function reportGeneralSales(Request $request)
	{
		$from      = $request->from;
		$to        = $request->to;
		$box_id    = $request->box_id;
		$user_id   = $request->user_id;
		$status    = $request->status;
	
		$orders = Order::select(
				'orders.payment_form_id',
				'orders.payment_method_id',
				DB::raw('SUM(orders.total_paid)               as total_paid'),
				DB::raw('SUM(orders.total_discount)           as total_discount'),
				DB::raw('SUM(orders.total_cost_price_tax_inc) as total_cost_price_tax_inc'),
				DB::raw('SUM(orders.total_iva_inc)           as total_iva_inc'),
				DB::raw('SUM(orders.total_iva_exc)           as total_iva_exc'),
				DB::raw('COUNT(orders.id)                     as number_of_orders'),
				DB::raw("COUNT(CASE WHEN orders.state = '1' THEN 1 END) as suspended"),
				DB::raw("COUNT(CASE WHEN orders.state = '2' THEN 1 END) as registered"),
				DB::raw("COUNT(CASE WHEN orders.state = '3' THEN 1 END) as quoted"),
				DB::raw("COUNT(CASE WHEN orders.state = '5' THEN 1 END) as credit"),
				DB::raw('SUM(orders.total_paid) as payment_value'),
				'pf.name as payment_form',
				'pm.name as payment_method'
			)
			->join('payment_forms  as pf', 'pf.id', '=', 'orders.payment_form_id')
			->join('payment_methods as pm', 'pm.id', '=', 'orders.payment_method_id')
			->when($from, function ($query, $from) {
				return $query->where('orders.created_at', '>=', Carbon::parse($from)->toDateTimeString());
			})
			->when($to, function ($query, $to) {
				return $query->where('orders.created_at', '<=', Carbon::parse($to)->addSeconds(59)->toDateTimeString());
			})
			->when($box_id, function ($query, $box_id) {
				if ($box_id != 0) {
					return $query->where('orders.box_id', $box_id);
				}
			})
			->when($user_id, function ($query, $user_id) {
				if (!empty($user_id)) {
					return $query->where('orders.user_id', $user_id);
				}
			})
			->when($status, function ($query, $status) {
				return $query->where('orders.state', $status);
			})
			->groupBy(
				'orders.payment_form_id',
				'orders.payment_method_id',
				'pf.name',
				'pm.name'
			)
			->get();
	
		return [
			'orders' => $orders,
		];
	}
	
	
	
=======

        $paginated = $query->paginate($perPage);

        return response()->json([
            'orders' => $paginated
        ]);
    }

    public function reportGeneralSales(Request $request)
    {
        $from      = $request->from;
        $to        = $request->to;
        $box_id    = $request->box_id;
        $user_id   = $request->user_id;
        $status    = $request->status;
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

        $orders = Order::select(
                'orders.payment_form_id',
                'orders.payment_method_id',
                DB::raw('SUM(orders.total_paid)               as total_paid'),
                DB::raw('SUM(orders.total_discount)           as total_discount'),
                DB::raw('SUM(orders.total_cost_price_tax_inc) as total_cost_price_tax_inc'),
                DB::raw('SUM(orders.total_iva_inc)           as total_iva_inc'),
                DB::raw('SUM(orders.total_iva_exc)           as total_iva_exc'),
                DB::raw('COUNT(orders.id)                     as number_of_orders'),
                DB::raw("COUNT(CASE WHEN orders.state = '1' THEN 1 END) as suspended"),
                DB::raw("COUNT(CASE WHEN orders.state IN ('2','4') THEN 1 END) as registered"), // incluye 4
                DB::raw("COUNT(CASE WHEN orders.state = '3' THEN 1 END) as quoted"),
                DB::raw("COUNT(CASE WHEN orders.state = '5' THEN 1 END) as credit"),
                DB::raw('SUM(orders.total_paid) as payment_value'),
                'pf.name as payment_form',
                'pm.name as payment_method'
            )
            ->join('payment_forms  as pf', 'pf.id', '=', 'orders.payment_form_id')
            ->join('payment_methods as pm', 'pm.id', '=', 'orders.payment_method_id')
            ->when($from, function ($query, $from) {
                return $query->where('orders.created_at', '>=', Carbon::parse($from)->toDateTimeString());
            })
            ->when($to, function ($query, $to) {
                return $query->where('orders.created_at', '<=', Carbon::parse($to)->addSeconds(59)->toDateTimeString());
            })
            ->when($box_id, function ($query, $box_id) {
                if ($box_id != 0) {
                    return $query->where('orders.box_id', $box_id);
                }
            })
            ->when($user_id, function ($query, $user_id) {
                if (!empty($user_id)) {
                    return $query->where('orders.user_id', $user_id);
                }
            })
            ->when($status, function ($query, $status) {
                return $query->where('orders.state', $status);
            })
            ->groupBy(
                'orders.payment_form_id',
                'orders.payment_method_id',
                'pf.name',
                'pm.name'
            )
            ->get();

        return [
            'orders' => $orders,
        ];
    }

    public function reportProductSales(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $product = $request->product;
        $category = $request->category;
        $nro_results = $request->nro_results ?: self::NRO_RESULTS;
        $product_status = $request->product_status ?? 'all'; // all, active, inactive

        $detail_order = DetailOrder::withTrashed()
            ->select(
                'detail_orders.product', 
                'detail_orders.barcode',
                'categories.name as category',
                DB::raw('SUM(detail_orders.quantity) as quantity_of_products'),
                DB::raw('SUM(detail_orders.quantity * products.sale_price_tax_inc) as value')
            )
            ->join('products', 'products.id', '=', 'detail_orders.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where(function ($query) use ($product_status) {
                if ($product_status !== 'all') {
                    if ($product_status == 'active') {
                        $query->whereNull('detail_orders.deleted_at');
                    } elseif ($product_status == 'inactive') {
                        $query->whereNotNull('detail_orders.deleted_at');
                    }
                }
            })
            ->whereHas('order', function ($query) use ($from, $to) {
                if ($from) {
                    $from = Carbon::parse($from)->toDateTimeString();
                    $query->where('created_at', '>=', $from);
                }
                if ($to) {
                    $to = Carbon::parse($to)->addSeconds(59)->toDateTimeString();
                    $query->where('created_at', '<=', $to);
                }
            })
            ->where(function ($query) use ($product) {
                if ($product) {
                    $query->where('detail_orders.barcode', 'LIKE', "%$product%")
                          ->orWhere('detail_orders.product', 'LIKE', "%$product%");
                }
            })
            ->where(function ($query) use ($category) {
                if ($category) {
                    $query->where('products.category_id', $category);
                }
            })
            ->groupBy('detail_orders.barcode', 'detail_orders.product', 'categories.name')
            ->paginate($nro_results);

        return $detail_order;
    }

    public function reportTotalProducts(Request $request)
    {
        $total_products = Product::selectRaw('count(id) as number_of_products')
            ->selectRaw('SUM(quantity) as quantity_of_products')
            ->selectRaw('SUM(quantity * cost_price_tax_inc ) as cost_stock')
            ->selectRaw('SUM(quantity * sale_price_tax_inc ) as cost_sale');

<<<<<<< HEAD
	public function reportClosing(Request $request)
	{
		$from        = $request->from;
		$to          = $request->to;
		$box_id      = $request->box_id;
		$user_id     = $request->user_id;
		$status      = $request->status ?? $request->status;
		$nro_results = $request->nro_results ?: self::NRO_RESULTS;
	
		$orders = Order::select(
				DB::raw('SUM(total_paid) as total_paid'),
				DB::raw('SUM(total_discount) as total_discount'),
				DB::raw('SUM(total_cost_price_tax_inc) as total_cost_price_tax_inc'),
				DB::raw('SUM(total_iva_inc) as total_iva_inc'),
				DB::raw('SUM(total_iva_exc) as total_iva_exc'),
				DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m-%d') as date_paid"),
				'orders.payment_form_id',
				'orders.payment_method_id',
				DB::raw('SUM(orders.total_paid) as payment_value'),
				'pf.name as payment_form',
				'pm.name as payment_method'
			)
			->join('payment_forms as pf', 'pf.id', '=', 'orders.payment_form_id')
			->join('payment_methods as pm', 'pm.id', '=', 'orders.payment_method_id')
			->selectRaw("count(orders.id) as number_of_orders")
			->selectRaw("count(case when state = '2' then 1 end) as registered")
			->selectRaw("count(case when state = '3' then 1 end) as quoted")
			->selectRaw("count(case when state = '5' then 1 end) as credit")
			->selectRaw("SUM(case when state = '3' then total_iva_inc ELSE 0 END) as paid_quoted")
			->selectRaw("SUM(case when state = '5' then total_iva_inc ELSE 0 END) as paid_credit")
			->selectRaw("SUM(case when state in ('2','5') then total_iva_inc ELSE 0 END) as total_sale")
			->selectRaw("SUM(case when state in ('2','5') then total_iva_exc ELSE 0 END) as total_sale_iva_exc")
			->orderBy('date_paid', 'desc')
			->where(function ($query) use ($from, $to) {
				if ($from !== '' && $from !== 'undefined' && $from !== null) {
					$from = Carbon::parse($from)->toDateTimeString();
					$query->where('orders.created_at', '>=', $from);
				}
				if ($to !== '' && $to !== 'undefined' && $to !== null) {
					$to = Carbon::parse($to)->addSeconds(59)->toDateTimeString();
					$query->where('orders.created_at', '<=', $to);
				}
			})
			->where(function ($query) use ($box_id) {
				if ($box_id !== '' && $box_id !== 'undefined' && $box_id !== null) {
					$query->where('box_id', $box_id);
				}
			})
			->where(function ($query) use ($status) {
				$query->where('state', '<>', '1')
					  ->where('state', '<>', '0');
				if ($status !== '' && $status !== 0 && $status !== null) {
					$query->where('state', $status);
				}
			})
			->where(function ($query) use ($user_id) {
				if ($user_id !== '' && $user_id !== 0 && $user_id !== null) {
					$query->where('orders.user_id', $user_id);
				}
			})
			->groupBy(
				'date_paid',
				'orders.payment_form_id',
				'orders.payment_method_id',
				'pf.name',
				'pm.name'
			)
			->paginate($nro_results);
	
		$totals = (object) [];
		$totals->total_sale = $orders->sum('total_sale');
	
		return [
			'orders' => $orders,
			'totals' => $totals
		];
	}
	
=======
        if ($request->category_id != '' && $request->category_id  != null && $request->category_id  != 0) {
            $total_products = $total_products
                ->where('category_id', "$request->category_id");
        }

        if ($request->brand_id != '' && $request->brand_id  != null && $request->brand_id  != 0) {
            $total_products = $total_products
                ->where('brand_id', "$request->brand_id");
        }

        if ($request->quantity_sign) {
            $total_products = $total_products
                ->where('quantity', "$request->quantity_sign", "$request->quantity");
        }
        $total_products = $total_products->first();

        return $total_products;
    }

    public function reportClosing(Request $request)
    {
        $from        = $request->from;
        $to          = $request->to;
        $box_id      = $request->box_id;
        $user_id     = $request->user_id;
        $status      = $request->status ?? $request->status;
        $nro_results = $request->nro_results ?: self::NRO_RESULTS;

        $orders = Order::select(
                DB::raw('SUM(total_paid) as total_paid'),
                DB::raw('SUM(total_discount) as total_discount'),
                DB::raw('SUM(total_cost_price_tax_inc) as total_cost_price_tax_inc'),
                DB::raw('SUM(total_iva_inc) as total_iva_inc'),
                DB::raw('SUM(total_iva_exc) as total_iva_exc'),
                DB::raw("DATE_FORMAT(orders.created_at, '%Y-%m-%d') as date_paid"),
                'orders.payment_form_id',
                'orders.payment_method_id',
                DB::raw('SUM(orders.total_paid) as payment_value'),
                'pf.name as payment_form',
                'pm.name as payment_method'
            )
            ->join('payment_forms as pf', 'pf.id', '=', 'orders.payment_form_id')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 'orders.payment_method_id')
            ->selectRaw("COUNT(orders.id) as number_of_orders")
            ->selectRaw("COUNT(CASE WHEN orders.payment_form_id = 2 THEN 1 END) as credit_invoices")
            ->selectRaw("SUM(CASE WHEN orders.payment_form_id = 2 THEN orders.total_iva_inc ELSE 0 END) as credit_sales")
            ->selectRaw("SUM(CASE WHEN orders.state IN ('2','4','5') THEN total_iva_inc ELSE 0 END) as total_sale") // incluye 4
            ->selectRaw("SUM(CASE WHEN orders.state IN ('2','4','5') THEN total_iva_exc ELSE 0 END) as total_sale_iva_exc") // incluye 4
            ->orderBy('date_paid', 'desc')
            ->where(function ($query) use ($from, $to) {
                if ($from) {
                    $query->where('orders.created_at', '>=', Carbon::parse($from)->toDateTimeString());
                }
                if ($to) {
                    $query->where('orders.created_at', '<=', Carbon::parse($to)->addSeconds(59)->toDateTimeString());
                }
            })
            ->where(function ($query) use ($box_id) {
                if ($box_id) {
                    $query->where('orders.box_id', $box_id);
                }
            })
            ->where(function ($query) use ($status) {
                $query->whereNotIn('orders.state', [0,1]);
                if ($status !== '' && $status !== null) {
                    if ((int)$status === 2) {
                        $query->whereIn('orders.state', [2,4]);
                    } else {
                        $query->where('orders.state', $status);
                    }
                }
            })
            ->where(function ($query) use ($user_id) {
                if ($user_id) {
                    $query->where('orders.user_id', $user_id);
                }
            })
            ->groupBy(
                'date_paid',
                'orders.payment_form_id',
                'orders.payment_method_id',
                'pf.name',
                'pm.name'
            )
            ->paginate($nro_results);

        $totals = (object)[
            'total_sale'        => $orders->sum('total_sale'),
            'number_of_orders'  => $orders->sum('number_of_orders'),
            'credit_invoices'   => $orders->sum('credit_invoices'),
            'credit_sales'      => $orders->sum('credit_sales'),
        ];
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

        return [
            'orders' => $orders,
            'totals' => $totals
        ];
    }

    public function reportInvoicedProducts(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        $product = $request->product;
        $category = $request->category;
        $orderDeleteFilter = $request->order_delete ?? 'all';
        $nro_results = $request->nro_results ?: self::NRO_RESULTS;

<<<<<<< HEAD
public function exportSalesReport(Request $request)
{
	$from       = $request->query('from');
	$to         = $request->query('to');
	$status     = $request->query('status');
	$no_invoice = $request->query('no_invoice');

	return Excel::download(
		new SalesReportExport($from, $to, $status, $no_invoice),
		'ventas.xlsx'
	);
}
=======
        $detail_order = DetailOrder::withTrashed()
            ->select(
                'detail_orders.product',
                'detail_orders.barcode',
                'categories.name as category',
                DB::raw('SUM(detail_orders.quantity) as quantity_of_products'),
                DB::raw('SUM(detail_orders.quantity * products.sale_price_tax_inc) as value')
            )
            ->join('products', 'products.id', '=', 'detail_orders.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereHas('order', function ($query) use ($from, $to, $orderDeleteFilter) {
                // Sólo se toman órdenes facturadas (state 2 y 4)
                $query->whereIn('state', [2,4]);
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

                if ($from) {
                    $from = Carbon::parse($from)->toDateTimeString();
                    $query->where('payment_date', '>=', $from);
                }
                if ($to) {
                    $to = Carbon::parse($to)->addSeconds(59)->toDateTimeString();
                    $query->where('payment_date', '<=', $to);
                }
                if ($orderDeleteFilter === 'deleted') {
                    $query->whereNotNull('deleted_at');
                } elseif ($orderDeleteFilter === 'active') {
                    $query->whereNull('deleted_at');
                }
            })
            ->where(function ($query) use ($product) {
                if ($product) {
                    $query->where('detail_orders.barcode', 'LIKE', "%$product%")
                        ->orWhere('detail_orders.product', 'LIKE', "%$product%");
                }
            })
            ->where(function ($query) use ($category) {
                if ($category) {
                    $query->where('products.category_id', $category);
                }
            })
            ->groupBy('detail_orders.barcode', 'detail_orders.product', 'categories.name')
            ->paginate($nro_results);

        return $detail_order;
    }

    public function exportSalesReport(Request $request)
    {
        $from       = $request->query('from');
        $to         = $request->query('to');
        $status     = $request->query('status');
        $no_invoice = $request->query('no_invoice');

        return Excel::download(
            new SalesReportExport($from, $to, $status, $no_invoice),
            'ventas.xlsx'
        );
    }
}
