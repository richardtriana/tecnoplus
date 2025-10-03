<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigurationController extends Controller
{
    /**
     * Mostrar la configuración (primer y único registro)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config = Configuration::first();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'configuration' => $config
        ], 200);
    }

    /**
     * Guardar o actualizar la configuración
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Reglas de validación, incluyendo shipments
        $rules = [
            'name'                  => 'required|string|min:3|max:100',
            'legal_representative'  => 'required|string|min:3|max:150',
            'nit'                   => 'required|string|min:8|max:15',
            'address'               => 'required|string|min:3|max:150',
            'email'                 => 'required|email',
            'tax_regime'            => 'required|string|min:3|max:255',
            'telephone'             => 'required|string|min:5|max:13',
            'mobile'                => 'required|string|min:5|max:13',
            'printer'               => 'required|string|min:3|max:100',
            'file0'                 => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
            'condition_order'       => 'nullable|string',
            'condition_quotation'   => 'nullable|string',
            'shipments'             => 'sometimes|boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 400,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validator->errors()
            ], 400);
        }

        // Si llegó una imagen, procesarla
        if ($request->hasFile('file0')) {
            $imageName = uniqid() . '_' . $request->file('file0')->getClientOriginalName();
            $request->file('file0')->move(public_path('storage/images'), $imageName);
            $request->merge(['logo' => 'storage/images/' . $imageName]);
        }

        // Procesar shipments (checkbox slider entrega 1 o 0)
        $request->merge(['shipments' => $request->boolean('shipments')]);

        // updateOrCreate por id
        $configuration = Configuration::updateOrCreate(
            ['id' => $request->id],
            $request->only([
                'name',
                'legal_representative',
                'nit',
                'address',
                'email',
                'tax_regime',
                'telephone',
                'mobile',
                'logo',
                'printer',
                'condition_order',
                'condition_quotation',
                'shipments'
            ])
        );

        return response()->json([
            'status'        => 'success',
            'code'          => 200,
            'message'       => 'Configuración guardada correctamente',
            'configuration' => $configuration
        ], 200);
    }

    /**
     * API para consultar solo el estado de shipments
     *
     * @return \Illuminate\Http\Response
     */
    public function getShipmentsStatus()
    {
        $config = Configuration::first();
        $status = $config ? (bool)$config->shipments : false;

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'shipments' => $status
        ], 200);
    }
}
