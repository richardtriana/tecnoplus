<?php

namespace App\Http\Controllers;

use App\Models\ProductTribute;
use Illuminate\Http\Request;

class ProductTributeController extends Controller
{
    public function index()
    {
        $tributes = ProductTribute::all();
        return response()->json(['data' => $tributes], 200);
    }
}
