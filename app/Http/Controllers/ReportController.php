<?php

namespace App\Http\Controllers;

use App\Models\DetailOrder;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
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
					$query->whereDate('payment_date', '>=', $from);
				}
				if ($to != '' && $to != 'undefined' && $to != null) {
					$query->whereDate('payment_date', '<=', $to);
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
					$query->whereDate('created_at', '>=', $from);
				}
				if ($to != '' && $to != 'undefined' && $to != null) {
					$query->whereDate('created_at', '<=', $to);
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

		$detail_order = DetailOrder::select('product', 'barcode')
			->selectRaw('SUM(quantity) as quantity_of_products')
			->groupBy('barcode', 'product')
			->where(function ($query) use ($from, $to) {
				if ($from != '' && $from != 'undefined' && $from != null) {
					$query->whereDate('created_at', '>=', $from);
				}
				if ($to != '' && $to != 'undefined' && $to != null) {
					$query->whereDate('created_at', '<=', $to);
				}
			})
			->where(function ($query) use ($product) {
				if ($product != '' && $product != 'undefined' && $product != null) {
					$query->where('barcode', 'LIKE', "%$product%")
						->orWhere('product', 'LIKE', "%$product%");
				}
			})
			->whereHas('product', function($query) use ($category, $request){
				if($request->filled('category') && $category != 'undefined'){
					$query->where('category_id', $category);
				}
			})
			->get();

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
}
