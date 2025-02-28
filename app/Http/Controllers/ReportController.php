<?php

namespace App\Http\Controllers;

use App\Models\DetailOrder;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
	Const NRO_RESULTS = 15;

	public function __construct()
	{
		$this->middleware('can:report.index');
	}

	public function reportSales(Request $request)
	{
		$from = $request->from;
		$to = $request->to;
		$box_id = $request->box_id;
		$status = $request->status ?? $request->status;

		$orders = Order::select(
			DB::raw('SUM(total_paid) as total_paid'),
			DB::raw('SUM(total_discount) as total_discount'),
			DB::raw('SUM(total_cost_price_tax_inc) as total_cost_price_tax_inc'),
			DB::raw('SUM(total_iva_inc) as total_iva_inc'),
			DB::raw('SUM(total_iva_exc) as total_iva_exc'),
			DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d')) as date_paid")
		)
			->selectRaw('count(id) as number_of_orders')
			->selectRaw("count(case when state = '1' then 1 end) as suspended")
			->selectRaw("count(case when state = '2' then 1 end) as registered")
			->selectRaw("count(case when state = '3' then 1 end) as quoted")
			->selectRaw("count(case when state = '5' then 1 end) as credit")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.nequi')) as nequi")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.card')) as card")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.cash')) as cash")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.others')) as others")
			->orderBy('date_paid', 'desc')
			->where(function ($query) use ($from, $to) {
				if ($from != '' && $from != 'undefined' && $from != null) {
					$from = Carbon::parse($from)->toDateTimeString();
					$query->where('payment_date', '>=', $from);
				}
				if ($to != '' && $to != 'undefined' && $to != null) {
					$to = Carbon::parse($to)->addSeconds(59)->toDateTimeString();
					$query->where('payment_date', '<=', $to);
				}
			})
			->where(function ($query) use ($box_id) {
				if ($box_id != '' && $box_id != 'undefined' && $box_id != null) {
					$query->where('box_id', $box_id);
				}
			})
			->where(function ($query) use ($status) {
				if ($status != '' && $status != 0 && $status != null) {
					$query->where('state', $status);
				}
			})
			->groupBy('date_paid')
			->get();

		return $orders;
	}

	public function reportGeneralSales(Request $request)
	{
		$from = $request->from;
		$to = $request->to;
		$box_id = $request->box_id;
		$user_id = $request->user_id;
		$status = $request->status ?? $request->status;

		$orders = Order::selectRaw('SUM(total_paid) as total_paid')
			->selectRaw('SUM(total_discount) as total_discount')
			->selectRaw('SUM(total_cost_price_tax_inc) as total_cost_price_tax_inc')
			->selectRaw('SUM(total_iva_inc) as total_iva_inc')
			->selectRaw('SUM(total_iva_exc) as total_iva_exc')
			->selectRaw('count(id) as number_of_orders')
			->selectRaw("count(case when state = '1' then 1 end) as suspended")
			->selectRaw("count(case when state = '2' then 1 end) as registered")
			->selectRaw("count(case when state = '3' then 1 end) as quoted")
			->selectRaw("count(case when state = '5' then 1 end) as credit")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.nequi')) as nequi")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.card')) as card")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.cash')) as cash")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.others')) as others")
			->where(function ($query) use ($from, $to) {
				if ($from != '' && $from != 'undefined' && $from != null) {
					$from = Carbon::parse($from)->toDateString();
					$query->where('created_at', '>=', $from);
				}
				if ($to != '' && $to != 'undefined' && $to != null) {
					$to = Carbon::parse($to)->addSeconds(59)->toDateString();
					$query->where('created_at', '<=', $to);
				}
			})
			->where(function ($query) use ($box_id) {
				if ($box_id != '' && $box_id != 0 && $box_id != null) {
					$query->where('box_id', $box_id);
				}
			})
			->where(function ($query) use ($user_id) {
				if ($user_id != '' && $user_id != 0 && $user_id != null) {
					$query->where('user_id', $user_id);
				}
			})
			->where(function ($query) use ($status) {
				if ($status != '' && $status != 0 && $status != null) {
					$query->where('state', $status);
				}
			})
			->get();
		return $orders;
	}

	public function reportProductSales(Request $request)
	{
		$from = $request->from;
		$to = $request->to;
		$product = $request->product;
		$category = $request->category;
		$nro_results = $request->nro_results ?: self::NRO_RESULTS;
		$product_status = $request->product_status ?? 'all'; // all, active, inactive
	
		$detail_order = DetailOrder::withTrashed() // Incluye los registros eliminados (inactivos)
			->select(
				'detail_orders.product', 
				'detail_orders.barcode',
				'categories.name as category', // Nombre de la categoría
				DB::raw('SUM(detail_orders.quantity) as quantity_of_products'),
				DB::raw('SUM(detail_orders.quantity * products.sale_price_tax_inc) as value') // Valor total
			)
			->join('products', 'products.id', '=', 'detail_orders.product_id') // Relación con productos
			->join('categories', 'categories.id', '=', 'products.category_id') // Relación con categorías
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
		$from = $request->from;
		$to = $request->to;
		$box_id = $request->box_id;
		$user_id = $request->user_id;
		$status = $request->status ?? $request->status;
		$nro_results = $request->nro_results ?: self::NRO_RESULTS;

		$orders = Order::select(
			// DB::raw('count(pay) as pay'),
			DB::raw('SUM(total_paid) as total_paid'),
			DB::raw('SUM(total_discount) as total_discount'),
			DB::raw('SUM(total_cost_price_tax_inc) as total_cost_price_tax_inc'),
			DB::raw('SUM(total_iva_inc) as total_iva_inc'),
			DB::raw('SUM(total_iva_exc) as total_iva_exc'),
			DB::raw("(DATE_FORMAT(orders.created_at,'%Y-%m-%d')) as date_paid")
		)

			->selectRaw("count(orders.id) as number_of_orders")
			->selectRaw("count(case when state = '2' then 1 end) as registered")
			->selectRaw("count(case when state = '3' then 1 end) as quoted")
			->selectRaw("count(case when state = '5' then 1 end) as credit")
			->selectRaw("SUM(case when state = '3' then total_iva_inc ELSE 0 END) as paid_quoted")
			->selectRaw("SUM(case when state = '5' then total_iva_inc ELSE 0 END) as paid_credit")
			->selectRaw("SUM(case when state = '2' or state ='5' then total_iva_inc ELSE 0 END) as total_sale")
			->selectRaw("SUM(case when state = '2' or state ='5' then total_iva_exc ELSE 0 END) as total_sale_iva_exc")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.nequi')) as nequi")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.card')) as card")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.cash') + JSON_EXTRACT(`payment_methods`,'$.pay_payment')) as cash")
			->selectRaw("SUM(JSON_EXTRACT(`payment_methods`,'$.others')) as others")
			->orderBy('date_paid', 'desc')
			->where(function ($query) use ($from, $to) {
				if ($from != '' && $from != 'undefined' && $from != null) {
					$from = Carbon::parse($from)->toDateTimeString();
					$query->where('orders.created_at', '>=', $from);
				}
				if ($to != '' && $to != 'undefined' && $to != null) {
					$to = Carbon::parse($to)->addSeconds(59)->toDateTimeString();
					$query->where('orders.created_at', '<=', $to);
				}
			})
			->where(function ($query) use ($box_id) {
				if ($box_id != '' && $box_id != 'undefined' && $box_id != null) {
					$query->where('box_id', $box_id);
				}
			})
			->where(function ($query) use ($status) {
				$query->where('state', '<>', '1');
				$query->where('state', '<>', '0');
				if ($status != '' && $status != 0 && $status != null) {
					$query->where('state', $status);
				}
			})

			->where(function ($query) use ($user_id) {
				if ($user_id != '' && $user_id != 0 && $user_id != null) {
					$query->where('orders.user_id', $user_id);
				}
			})
			->groupBy('date_paid')
			->paginate($nro_results);

		$totals = (object) [];

		$totals->total_sale = $orders->sum('total_sale');

		return ['orders'=>$orders, 'totals'=>$totals];
	}
// En app/Http/Controllers/ReportController.php

public function reportInvoicedProducts(Request $request)
{
    $from = $request->from;
    $to = $request->to;
    $product = $request->product;
    $category = $request->category;
    // Parámetro para filtrar el estado de la orden: 'active' (no eliminadas), 'deleted' o 'all'
    $orderDeleteFilter = $request->order_delete ?? 'all';
    $nro_results = $request->nro_results ?: self::NRO_RESULTS;

    $detail_order = DetailOrder::withTrashed() // incluye registros eliminados en detalle de órdenes
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
            // Sólo se toman órdenes facturadas (por ejemplo, state = 2)
            $query->where('state', 2);

            if ($from) {
                $from = \Carbon\Carbon::parse($from)->toDateTimeString();
                $query->where('payment_date', '>=', $from);
            }
            if ($to) {
                $to = \Carbon\Carbon::parse($to)->addSeconds(59)->toDateTimeString();
                $query->where('payment_date', '<=', $to);
            }
            // Filtro para órdenes eliminadas o activas según corresponda
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


}
