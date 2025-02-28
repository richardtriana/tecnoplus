<?php

namespace App\Http\Controllers;

use App\Models\KitProduct;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Zone;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use PhpParser\Node\Expr\Cast\Array_;

class ProductController extends Controller
{
	public function __construct()
	{
		$this->middleware('can:product.index')->only('index');
		$this->middleware('can:product.store')->only('store');
		$this->middleware('can:product.update')->only('update');
		$this->middleware('can:product.delete')->only('destroy');
		$this->middleware('can:product.active')->only('active');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$no_results = $request->no_results ?? 10;
		$products = Product::select()
			// ->selectRaw("DATE_ADD(`expiration_date`, INTERVAL 3 MONTH) as alert_expiration_date")
			->selectRaw("DATE_ADD(NOW(), INTERVAL 3 MONTH) as alert_expiration_date");

		if ($request->product != '') {
			$products = $products
				->where('product', 'LIKE', "%$request->product%");
		}
		if ($request->barcode != '') {
			$products = $products
				->where('barcode', 'LIKE', "$request->barcode");
		}
		if ($request->category_id != '' && $request->category_id  != null && $request->category_id  != 0) {
			$products = $products
				->where('category_id', "$request->category_id");
		}
		if ($request->brand_id != '' && $request->brand_id  != null && $request->brand_id  != 0) {
			$products = $products
				->where('brand_id', "$request->brand_id");
		}
		
		if ($request->quantity_sign) {
			$products = $products
				->where('quantity', "$request->quantity_sign", "$request->quantity");
		}

		if ($request->expiration_date_from != '' && $request->expiration_date_from  != null && $request->expiration_date_from  != 0) {
			$products = $products
				->where('expiration_date', '>=', "$request->expiration_date_from");
		}

		if ($request->expiration_date_to != '' && $request->expiration_date_to  != null && $request->expiration_date_to  != 0) {
			$products = $products
				->where('expiration_date', '<=', "$request->expiration_date_to");
		}

		if ($request->state != '' && $request->state  != null && $request->state  != 'all') {
			$products = $products
				->where('state', "$request->state");
		}

	$products = $products->orderBy('product', 'asc')->with('zones')->paginate($no_results);

		$total_products = new ReportController();
		$total_products = $total_products->reportTotalProducts($request);

		return response()->json([
			'status' => 'success',
			'code' => 200,
			'products' => $products,
			'total_products' => $total_products
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		abort(404);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{

		$new_product = $request->product;

		$validate = Validator::make($new_product, [
			'category_id' => 'required|integer|exists:categories,id',
			'tax_id' => 'required|integer|exists:taxes,id',
			'brand_id' => 'nullable|integer|exists:brands,id',
			'zone_id' => 'nullable|array|exists:zones,id',
			'product' => 'required|string|min:3|max:100',
			'barcode' => 'required|string|unique:products',
			'type' => 'required|integer',
			'cost_price_tax_exc' => 'required|numeric',
			'cost_price_tax_inc' => 'required|numeric',
			'gain' => 'required|numeric',
			'sale_price_tax_exc' => 'required|numeric',
			'sale_price_tax_inc' => 'required|numeric',
			'wholesale_price_tax_exc' => 'nullable|numeric',
			'wholesale_price_tax_inc' => 'nullable|numeric',
			'stock' => 'boolean',
			'quantity' => 'nullable|numeric',
			'minimum' => 'nullable|numeric',
			'maximum' => 'nullable|numeric',
			'expiration_date' => 'nullable|date'
		]);

		if (!$validate->fails()) {
			$product = new Product();
			$product->barcode = $new_product['barcode'];
			$product->product = $new_product['product'];
			$product->type = $new_product['type'];
			$product->cost_price_tax_exc = $new_product['cost_price_tax_exc'];
			$product->cost_price_tax_inc = $new_product['cost_price_tax_inc'];
			$product->gain = $new_product['gain'];
			$product->sale_price_tax_exc = $new_product['sale_price_tax_exc'];
			$product->sale_price_tax_inc = $new_product['sale_price_tax_inc'];
			$product->wholesale_price_tax_exc = $new_product['wholesale_price_tax_exc'];
			$product->wholesale_price_tax_inc = $new_product['wholesale_price_tax_inc'];
			$product->stock = $new_product['stock'];
			$product->quantity = $new_product['quantity'] ?? 0;
			$product->minimum = $new_product['minimum'] ?? 0;
			$product->maximum = $new_product['maximum'] ?? 0;
			$product->category_id = $new_product['category_id'];
			$product->tax_id = $new_product['tax_id'];
			$product->brand_id = $new_product['brand_id'];
			// $product->zone_id = $new_product['zone_id'];			
			$product->expiration_date = $new_product['expiration_date'];
			$product->save();

			$zones = Zone::find($new_product['zone_id']);
			$product->zones()->sync($zones);

			if ($new_product['type'] == 3) {
				if (count($request->itemListKit) > 0) {
					foreach ($request->itemListKit as $item) {
						$kitProduct = new KitProduct();
						$kitProduct->product_parent_id = $product->id;
						$kitProduct->product_child_id = $item['product_id'];
						$kitProduct->quantity = $item['quantity'];
						$kitProduct->product = $item['product'];
						$kitProduct->barcode = $item['barcode'];
						$kitProduct->save();
					}
				}
			}

			$data = [
				'status' => 'success',
				'code' => 200,
				'message' => 'Registro exitoso',
				'product' => $product
			];
		} else {
			$data = [
				'status' => 'error',
				'code' =>  400,
				'message' => 'Validación de datos incorrecta',
				'errors' =>  $validate->errors()
			];
		}

		return response()->json($data, $data['code']);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Product $product)
	{
		return $product;
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		abort(404);
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
		$p = $request->product;
		$product = Product::find($id);

		$validate = Validator::make($p, [
			'category_id' => 'required|integer|exists:categories,id',
			'tax_id' => 'required|integer|exists:taxes,id',
			'brand_id' => 'nullable|integer|exists:brands,id',
			'zone_id' => 'nullable|array|exists:zones,id',
			'product' => 'required|string|min:3|max:100',
			'barcode' => ['required', 'string', Rule::unique('products')->ignore($product->barcode, 'barcode')],
			'type' => 'required|integer',
			'cost_price_tax_exc' => 'required|numeric',
			'cost_price_tax_inc' => 'required|numeric',
			'gain' => 'required|numeric',
			'sale_price_tax_exc' => 'required|numeric',
			'sale_price_tax_inc' => 'required|numeric',
			'wholesale_price_tax_exc' => 'required|numeric',
			'wholesale_price_tax_inc' => 'required|numeric',
			'stock' => 'required|boolean',
			'quantity' => 'nullable|numeric',
			'minimum' => 'nullable|numeric',
			'maximum' => 'nullable|numeric'
		]);

		if (!$validate->fails()) {

			$product->barcode = $p['barcode'];
			$product->product = $p['product'];
			$product->type = $p['type'];
			$product->cost_price_tax_exc = $p['cost_price_tax_exc'];
			$product->cost_price_tax_inc = $p['cost_price_tax_inc'];
			$product->gain = $p['gain'];
			$product->sale_price_tax_exc = $p['sale_price_tax_exc'];
			$product->sale_price_tax_inc = $p['sale_price_tax_inc'];
			$product->wholesale_price_tax_exc = $p['wholesale_price_tax_exc'];
			$product->wholesale_price_tax_inc = $p['wholesale_price_tax_inc'];
			$product->stock = $p['stock'];
			$product->quantity = $p['quantity'];
			$product->minimum = $p['minimum'];
			$product->maximum = $p['maximum'];
			$product->category_id = $p['category_id'];
			$product->tax_id = $p['tax_id'];
			$product->brand_id = $p['brand_id'];
			// $product->zone_id = $p['zone_id'];
			$product->expiration_date = $p['expiration_date'];
			$product->save();

			// Asociar el producto a múltiples zonas
			$zones = Zone::find($p['zone_id']);
			$product->zones()->sync($zones);

			if ($p['type'] == 3) {
				if (count($request->itemListKit) > 0) {
					foreach ($request->itemListKit as $item) {

						KitProduct::updateOrCreate(
							['product_parent_id' => $id, 'product_child_id' => $item['product_id']],
							[
								'quantity' => $item['quantity'],
								'product' => $item['product'],
								'barcode' => $item['barcode']
							]
						);
					}
				}
			}

			$data = [
				'status' => 'success',
				'code' => 200,
				'message' => 'Actualización exitoso',
				'product' => $product
			];
		} else {
			$data = [
				'status' => 'error',
				'code' =>  400,
				'message' => 'Validación de datos incorrecta',
				'errors' =>  $validate->errors()
			];
		}

		return response()->json($data, $data['code']);
	}

	/*
	* @param integer $type
	* 1 : resta a stock
	* 2: suma a stock
	* @param string $product_parent_id
	* @param mixed $quantity
	*/

	public function searchKitById($type, $product_parent_id, $quantity)
	{
		$products = KitProduct::where('product_parent_id', $product_parent_id)->get();
		foreach ($products as $product) {
			$this->updateStockByBarcode($type, $product['barcode'], $product['quantity'] * $quantity);
		}
	}

	/*
	* @param integer $type
	* 1 : resta a stock
	* 2: suma a stock
	* @param mixed $barcode

	* @param mixed $quantity
	*/
	public function updateStockByBarcode($type, $barcode, $quantity)
	{
		$product = Product::select('id', 'barcode', 'quantity')->where('barcode', $barcode)->first();
		// if ($product->stock) {
			if ($type == 1) {
				$product->quantity = $product->quantity - $quantity;
			}
			if ($type == 2) {
				$product->quantity = $product->quantity + $quantity;
			}
			$product->save();
		// }
	}

	public function updateStockById(Request $request, $id)
	{
		$product = Product::findOrFail($id);
		// if ($product->stock) {
			$product->quantity = $product->quantity + $request->quantity;
			$product->save();
		// }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		abort(404);
	}

	/**
	 * Activate the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function activate($id)
	{
		//
		$product = Product::find($id);
		$product->state = !$product->state;
		$product->save();
	}

	public function searchProduct(Request $request)
	{
		$products = Product::select()
			->where('barcode', 'LIKE', "$request->product")
			//->orWhere('product', 'LIKE', "%$request->product%")
			->where('state', 1)
			->first();

		return ['products' => $products];
	}

	public function filterProductList(Request $request)
{
    // Crear la consulta base
    $products = Product::query();

    // Filtrar por estado activo
    $products->where('state', 1);

    // Filtro por código de barras o nombre del producto
    if ($request->product) {
        $products->where(function ($query) use ($request) {
            $query->where('barcode', 'LIKE', "%{$request->product}%")
                  ->orWhere('product', 'LIKE', "%{$request->product}%");
        });
    }

    // Filtro por categoría
    if ($request->category_id) {
        $products->where('category_id', $request->category_id);
    }

    // Filtro adicional para pedidos (is_order)
    if ($request->is_order) {
        $products->where('quantity', '>', 0)
                 ->limit(5);
    }

    // Obtener los productos
    $products = $products->get();

    // Retornar los productos como respuesta
    return $products;
}

	/*
	* @param integer $type
	* 1 : resta a stock
	* 2: suma a stock
	* @param mixed $barcode
	*/

	public function updatePriceById($type, $purchased_product)
	{
		$product = Product::find($purchased_product->product_id);
		$percentage = $product->tax->percentage / 100;

		$product->cost_price_tax_exc  = ($purchased_product->cost_price_tax_inc) /	(1 + $percentage);
		$product->cost_price_tax_inc  =  $purchased_product->cost_price_tax_inc;
		$product->sale_price_tax_exc = ($purchased_product->sale_price_tax_inc) /	(1 + $percentage);
		$product->sale_price_tax_inc = $purchased_product->sale_price_tax_inc;
		$product->gain = 	$product->sale_price_tax_exc - $purchased_product->sale_price_tax_inc;
		if ($type == 2) {
			$product->quantity += $purchased_product->quantity;
		} else {
			$product->quantity -= $purchased_product->quantity;
		}
		$product->save();
	}
}
