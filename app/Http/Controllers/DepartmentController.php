<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Municipality;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

class DepartmentController extends Controller
{
    public function index(){
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'departments' => Department::all(),
        ]);
    }

    public function getMunicipalitiesByDepartment($id){
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'municipalities' => Municipality::where('department_id', $id)->get(),
        ]);
    }
}
