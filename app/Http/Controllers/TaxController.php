<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tax;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
	public function __construct()
	{
		$this->middleware('can:tax.index')->only('index', 'show');
		$this->middleware('can:tax.store')->only('store');
		$this->middleware('can:tax.update')->only('update');
		$this->middleware('can:tax.delete')->only('destroy');
		$this->middleware('can:tax.active')->only('active');
	}
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
			'taxes' => Tax::paginate(10)
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
			'percentage' => 'required|numeric'
		]);

		if ($validate->fails()) {
			return response()->json([
				'status' => 'error',
				'code' =>  400,
				'message' => 'Validación de datos incorrecta',
				'errors' =>  $validate->errors()
			], 400);
		}

		$tax = Tax::create([
			'percentage' => $request->input('percentage'),
			'name' => $request->input('name')

		]);

		return response()->json([
			'status' => 'success',
			'code' => 200,
			'message' => 'Registro exitoso',
			'tax' => $tax
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
		$tax = Tax::find($id);

		if ($tax) {
			$data = [
				'status' => 'success',
				'code' => 200,
				'tax' => $tax
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
			'percentage' => 'required|numeric'
		]);

		if ($validate->fails()) {
			return response()->json([
				'status' => 'error',
				'code' =>  400,
				'message' => 'Validación de datos incorrecta',
				'errors' =>  $validate->errors()
			], 400);
		}

		$tax = Tax::find($id);

		if ($tax) {
			$tax->percentage = $request->input('percentage');
			$tax->name = $request->input('name');

			$tax->save();
			$data = [
				'status' => 'success',
				'code' =>  200,
				'message' => 'Actualización exitosa',
				'tax' =>  $tax
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
		$tax = Tax::find($id);

		if ($tax) {
			$tax->delete();
			$data = [
				'status' => 'success',
				'code' => 200,
				'tax' => $tax
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
	 * Activate the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function activate($id)
	{
		//
		$tax = Tax::find($id);
		$tax->active = !$tax->active;
		$tax->save();
	}
}
