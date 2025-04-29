<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Zone;
use App\Models\ProductPortion;
use App\Models\PortionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:product.index')->only('index');
        $this->middleware('can:product.store')->only('store');
        $this->middleware('can:product.update')->only('update');
        $this->middleware('can:product.delete')->only('destroy');
        $this->middleware('can:product.active')->only('activate');
    }

    public function index(Request $request)
    {
        $no_results = $request->no_results ?? 10;
        $products = Product::select()
            ->selectRaw("DATE_ADD(NOW(), INTERVAL 3 MONTH) as alert_expiration_date");

        if ($request->product != '') {
            $products = $products->where('product', 'LIKE', "%$request->product%");
        }
        if ($request->barcode != '') {
            $products = $products->where('barcode', 'LIKE', "$request->barcode");
        }
        if ($request->category_id != '' && $request->category_id != null && $request->category_id != 0) {
            $products = $products->where('category_id', $request->category_id);
        }
        if ($request->quantity_sign) {
            $products = $products->where('quantity', $request->quantity_sign, $request->quantity);
        }
        if ($request->expiration_date_from != '' && $request->expiration_date_from != null) {
            $products = $products->where('expiration_date', '>=', $request->expiration_date_from);
        }
        if ($request->expiration_date_to != '' && $request->expiration_date_to != null) {
            $products = $products->where('expiration_date', '<=', $request->expiration_date_to);
        }
        if ($request->state != '' && $request->state != null && $request->state != 'all') {
            $products = $products->where('state', $request->state);
        }

        $products = $products->orderBy('product', 'asc')
                             ->with('zones', 'portions')
                             ->paginate($no_results);

        $total_products = (new ReportController())->reportTotalProducts($request);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'products' => $products,
            'total_products' => $total_products
        ]);
    }

    public function create()
    {
        abort(404);
    }

    /**
     * Almacena un nuevo producto y guarda las porciones asociadas.
     */
    public function store(Request $request)
    {
        $new_product = $request->product;
    
        $validate = Validator::make($new_product, [
            'category_id' => 'required|integer|exists:categories,id',
            'tax_id' => 'required|integer|exists:taxes,id',
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
            'expiration_date' => 'nullable|date',
            'measurement_unit_id' => 'nullable|integer|exists:measurement_units,id',
            'product_identification_standard_id' => 'nullable|integer|exists:product_identification_standards,id',
            'uses_portions' => 'boolean'
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
            $product->wholesale_price_tax_exc = $new_product['wholesale_price_tax_exc'] ?? 0;
            $product->wholesale_price_tax_inc = $new_product['wholesale_price_tax_inc'] ?? 0;
            $product->stock = $new_product['stock'];
            $product->quantity = $new_product['quantity'] ?? 0;
            $product->minimum = $new_product['minimum'] ?? 0;
            $product->maximum = $new_product['maximum'] ?? 0;
            $product->category_id = $new_product['category_id'];
            $product->tax_id = $new_product['tax_id'];
            $product->expiration_date = $new_product['expiration_date'];
            $product->measurement_unit_id = $new_product['measurement_unit_id'] ?? null;
            $product->product_identification_standard_id = $new_product['product_identification_standard_id'] ?? null;
            $product->uses_portions = $new_product['uses_portions'] ?? false;
            $product->save();
    
            // Sincronizar zonas
            $zones = Zone::find($new_product['zone_id']);
            $product->zones()->sync($zones);
    
            // Guardar las porciones asociadas si usa porciones y se envió portionList
            if ($new_product['uses_portions'] && isset($request->portionList) && count($request->portionList) > 0) {
                foreach ($request->portionList as $portionItem) {
                    // Se usa 'portion_id' para relacionar correctamente la porción
                    ProductPortion::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'portion_id' => $portionItem['portion_id']
                        ],
                        [
                            'quantity' => $portionItem['quantity']
                        ]
                    );
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
                'code' => 400,
                'message' => 'Validación de datos incorrecta',
                'errors' => $validate->errors()
            ];
        }
    
        return response()->json($data, $data['code']);
    }
    
    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('portions'); // Carga la relación de porciones junto con la porción
        return response()->json($product);
    }
    
    public function edit($id)
    {
        abort(404);
    }

    /**
     * Actualiza el producto y las porciones asociadas.
     */
    public function update(Request $request, $id)
    {
        $p = $request->product;
        $product = Product::find($id);
    
        $validate = Validator::make($p, [
            'category_id' => 'required|integer|exists:categories,id',
            'tax_id' => 'required|integer|exists:taxes,id',
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
            'maximum' => 'nullable|numeric',
            'expiration_date' => 'nullable|date',
            'measurement_unit_id' => 'required|integer|exists:measurement_units,id',
            'product_identification_standard_id' => 'nullable|integer|exists:product_identification_standards,id'
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
            $product->expiration_date = $p['expiration_date'];
            $product->measurement_unit_id = $p['measurement_unit_id'];
            $product->product_identification_standard_id = $p['product_identification_standard_id'] ?? null;
            $product->save();
    
            // Sincronizar zonas
            if (isset($p['zone_id'])) {
                $zones = Zone::find($p['zone_id']);
                $product->zones()->sync($zones);
            } else {
                $product->zones()->sync([]);
            }
    
            // Actualizar las porciones asociadas
            if ($p['uses_portions'] && isset($request->portionList)) {
                // Eliminamos las asociaciones actuales
                ProductPortion::where('product_id', $product->id)->delete();
    
                foreach ($request->portionList as $portionItem) {
                    if (isset($portionItem['portion_id'])) {
                        ProductPortion::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'portion_id' => $portionItem['portion_id']
                            ],
                            [
                                'quantity' => $portionItem['quantity']
                            ]
                        );
                    }
                }
            }
    
            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Actualización exitosa',
                'product' => $product
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Validación de datos incorrecta',
                'errors' => $validate->errors()
            ];
        }
    
        return response()->json($data, $data['code']);
    }
    
    public function destroy($id)
    {
        abort(404);
    }

    public function activate($id)
    {
        $product = Product::find($id);
        $product->state = !$product->state;
        $product->save();
    }

    public function searchProduct(Request $request)
    {
        $products = Product::select()
            ->where('barcode', 'LIKE', $request->product)
            ->where('state', 1)
            ->first();

        return ['products' => $products];
    }

    public function filterProductList(Request $request)
    {
        $products = Product::query();
        $products->where('state', 1);

        if ($request->product) {
            $products->where(function ($query) use ($request) {
                $query->where('barcode', 'LIKE', "%{$request->product}%")
                      ->orWhere('product', 'LIKE', "%{$request->product}%");
            });
        }
        if ($request->category_id) {
            $products->where('category_id', $request->category_id);
        }
        if ($request->is_order) {
            $products->where('quantity', '>', 0)
                     ->limit(5);
        }

        $products = $products->orderBy('product', 'asc')->get();

        return response()->json($products);
    }
    
    public function searchKitById($type, $product_parent_id, $quantity)
    {
        $products = KitProduct::where('product_parent_id', $product_parent_id)->get();
        foreach ($products as $product) {
            $this->updateStockByBarcode($type, $product['barcode'], $product['quantity'] * $quantity);
        }
    }

    public function updateStockByBarcode($type, $barcode, $quantity)
    {
        $product = Product::select('id', 'barcode', 'quantity')->where('barcode', $barcode)->first();
        if ($type == 1) {
            $product->quantity = $product->quantity - $quantity;
        }
        if ($type == 2) {
            $product->quantity = $product->quantity + $quantity;
        }
        $product->save();
    }

    public function updateStockById(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->quantity = $product->quantity + $request->quantity;
        $product->save();
    }

    public function updatePriceById($type, $purchased_product)
    {
        $product = Product::find($purchased_product->product_id);
        $percentage = $product->tax->percentage / 100;

        $product->cost_price_tax_exc  = ($purchased_product->cost_price_tax_inc) / (1 + $percentage);
        $product->cost_price_tax_inc  =  $purchased_product->cost_price_tax_inc;
        $product->sale_price_tax_exc = ($purchased_product->sale_price_tax_inc) / (1 + $percentage);
        $product->sale_price_tax_inc = $purchased_product->sale_price_tax_inc;
        $product->gain = $product->sale_price_tax_exc - $purchased_product->sale_price_tax_inc;
        if ($type == 2) {
            $product->quantity += $purchased_product->quantity;
        } else {
            $product->quantity -= $purchased_product->quantity;
        }
        $product->save();
    }

    public function getPortions($id)
    {
        // Cargamos la relación portions y, dentro de ella, la relación portion (belongsTo)
        $product = Product::with(['portions.portion'])->find($id);
    
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    
        return response()->json($product->portions, 200);
    }

    public function updatePortionStock($operation, $productId, $portionId, $quantity, $orderQuantity = 1)
    {
        $qtyBase = floatval($quantity);
        $totalQty = $qtyBase * floatval($orderQuantity);
        \Log::info("updatePortionStock iniciado: op={$operation}, productId={$productId}, portionId={$portionId}, qtyBase={$qtyBase}, orderQuantity={$orderQuantity}, totalQty={$totalQty}");

        // Actualizar registro en product_portions
        $productPortion = ProductPortion::where('product_id', $productId)
            ->where('portion_id', $portionId)
            ->first();

        if ($productPortion) {
            \Log::info("Registro ProductPortion encontrado: ID={$productPortion->id}, cantidad actual={$productPortion->quantity}");
            if ($operation == 1) {
                $productPortion->quantity = max(0, $productPortion->quantity - $totalQty);
                $movement = 'Facturado';
                $historyQty = -$totalQty;
            } elseif ($operation == 2) {
                $productPortion->quantity += $totalQty;
                $movement = 'in';
                $historyQty = $totalQty;
            }
            $productPortion->save();
            \Log::info("ProductPortion actualizado: nueva cantidad={$productPortion->quantity}");
        } else {
            \Log::warning("No se encontró ProductPortion para product_id={$productId} y portion_id={$portionId}");
        }
        
        // Actualizar stock global en portions
        $globalPortion = \App\Models\Portion::find($portionId);
        if ($globalPortion) {
            \Log::info("Porción global encontrada: ID={$globalPortion->id}, cantidad actual={$globalPortion->quantity}");
            if ($operation == 1) {
                $globalPortion->quantity = max(0, $globalPortion->quantity - $totalQty);
                $movement = 'Facturado';
                $historyQty = -$totalQty;
            } elseif ($operation == 2) {
                $globalPortion->quantity += $totalQty;
                $movement = 'in';
                $historyQty = $totalQty;
            }
            $globalPortion->save();
            \Log::info("Porción global actualizada: nueva cantidad={$globalPortion->quantity}");
            
            // Registrar historial en portion_histories
            try {
                $history = PortionHistory::create([
                    'portion_id' => $globalPortion->id,
                    'movement'   => $movement,
                    'quantity'   => $historyQty
                ]);
                \Log::info("Registro en historial creado: id={$history->id}, movimiento={$movement}, cantidad={$historyQty}");
            } catch (\Exception $e) {
                \Log::error("Error al crear registro en historial: " . $e->getMessage());
            }
        } else {
            \Log::warning("No se encontró Porción global con id={$portionId}");
        }
        
        \Log::info("updatePortionStock finalizado");
    }


    public function filter(Request $request)
    {
        // Inicia la consulta filtrando por estado activo (1)
        $products = Product::query()->where('state', 1);
    
        if ($request->has('search') && $request->search !== '') {
            $products->where(function ($query) use ($request) {
                $query->where('barcode', 'LIKE', "%{$request->search}%")
                      ->orWhere('product', 'LIKE', "%{$request->search}%");
            });
        }
    
        if ($request->has('category_id') && $request->category_id != 0) {
            $products->where('category_id', $request->category_id);
        }
    
        if ($request->has('is_order') && $request->is_order) {
            $products->where('quantity', '>', 0)
                     ->limit(5);
        }
    
        $products = $products->orderBy('product', 'asc')->get();
    
        return response()->json($products);
    }
}
