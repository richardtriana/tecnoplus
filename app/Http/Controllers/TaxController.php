<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class TaxController extends Controller
{
    public function __construct()
    {
        /*
        $this->middleware('can:tax.index')->only('index', 'show');
        $this->middleware('can:tax.store')->only('store');
        $this->middleware('can:tax.update')->only('update');
        $this->middleware('can:tax.delete')->only('destroy');
        $this->middleware('can:tax.active')->only('activate');*/
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Permite filtrar con el parámetro "search" si es enviado
        $search = $request->input('search');

        $query = Tax::query();
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $taxes = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'taxes' => $taxes
        ]);
    }
    
    public function create()
    {
        abort(404);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'code'        => 'required|string',
            'percentage'  => 'required|numeric',
            'name'        => 'required|string',
            'description' => 'nullable|string'
        ]);
    
        if ($validate->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validate->errors()
            ], 400);
        }
    
        $tax = Tax::create([
            'code'        => $request->input('code'),
            'percentage'  => $request->input('percentage'),
            'name'        => $request->input('name'),
            'description' => $request->input('description')
        ]);
    
        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Registro exitoso',
            'tax'     => $tax
        ], 200);
    }
    
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tax = Tax::find($id);
    
        if ($tax) {
            $data = [
                'status' => 'success',
                'code'   => 200,
                'tax'    => $tax
            ];
        } else {
            $data = [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Registro no encontrado'
            ];
        }
    
        return response()->json($data, $data['code']);
    }
    
    public function edit($id)
    {
        abort(404);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'code'        => 'required|string',
            'percentage'  => 'required|numeric',
            'name'        => 'required|string',
            'description' => 'nullable|string'
        ]);
    
        if ($validate->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validate->errors()
            ], 400);
        }
    
        $tax = Tax::find($id);
    
        if ($tax) {
            $tax->code        = $request->input('code');
            $tax->percentage  = $request->input('percentage');
            $tax->name        = $request->input('name');
            $tax->description = $request->input('description');
            $tax->save();
    
            $data = [
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Actualización exitosa',
                'tax'     => $tax
            ];
        } else {
            $data = [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Registro no encontrado'
            ];
        }
        return response()->json($data, $data['code']);
    }
    
    /**
     * Elimina el impuesto especificado.
     */
    public function destroy($id)
    {
        $tax = Tax::find($id);
    
        if ($tax) {
            $tax->delete();
            $data = [
                'status' => 'success',
                'code'   => 200,
                'tax'    => $tax
            ];
        } else {
            $data = [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Registro no encontrado'
            ];
        }
    
        return response()->json($data, $data['code']);
    }
    
    /**
     * Alterna el estado activo/inactivo del impuesto.
     */
    public function activate($id)
    {
        $tax = Tax::find($id);
        if ($tax) {
            $tax->active = !$tax->active;
            $tax->save();
            return response()->json([
                'status'  => 'success',
                'message' => 'Estado del impuesto actualizado'
            ]);
        }
        return response()->json([
            'status'  => 'error',
            'message' => 'Registro no encontrado'
        ], 404);
    }
    
    /**
     * Consulta el endpoint de tributos de productos de Factus y actualiza la tabla de impuestos.
     *
     * Método: GET
     * Endpoint (Pruebas): https://api-sandbox.factus.com.co/v1/tributes/products?name=
     *
     * Es necesario enviar el token de acceso en el header "Authorization" con el formato:
     * "Bearer {token_de_acceso}"
     *
     * Ejemplo de respuesta:
     * {
     *   "status": "OK",
     *   "message": "Solicitud exitosa",
     *   "data": [
     *      { "id": 1, "code": "01", "name": "IVA", "description": "Impuesto sobre la Ventas" },
     *      ...
     *   ]
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchFromFactus(Request $request)
    {
        $accessToken = $request->header('Authorization');
        if (!$accessToken) {
            return response()->json(['error' => 'No se proporcionó el access token'], 401);
        }

        $queryParams = $request->query();
        $url = 'https://api-sandbox.factus.com.co/v1/tributes/products';

        $response = Http::withHeaders([
            'Authorization' => $accessToken,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json'
        ])->get($url, $queryParams);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['data'])) {
                foreach ($data['data'] as $tributeData) {
                    Tax::updateOrCreate(
                        ['code' => $tributeData['code']], // criterio de búsqueda: code
                        [
                            'code'        => $tributeData['code'],
                            'name'        => $tributeData['code'] . ' - ' . $tributeData['name'],
                            'percentage'  => $tributeData['name'] === 'IVA' ? 19.00 : 0,
                            'active'      => 1,
                            'description' => $tributeData['description'] ?? null
                        ]
                    );
                }
                return response()->json([
                    'status'  => 'success',
                    'code'    => 200,
                    'message' => 'Datos actualizados correctamente'
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Estructura de respuesta inválida'
                ], 400);
            }
        } else {
            return response()->json([
                'error'   => 'Error en la petición a la API',
                'details' => $response->body()
            ], $response->status());
        }
    }
}
