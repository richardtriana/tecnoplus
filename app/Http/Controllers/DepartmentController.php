<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipality;

class DepartmentController extends Controller
{
    /**
     * Retorna la lista de departamentos (valores únicos de la columna "department" en municipalities).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistinctDepartments()
    {
        // Se obtienen los valores únicos de la columna "department"
        $departments = Municipality::select('department')
            ->distinct()
            ->get();
        return response()->json(['departments' => $departments], 200);
    }

    /**
     * Retorna la lista de municipios que pertenezcan a un departamento dado.
     * @param string $department
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMunicipalitiesByDepartment($department)
    {
        // Filtra municipios donde la columna "department" sea igual al valor recibido.
        $municipalities = Municipality::where('department', $department)->get();
        return response()->json(['municipalities' => $municipalities], 200);
    }
}
