<?php

namespace App\Http\Controllers;

use App\Models\Box;
use App\Models\BoxUser;
use App\Models\NumberingRange; // Modelo de comprobantes (vouchers)
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BoxController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:box.index')->only('index', 'show');
        $this->middleware('can:box.store')->only('store');
        $this->middleware('can:box.update')->only('update');
        $this->middleware('can:box.delete')->only('destroy');
        $this->middleware('can:box.active')->only('activate');
    }

    /**
     * Muestra el listado de cajas con paginación.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'boxes' => Box::paginate(20),
        ]);
    }

    public function getBoxesByUser()
    {
        $boxes = Box::with('numberingRanges')
            ->whereHas('boxUsers', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->get();
    
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'boxes' => $boxes
        ], 200);
    }
    
    
    public function create()
    {
        return abort(404);
    }

    /**
     * Almacena una nueva caja y vincula comprobantes mediante la tabla intermedia.
     *
     * Se espera recibir en el request:
     * - name: string (solo letras y números, máximo 10 caracteres)
     * - printer: string
     * - numbering_ranges_ids: array opcional de IDs de comprobantes a enlazar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'printer' => 'required|string',
            'numbering_ranges_ids' => 'nullable|array',
            'numbering_ranges_ids.*' => 'integer|exists:numbering_ranges,id'
        ], [
            'name.regex' => 'El nombre sólo debe contener letras y números.',
            'name.max' => 'El nombre no debe ser mayor que 10 caracteres.'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validación de datos incorrecta',
                'errors' => $validate->errors()
            ], 400);
        }

        // Crear la caja (aunque la migración actual incluye el campo prefix, aquí se ignora)
        $box = Box::create([
            'name' => $request->name,
            'printer' => $request->printer
        ]);

        // Vincular comprobantes mediante la tabla intermedia (si se envía el array)
        if ($request->has('numbering_ranges_ids')) {
            foreach ($request->numbering_ranges_ids as $nrId) {
                \DB::table('box_numbering_range')->insert([
                    'box_id' => $box->id,
                    'numbering_range_id' => $nrId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // Asignar la caja al usuario autenticado
        BoxUser::create([
            'box_id' => $box->id,
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Registro exitoso',
            'box' => $box
        ], 200);
    }

    /**
     * Muestra una caja en particular.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $box = Box::find($id);

        if ($box) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'box' => $box
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

    public function edit($id)
    {
        return abort(404);
    }

    /**
     * Actualiza una caja y sus vínculos con comprobantes.
     *
     * Se espera recibir en el request:
     * - name: string (solo letras y números, máximo 10 caracteres)
     * - printer: string
     * - numbering_ranges_ids: array opcional de IDs de comprobantes a enlazar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'printer' => 'required|string',
            'numbering_ranges_ids' => 'nullable|array',
            'numbering_ranges_ids.*' => 'integer|exists:numbering_ranges,id'
        ], [
            'name.regex' => 'El nombre sólo debe contener letras y números.',
            'name.max' => 'El nombre no debe ser mayor que 10 caracteres.'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validación de datos incorrecta',
                'errors' => $validate->errors()
            ], 400);
        }

        $box = Box::find($id);

        if ($box) {
            $box->name = $request->name;
            $box->printer = $request->printer;
            $box->update();

            // Actualizar enlaces: primero eliminar los existentes, luego insertar los nuevos
            \DB::table('box_numbering_range')->where('box_id', $box->id)->delete();

            if ($request->has('numbering_ranges_ids')) {
                foreach ($request->numbering_ranges_ids as $nrId) {
                    \DB::table('box_numbering_range')->insert([
                        'box_id' => $box->id,
                        'numbering_range_id' => $nrId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Actualización exitosa',
                'box' => $box
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
     * Actualiza la base de una caja y guarda el historial.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return void
     */
    public function updateBase(Request $request, $id)
    {
        $user = Auth::user();
        $box = Box::find($id);

        if ($box && ($box->base != $request->base)) {

            $data = ([
                'user' => "$user->name $user->last_name",
                'date' => date('Y-m-d'),
                'value' => $request->base
            ]);

            if ($box->history != null) {
                $history = (array) json_decode($box->history);
            } else {
                $history = array();
            }
            array_push($history, $data);

            $box->history = json_encode($history);
            $box->base = $request->base;
            $box->update();
        }
    }

    /**
     * Elimina una caja.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $box = Box::find($id);

        if ($box) {
            $box->delete();
            $data = [
                'status' => 'success',
                'code' => 200,
                'box' => $box
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
     * Cambia el estado (activo/inactivo) de una caja.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activate($id)
    {
        $box = Box::find($id);
        $box->active = !$box->active;
        $box->save();
    }

    /**
     * Obtiene los comprobantes vinculados a una caja a través de la tabla intermedia.
     *
     * @param  \App\Models\Box  $box
     * @return \Illuminate\Http\Response
     */
    public function consecutiveAllByBox(Box $box)
    {
        $ranges = \DB::table('box_numbering_range')
            ->join('numbering_ranges', 'numbering_ranges.id', '=', 'box_numbering_range.numbering_range_id')
            ->where('box_numbering_range.box_id', $box->id)
            ->select('numbering_ranges.*')
            ->get();

        $ranges = collect($ranges);

        $ranges->map(function ($item) use ($box) {
            $bill_number = $item->prefix . $item->from;
            $orders = Order::where('bill_number', $bill_number)->where('box_id', $box->id)->count();
            $item->process = $orders > 0 ? true : false;
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'consecutive' => $ranges
        ], 200);
    }

    public function getAssignUserByBox($id)
    {
        $box = Box::find($id);

        if ($box) {
            $assignments = User::select('users.id', 'users.name')->get();

            $assignments->map(function ($item) use ($box) {
                $item->assign = $item->boxUser()->where('box_id', $box->id)->count();
            });

            $data = [
                'status' => 'success',
                'code' => 200,
                'assignments' => $assignments
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

    public function toAssignUserByBox(Request $request, $id)
    {
        $box = Box::find($id);
        if ($box) {

            $validate = Validator::make($request->all(), [
                'assignments' => 'required|array',
                'assignments.*' => 'integer|exists:users,id'
            ]);

            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Validación de datos incorrecta',
                    'errors' => $validate->errors()
                ];
            } else {

                BoxUser::where('box_id', $box->id)->delete();

                foreach ($request->assignments as $user) {
                    BoxUser::create([
                        'box_id' => $box->id,
                        'user_id' => $user
                    ]);
                }

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Registro exitoso',
                ];
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Registro no encontrado'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function boxList()
    {
        $boxes = Box::where('active', 1)->get();

        if ($boxes) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'boxes' => $boxes
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

    public function toAssignVouchers(Request $request, $id)
    {
        $box = Box::find($id);
        if (!$box) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Caja no encontrada'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'numbering_ranges_ids' => 'required|array',
            'numbering_ranges_ids.*' => 'integer|exists:numbering_ranges,id'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validación de datos incorrecta',
                'errors' => $validate->errors()
            ], 400);
        }

        // Eliminar asignaciones previas para la caja
        \DB::table('box_numbering_range')->where('box_id', $box->id)->delete();

        // Insertar los nuevos vínculos
        foreach ($request->numbering_ranges_ids as $nrId) {
            \DB::table('box_numbering_range')->insert([
                'box_id' => $box->id,
                'numbering_range_id' => $nrId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Asignación de comprobantes exitosa'
        ], 200);
    }

    public function assignedVouchers($id)
    {
        $box = Box::find($id);
        if (!$box) {
            return response()->json([], 404);
        }
        
        $assigned = \DB::table('box_numbering_range')
            ->where('box_id', $box->id)
            ->pluck('numbering_range_id'); // Obtiene solo los IDs
    
        return response()->json($assigned, 200);
    }
}
