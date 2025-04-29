<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->input('paginate', true)) {
            $data = Service::all();
        } else {
            $data = Service::paginate(20);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'services' => $data,
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
        $validate = Validator::make($request->all(), [
            'codigo' => 'required|string|max:100',
            'name'   => 'required|string|min:3|max:100',
            'active' => 'boolean'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validate->errors()
            ], 400);
        }

        $service = Service::create([
            'codigo' => $request->input('codigo'),
            'name'   => $request->input('name'),
            'active' => $request->has('active') ? (bool)$request->input('active') : true,
        ]);

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Registro exitoso',
            'service' => $service
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $service = Service::find($id);

        if ($service) {
            $data = [
                'status'  => 'success',
                'code'    => 200,
                'service' => $service
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
        $service = Service::find($id);

        $validate = Validator::make($request->all(), [
            'codigo' => 'required|string|max:100',
            'name'   => 'required|string|min:3|max:100',
            'active' => 'boolean'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validate->errors()
            ], 400);
        }

        if ($service) {
            $service->codigo = $request->input('codigo');
            $service->name = $request->input('name');
            $service->active = $request->has('active') ? (bool)$request->input('active') : $service->active;
            $service->save();

            $data = [
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Actualización exitosa',
                'service' => $service
            ];
        } else {
            $data = [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Registro no encontrado',
            ];
        }
        return response()->json($data, $data['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if ($service) {
            $service->delete();
            $data = [
                'status'  => 'success',
                'code'    => 200,
                'service' => $service
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
     * Activate or deactivate the specified service.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Servicio no encontrado',
            ], 404);
        }

        $service->active = !$service->active;
        $service->save();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Estado actualizado correctamente',
            'service' => $service,
        ]);
    }

    /**
     * Devuelve la lista de servicios activos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function serviceList()
    {
        $services = Service::where('active', 1)->get();

        if ($services->count() > 0) {
            $data = [
                'status'   => 'success',
                'code'     => 200,
                'services' => $services
            ];
        } else {
            $data = [
                'status'  => 'error',
                'code'    => 400,
                'message' => 'No se encontraron servicios activos'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
