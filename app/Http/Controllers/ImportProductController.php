<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Helper\Sample; //eliminar


class ImportProductController extends Controller
{
  // public function __construct()
  // {
  //   $this->middleware('can:product.store');
  // }

  public function downloadExample()
  {
    $path = public_path('files/productos.xls');
    return response()->download($path);
  }

  public function uploadFile(Request $request)
  {
    $request->validate([
      'file' => 'required|mimes:csv,xlx,xls,xlsx|max:4096'
      // 'file' => 'required|mimes:csv,xlx,xls|max:2048'
    ]);
    // 'file' => 'required|mimes:csv,xlx,xls|max:2048'

    if ($request->file()) {
      $upload_path = public_path('uploads');
      $file_name = $request->file->getClientOriginalName();
      $generated_new_name = time() . '.' . $request->file->getClientOriginalExtension();

      if ($request->file->move($upload_path, $generated_new_name)) {
        $products = $this->uploadData((public_path('uploads')) . '\\' . $generated_new_name);
        return $this->inserData($products);
      }
    }
  }

  public function uploadData($file)
  {
    $helper = new Sample();
    $sheetData = array();
    $inputFileName = $file;
    $spreadsheet = IOFactory::load($inputFileName);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    return ['products' => $sheetData];
  }

  public function inserData($productsData)
  {
    if (is_array($productsData) && count($productsData) > 0) {

      foreach ($productsData as $item) {

        $total_items = 0;
        $item_saved = 0;
        $item_error = 0;
        $item_cost_null = 0;

        foreach ($item as $p) {
          if (Product::where('barcode', $p["A"])->exists()) {
            $item_error += 1;
          } else {
            $tax_id = 1;
            $tax_percentage = 0;
            $percentage = 0;

            if (isset($p["J"]) && (float)$p["J"] != '') {
              $tax = Tax::firstOrCreate(['percentage' => (float)$p["J"], 'name' => "Impuesto al {$p['J']} %"]);
              $tax_id = $tax->id;
              $tax_percentage = (float)$tax->percentage;

              if ($tax_percentage > 0) {
                $percentage = (float)($tax_percentage / 100) + 1;
              }
            } else {
              $tax = Tax::firstOrCreate(['percentage' => '0', 'name' => "Impuesto al 0 %"]);
              $tax_id = $tax->id;
              $percentage = 0;
            }

            $type = 1;

            if (isset($p["I"])) {
              if ($p["I"] == 'UNIDAD') {
                $type =  1;
              }

              if ($p["I"] == 'GRANEL') {
                $type =  2;
              }
            }

            if (($p["C"]) != null && ($p["B"] != "Producto")) {

              $cost_price_tax_inc = (float) str_replace(',', '', preg_replace('/[$\@\;\" "]+/', '', $p["C"]));
              $sale_price_tax_inc = (float) str_replace(',', '', preg_replace('/[$\@\;\" "]+/', '', $p["D"]));
              $wholesale_price_tax_inc = (float) str_replace(',', '', preg_replace('/[$\@\;\" "]+/', '', $p["E"]));
              $quantity = (float) str_replace(',', '', preg_replace('/[$\@\;\" "]+/', '', $p["F"]));
              $minimum = (float) str_replace(',', '', preg_replace('/[$\@\;\" "]+/', '', $p["G"]));
              $maximum = (float) str_replace(',', '', preg_replace('/[$\@\;\" "]+/', '', $p["H"]));

              if ((float)$cost_price_tax_inc == 0) {
                $item_cost_null += 1;
              }
              if (is_float((float)$cost_price_tax_inc) && ((float)$cost_price_tax_inc) >= 0) {

                $category_id = NULL;
                if (isset($p["K"]) && $p["K"] != '') {
                  $category = Category::firstOrCreate(['name' => $p["K"]]);
                  $category_id = $category->id;
                }

                $brand_id = NULL;
                if (isset($p["L"]) && $p["L"] != '') {
                  $brand = Brand::firstOrCreate(['name' => $p["L"]]);
                  $brand_id = $brand->id;
                }

                $product = new Product();
                $product->barcode = $p["A"];
                $product->product = $p["B"];
                $product->cost_price_tax_inc = $cost_price_tax_inc;
                $product->sale_price_tax_inc = $sale_price_tax_inc;
                $product->wholesale_price_tax_inc = $wholesale_price_tax_inc;
                $product->category_id = $category_id;
                $product->brand_id = $brand_id;
                $product->quantity = $quantity;
                $product->minimum = $minimum;
                $product->maximum = $maximum;
                $product->type = $type;
                $product->tax_id = $tax_id;

                if ($percentage != 0) {
                  $product->cost_price_tax_exc = ($cost_price_tax_inc) / ($percentage);
                  $product->sale_price_tax_exc = ($sale_price_tax_inc) / ($percentage);
                  $product->wholesale_price_tax_exc = ($wholesale_price_tax_inc) / ($percentage);
                } else {
                  $product->cost_price_tax_exc = $cost_price_tax_inc;
                  $product->sale_price_tax_exc = ($sale_price_tax_inc);
                  $product->wholesale_price_tax_exc = ($wholesale_price_tax_inc);
                }
                $product->gain = $product->sale_price_tax_exc - $product->cost_price_tax_inc;

                try {
                  $product->save();
                  $item_saved += 1;
                } catch (\Throwable $th) {
                  //throw $th;
                  $item_error += 1;
                }
              }
            }
          }
          $total_items += 1;
          $data = [
            'item_saved' => $item_saved,
            'item_error' => $item_error,
            'item_cost_null' => $item_cost_null,
            'total_items' => $total_items

          ];
        }
      }
    }


    return $data;
  }
}
