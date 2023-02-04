<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetailBilling;

class DetailBillingController extends Controller
{
	public function __construct()
	{
		$this->middleware('can:billing.index')->only('index');
		$this->middleware('can:billing.store')->only('store');
		$this->middleware('can:billing.update')->only('update');
		$this->middleware('can:billing.delete')->only('destroy');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
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
	public function store($request, $billing_id)
	{
		$detail = new DetailBilling;
		$detail->billing_id = $billing_id;
		$detail->product_id = $request['product_id'];
		$detail->barcode = $request['barcode'];
		$detail->discount_percentage = $request['discount_percentage'];
		$detail->discount_price = $request['discount_price'];
		$detail->price_tax_exc = $request['cost_price_tax_exc'];
		$detail->price_tax_inc = $request['cost_price_tax_inc'];
		$detail->price_tax_inc_total = $request['cost_price_tax_inc_total'];
		$detail->quantity = $request['quantity'];
		$detail->product = $request['product'];
		$detail->save();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\DetailBilling  $detailBilling
	 * @return \Illuminate\Http\Response
	 */
	public function show(DetailBilling $detailBilling)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\DetailBilling  $detailBilling
	 * @return \Illuminate\Http\Response
	 */
	public function edit(DetailBilling $detailBilling)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\DetailBilling  $detailBilling
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, DetailBilling $detailBilling)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\DetailBilling  $detailBilling
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$detailBilling = DetailBilling::find($id);
		$detailBilling->delete();
	}
}
