<?php

namespace App\Http\Controllers;

use App\Models\KitProduct;
use Illuminate\Http\Request;

class KitProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->parent_id){
           return KitProduct::select()->where('product_parent_id', $request->parent_id)->get();
        }
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
        // $kitProduct = new KitProduct();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\KitProduct  $kitProduct
     * @return \Illuminate\Http\Response
     */
    public function show(KitProduct $kitProduct)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\KitProduct  $kitProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(KitProduct $kitProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\KitProduct  $kitProduct
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, KitProduct $kitProduct)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\KitProduct  $kitProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(KitProduct $kitProduct)
    {
        $kitProduct->delete();
    }
}
