<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tables = Table::select();
        if ($request->table != '') {
			$tables = $tables
				->where('table', 'LIKE', "%$request->table%");
		}

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'tables' => $tables->paginate(10)
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
            'table' => 'required|string',
            'observations' => 'nullable|string',
            'state' => 'nullable|string',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Validación de datos incorrecta',
                'errors' =>  $validate->errors()
            ], 400);
        }

        $table = Table::create([
            'table' => $request->input('table'),
            'observations' => $request->input('observations'),
            'state' => $request->input('state')
        ]);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Registro exitoso',
            'table' => $table
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function show(Table $table)
    {
		if ($table) {
			$data = [
				'status' => 'success',
				'code' => 200,
				'table' => $table
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
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function edit(Table $table)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Table $table)
    {
        $validate = Validator::make($request->all(), [
            'table' => 'required|string',
            'observations' => 'nullable|string',
            'state' => 'nullable|string'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Validación de datos incorrecta',
                'errors' =>  $validate->errors()
            ], 400);
        }

		if ($table) {
			$table->table = $request->input('table');
			$table->observations = $request->input('observations');
			$table->state = $request->input('state');
			$table->save();

			$data = [
				'status' => 'success',
				'code' =>  200,
				'message' => 'Actualización exitosa',
				'table' =>  $table
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
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function destroy(Table $table)
    {
        if ($table) {
			$table->delete();
			$data = [
				'status' => 'success',
				'code' => 200,
				'table' => $table
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

    public function tableList(Request $request)
    {
        $tables = Table::where('state', 'free')->get();

        if ($tables) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'tables' => $tables
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
