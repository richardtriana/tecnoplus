<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportDocument;
use App\Models\SupportDocumentItem;
use App\Models\NumberingRange;
use Illuminate\Support\Facades\Validator;
use App\Services\FactusSupportDocumentService;

class SupportDocumentController extends Controller
{
    protected $factusSupportDocumentService;

    public function __construct(FactusSupportDocumentService $factusSupportDocumentService)
    {
        $this->factusSupportDocumentService = $factusSupportDocumentService;
    }

    /**
     * Muestra una lista paginada de Documentos Soporte.
     */
    public function index(Request $request)
    {
        $documents = SupportDocument::with('items')->paginate(15);

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'documents' => $documents
        ]);
    }

    /**
     * Crea un nuevo Documento Soporte y, si el comprobante tiene "enviado_dian": 1, lo envía a Factus.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_code'        => 'required|string|unique:support_documents,reference_code',
            'numbering_range_id'    => 'required|integer',
            'payment_method_code'   => 'required|string',
            'provider_id'           => 'required|integer',
            'total'                 => 'required|numeric',
            'services'              => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 422,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Crear el documento soporte localmente
        $document = SupportDocument::create([
            'reference_code'        => $request->reference_code,
            'numbering_range_id'    => $request->numbering_range_id,
            'payment_method_code'   => $request->payment_method_code,
            'observation'           => $request->observation,
            'provider_id'           => $request->provider_id,
            'number'                => $request->number ?? null,
            'status'                => $request->status ?? null,
            'qr'                    => $request->qr ?? null,
            'cuds'                  => $request->cuds ?? null,
            'validated'             => $request->validated ?? null,
            'discount_rate'         => $request->discount_rate ?? 0,
            'discount'              => $request->discount ?? 0,
            'gross_value'           => $request->gross_value ?? 0,
            'taxable_amount'        => $request->taxable_amount ?? 0,
            'tax_amount'            => $request->tax_amount ?? 0,
            'total'                 => $request->total,
            'errors'                => $request->errors ? json_encode($request->errors) : null,
            'qr_image'              => $request->qr_image ?? null,
        ]);

        // Crear los items asociados al documento
        foreach ($request->services as $itemData) {
            SupportDocumentItem::create([
                'support_document_id' => $document->id,
                'service_id'          => $itemData['service_id'] ?? null,
                'code_reference'      => $itemData['code_reference'] ?? $itemData['codigo'] ?? '',
                'name'                => $itemData['name'],
                'quantity'            => $itemData['quantity'],
                'discount_rate'       => $itemData['discount_rate'] ?? 0,
                'discount'            => $itemData['discount'] ?? 0,
                'gross_value'         => $itemData['gross_value'] ?? 0,
                'tax_rate'            => $itemData['tax_rate'] ?? 0,
                'taxable_amount'      => $itemData['taxable_amount'] ?? 0,
                'tax_amount'          => $itemData['tax_amount'] ?? 0,
                'price'               => $itemData['price'],
                'is_excluded'         => $itemData['is_excluded'] ?? 0,
                'unit_measure_id'     => $itemData['unit_measure_id'] ?? null,
                'standard_code_id'    => $itemData['standard_code_id'] ?? null,
                'total'               => $itemData['total'] ?? 0,
                'withholding_taxes'   => isset($itemData['withholding_taxes']) ? json_encode($itemData['withholding_taxes']) : null,
            ]);
        }

        // Incrementar el campo "current" del rango de numeración
        $range = NumberingRange::find($document->numbering_range_id);
        if ($range) {
            $range->current += 1;
            $range->save();
        }

        // Envío a Factus: se procede si el rango tiene "enviado_dian" igual a 1.
        if ($range && (int)$range->enviado_dian === 1) {
            // Cargar relaciones necesarias para el envío
            $document->load('provider', 'items');
            $factusResponse = $this->factusSupportDocumentService->sendSupportDocument($document);
            if ($factusResponse) {
                // Se accede a "data" y luego a "support_document" para obtener la respuesta de Factus
                $supportDoc = $factusResponse['data']['support_document'] ?? null;
                if ($supportDoc) {
                    // Almacenar el id retornado por Factus en la columna factus_support_document_id
                    $document->factus_support_document_id = $supportDoc['id'] ?? null;
                    $document->validated = $supportDoc['validated'] ?? null;
                    $document->qr        = $supportDoc['qr'] ?? null;
                    $document->cuds      = $supportDoc['cuds'] ?? null;
                    $document->qr_image  = $supportDoc['qr_image'] ?? null;
                    $document->number    = $supportDoc['number'] ?? null;
                    $document->errors    = isset($supportDoc['errors'])
                                           ? json_encode($supportDoc['errors'])
                                           : null;
                    // Actualizar el status retornado por Factus
                    $document->status    = $supportDoc['status'] ?? null;
                    $document->save();
                }
            }
        }

        return response()->json([
            'status'   => 'success',
            'code'     => 201,
            'message'  => 'Documento soporte creado exitosamente',
            'document' => $document
        ], 201);
    }

    /**
     * Muestra un Documento Soporte en específico junto con sus items.
     */
    public function show($id)
    {
        $document = SupportDocument::with('items')->find($id);

        if (!$document) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Documento soporte no encontrado'
            ], 404);
        }

        return response()->json([
            'status'   => 'success',
            'code'     => 200,
            'document' => $document
        ], 200);
    }

    /**
     * Actualiza un Documento Soporte y sus items.
     */
    public function update(Request $request, $id)
    {
        $document = SupportDocument::find($id);

        if (!$document) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Documento soporte no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'reference_code'      => 'required|string|unique:support_documents,reference_code,'.$id,
            'numbering_range_id'  => 'required|integer',
            'payment_method_code' => 'required|string',
            'provider_id'         => 'required|integer',
            'total'               => 'required|numeric',
            'services'            => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 422,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Actualizar el documento soporte
        $document->update([
            'reference_code'      => $request->reference_code,
            'numbering_range_id'  => $request->numbering_range_id,
            'payment_method_code' => $request->payment_method_code,
            'observation'         => $request->observation,
            'provider_id'         => $request->provider_id,
            'number'              => $request->number,
            'status'              => $request->status,
            'qr'                  => $request->qr,
            'cuds'                => $request->cuds,
            'validated'           => $request->validated,
            'discount_rate'       => $request->discount_rate ?? 0,
            'discount'            => $request->discount ?? 0,
            'gross_value'         => $request->gross_value ?? 0,
            'taxable_amount'      => $request->taxable_amount ?? 0,
            'tax_amount'          => $request->tax_amount ?? 0,
            'total'               => $request->total,
            'errors'              => $request->errors ? json_encode($request->errors) : null,
            'qr_image'            => $request->qr_image,
        ]);

        // Eliminar los items existentes y recrearlos
        $document->items()->delete();
        foreach ($request->services as $itemData) {
            SupportDocumentItem::create([
                'support_document_id' => $document->id,
                'service_id'          => $itemData['service_id'] ?? null,
                'code_reference'      => $itemData['code_reference'] ?? $itemData['codigo'] ?? '',
                'name'                => $itemData['name'],
                'quantity'            => $itemData['quantity'],
                'discount_rate'       => $itemData['discount_rate'] ?? 0,
                'discount'            => $itemData['discount'] ?? 0,
                'gross_value'         => $itemData['gross_value'] ?? 0,
                'tax_rate'            => $itemData['tax_rate'] ?? 0,
                'taxable_amount'      => $itemData['taxable_amount'] ?? 0,
                'tax_amount'          => $itemData['tax_amount'] ?? 0,
                'price'               => $itemData['price'],
                'is_excluded'         => $itemData['is_excluded'] ?? 0,
                'unit_measure_id'     => $itemData['unit_measure_id'] ?? null,
                'standard_code_id'    => $itemData['standard_code_id'] ?? null,
                'total'               => $itemData['total'] ?? 0,
                'withholding_taxes'   => isset($itemData['withholding_taxes']) ? json_encode($itemData['withholding_taxes']) : null,
            ]);
        }

        return response()->json([
            'status'   => 'success',
            'code'     => 200,
            'message'  => 'Documento soporte actualizado exitosamente',
            'document' => $document
        ], 200);
    }

    /**
     * Elimina un Documento Soporte junto con sus items.
     */
    public function destroy($id)
    {
        $document = SupportDocument::find($id);

        if (!$document) {
            return response()->json([
                'status'  => 'error',
                'code'    => 404,
                'message' => 'Documento soporte no encontrado'
            ], 404);
        }

        $document->delete();

        return response()->json([
            'status'  => 'success',
            'code'    => 200,
            'message' => 'Documento soporte eliminado exitosamente'
        ], 200);
    }
}
