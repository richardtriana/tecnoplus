<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'zones' => Zone::paginate(10)
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
            'zone' => 'required|string',
            'printer' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Validación de datos incorrecta',
                'errors' =>  $validate->errors()
            ], 400);
        }

        $zone = Zone::create([
            'zone' => $request->input('zone'),
            'printer' => $request->input('printer')
        ]);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Registro exitoso',
            'zone' => $zone
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
        $zone = Zone::find($id);

		if ($zone) {
			$data = [
				'status' => 'success',
				'code' => 200,
				'zone' => $zone
			];
		} else {
			$data = [
				'status' => 'error',
				'code' => 400,
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
        $validate = Validator::make($request->all(), [
            'zone' => 'required|string',
            'printer' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Validación de datos incorrecta',
                'errors' =>  $validate->errors()
            ], 400);
        }

        $zone = Zone::find($id);

		if ($zone) {
			$zone->zone = $request->input('zone');
			$zone->printer = $request->input('printer');
			$zone->save();

			$data = [
				'status' => 'success',
				'code' =>  200,
				'message' => 'Actualización exitosa',
				'zone' =>  $zone
			];
		} else {
			$data = [
				'status' => 'error',
				'code' =>  400,
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
        $zone = Zone::find($id);

		if ($zone) {
			$zone->delete();
			$data = [
				'status' => 'success',
				'code' => 200,
				'zone' => $zone
			];
		} else {
			$data = [
				'status' => 'error',
				'code' => 400,
				'message' => 'Registro no encontrado'
			];
		}

		return response()->json($data, $data['code']);    
    }

    public function zoneList()
    {
        $zones = Zone::get();

        if ($zones) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'zones' => $zones
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Registro no encontrado'
            ];
        }

        return response()->json($data, $data['code']);
    }
}
