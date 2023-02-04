<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\RoleDefault;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:rol.index')->only('index', 'show');
        $this->middleware('can:rol.store')->only('store', 'getPermissions');
        $this->middleware('can:rol.update')->only('update');
        $this->middleware('can:rol.delete')->only('destroy');
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
            'roles' => RoleDefault::paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return  abort(404);
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
            'name' => 'required|string|unique:roles|min:3|max:50',
            'permissions_sync' => 'nullable|array|exists:permissions,id'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Validación de datos incorrecta',
                'errors' =>  $validate->errors()
            ], 400);
        }

        $role = Role::create(['guard_name' => 'api', 'name' => $request->input('name')]);

        $role->syncPermissions($request->input('permissions_sync'));

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Registro exitoso',
            'role' => $role
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
        $role = Role::find($id);

        if ($role) {

            $permissions = collect($role->permissions)->groupBy('component');
            unset($role->permissions);

            $role->permissions = $permissions;

            $data = [
                'status' => 'success',
                'code' => 200,
                'role' => $role,
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
        return abort(404);
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
            'name' => [
                'required', 'string', 'min:3', 'max:50',  Rule::unique('roles')->ignore($id)
            ],
            'permissions_sync' => 'nullable|array|exists:permissions,id'
        ]);
        
        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' =>  400,
                'message' => 'Validación de datos incorrecta',
                'errors' =>  $validate->errors()
            ], 400);
        }

        $role = Role::find($id);

        if ($role) {

            $role->name = $request->input('name');
            $role->save();

            $role->syncPermissions($request->input('permissions_sync'));
   
            $data = [
                'status' => 'success',
                'code' =>  200,
                'message' => 'Actualización exitosa',
                'role' =>  $role
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
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if ($role) {
            $role->delete();
            $data = [
                'status' => 'success',
                'code' => 200,
                'role' => $role
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
    public function getAllRoles()
    {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'roles' => Role::all()
        ]);
    }
    public function getPermissions()
    {
        $permissions = collect(Permission::all());
        $permissions = $permissions->groupBy('component');

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'permissions' => $permissions,
        ]);
    }

}
