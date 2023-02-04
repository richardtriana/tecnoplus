<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
  public function __construct()
  {
    $this->middleware('can:brand.index')->only('index', 'show');
    $this->middleware('can:brand.store')->only('store');
    $this->middleware('can:brand.update')->only('update');
    $this->middleware('can:brand.delete')->only('destroy');
    $this->middleware('can:brand.active')->only('activate');
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
      'brands' => Brand::paginate(20),
    ]);
  }

  public function brandList()
  {
    $brands = Brand::where('active', 1)->get();

    if ($brands) {
      $data = [
        'status' => 'success',
        'code' => 200,
        'brands' => $brands
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
      'name' => 'required|string|min:3|max:50'
    ]);

    if ($validate->fails()) {
      return response()->json([
        'status' => 'error',
        'code' =>  400,
        'message' => 'Validación de datos incorrecta',
        'errors' =>  $validate->errors()
      ], 400);
    }

    $brand = Brand::create([
      'name' => $request->input('name')
    ]);

    return response()->json([
      'status' => 'success',
      'code' => 200,
      'message' => 'Registro exitoso',
      'brand' => $brand
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
    $brand = Brand::find($id);

    if ($brand) {
      $data = [
        'status' => 'success',
        'code' => 200,
        'brand' => $brand
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
      'name' => 'required|string|min:3|max:50'
    ]);

    if ($validate->fails()) {
      return response()->json([
        'status' => 'error',
        'code' =>  400,
        'message' => 'Validación de datos incorrecta',
        'errors' =>  $validate->errors()
      ], 400);
    }

    $brand = Brand::find($id);

    if ($brand) {
      $brand->name = $request->input('name');
      $brand->save();
      $data = [
        'status' => 'success',
        'code' =>  200,
        'message' => 'Actualización exitosa',
        'brand' =>  $brand
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
    $brand = Brand::find($id);

    if ($brand) {
      $brand->delete();
      $data = [
        'status' => 'success',
        'code' => 200,
        'brand' => $brand
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
    $brand = Brand::find($id);
    $brand->active = !$brand->active;
    $brand->save();
  }
}
