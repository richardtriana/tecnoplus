<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

	public function __construct()
	{
		$this->middleware('can:supplier.index')->only('index');
		$this->middleware('can:supplier.store')->only('store');
		$this->middleware('can:supplier.update')->only('update');
		$this->middleware('can:supplier.delete')->only('destroy');
		$this->middleware('can:supplier.active')->only('active');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$suppliers = new Supplier;
		$suppliers = $suppliers
			->paginate(10);

		return response()->json([
			'status' => 'success',
			'code' => 200,
			'suppliers' => $suppliers
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
		$supplier = $request->all();
		Supplier::create($supplier);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		//
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
		$supplier = Supplier::find($id);
		$supplier->name = $request->name;
		$supplier->address = $request->address;
		$supplier->mobile = $request->mobile;
		$supplier->contact = $request->contact;
		$supplier->email = $request->email;
		$supplier->type_person = $request->type_person;
		$supplier->municipality_id = $request->municipality_id;
		$supplier->type_document = $request->type_document;
		$supplier->document = $request->document;
		$supplier->tax = $request->tax;
		$supplier->update();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//
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
		$supplier = Supplier::find($id);
		$supplier->active = !$supplier->active;
		$supplier->save();
	}

	public function searchSupplier(Request $request)
	{

		$supplier = Supplier::select()
			->where('active', 1)
			->where('document', 'LIKE', "%$request->supplier%")
			->orWhere('name', 'LIKE', "%$request->supplier%")
			->first();

		return $supplier;
	}

	/**
	 * Find a Client with name or document
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */

	public function filterSupplierList(Request $request)
	{
		if (!$request->supplier || $request->supplier == '') {
			$suppliers = Supplier::select()
				->where('active', 1)
				->get();
		} else {
			$suppliers = Supplier::select()
				->where('active', 1)
				->where('document', 'LIKE', "%$request->supplier%")
				->orWhere('name', 'LIKE', "%$request->supplier%")
				->get(20);
		}

		return $suppliers;
	}
}

