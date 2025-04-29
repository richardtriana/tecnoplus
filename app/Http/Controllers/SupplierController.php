<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:supplier.index')->only('index', 'show');
        $this->middleware('can:supplier.store')->only('store');
        $this->middleware('can:supplier.update')->only('update');
        $this->middleware('can:supplier.delete')->only('destroy');
        $this->middleware('can:supplier.active')->only('activate');
    }

    /**
     * Display a listing of the resource.
     * Permite filtrar por nombre, razón social o documento usando el parámetro "search".
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $suppliersQuery = Supplier::with([
            'municipality',
            'clientTribute',
            'identityDocumentType',
            'organizationType'
        ])
        ->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('razon_social', 'LIKE', "%{$search}%")
                  ->orWhere('document', 'LIKE', "%{$search}%");
            });
        });

        $suppliers = $suppliersQuery->paginate(15);

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $supplierData = $request->all();
        Supplier::create($supplierData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Proveedor creado exitosamente'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $supplier = Supplier::with([
            'municipality',
            'clientTribute',
            'identityDocumentType',
            'organizationType'
        ])->find($id);

        if ($supplier) {
            return response()->json([
                'status'   => 'success',
                'supplier' => $supplier
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Proveedor no encontrado'
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);

        if ($supplier) {
            $supplier->first_name                = $request->first_name;
            $supplier->second_name               = $request->second_name;
            $supplier->first_lastname            = $request->first_lastname;
            $supplier->second_lastname           = $request->second_lastname;
            $supplier->razon_social              = $request->razon_social;
            $supplier->address                   = $request->address;
            $supplier->phone                     = $request->phone;
            $supplier->email                     = $request->email;
            $supplier->document                  = $request->document;
            $supplier->div_verification          = $request->div_verification;
            $supplier->municipality_id           = $request->municipality_id;
            $supplier->client_tribute_id         = $request->client_tribute_id;
            $supplier->identity_document_type_id = $request->identity_document_type_id;
            $supplier->organization_type_id      = $request->organization_type_id;
            $supplier->active                    = $request->active;
            $supplier->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Proveedor actualizado exitosamente'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Proveedor no encontrado'
        ], 404);
    }

    /**
     * Activate/Deactivate the specified resource.
     */
    public function activate($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $supplier->active = !$supplier->active;
            $supplier->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Estado del proveedor actualizado'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Proveedor no encontrado'
        ], 404);
    }

    /**
     * Search for a supplier by document or name (only active suppliers).
     */
    public function searchSupplier(Request $request)
    {
        $supplier = Supplier::select()
            ->where('active', 1)
            ->where(function ($q) use ($request) {
                $q->where('document', 'LIKE', "%{$request->supplier}%")
                  ->orWhere('first_name', 'LIKE', "%{$request->supplier}%");
            })
            ->first();

        return response()->json($supplier);
    }

    /**
     * Filter the supplier list (only active suppliers), limited to 20 results.
     */
    public function filterSupplierList(Request $request)
    {
        if (!$request->supplier || $request->supplier == '') {
            $suppliers = Supplier::select()->where('active', 1)->get();
        } else {
            $suppliers = Supplier::select()
                ->where('active', 1)
                ->where(function ($q) use ($request) {
                    $q->where('document', 'LIKE', "%{$request->supplier}%")
                      ->orWhere('first_name', 'LIKE', "%{$request->supplier}%");
                })
                ->limit(20)
                ->get();
        }
        return response()->json($suppliers);
    }

    // Optionally, you can implement destroy() if needed.
}
