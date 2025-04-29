<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdjustmentNote;
use App\Models\AdjustmentNoteItem;
use App\Models\NumberingRange;
use Illuminate\Support\Facades\Validator;
use App\Services\FactusAdjustmentNoteService;

class AdjustmentNoteController extends Controller
{
    protected $factusAdjustmentNoteService;

    public function __construct(FactusAdjustmentNoteService $factusAdjustmentNoteService)
    {
        $this->factusAdjustmentNoteService = $factusAdjustmentNoteService;
    }

    /**
     * Crea y valida una Nota de Ajuste al Documento Soporte.
     *
     * Se espera que el cuerpo de la solicitud contenga:
     * - numbering_range_id: ID del rango de numeración (activo para notas de ajuste).
     * - payment_method_code: Código del método de pago (opcional, por defecto "10").
     * - support_document_id: ID del documento soporte a ajustar.
     * - correction_concept_code: Código del motivo del ajuste.
     * - observation: Observación (máximo 250 caracteres).
     * - items: Array de objetos con la información de cada producto/servicio.
     *
     * Para cada item se requiere:
     * - code_reference, name, quantity, discount_rate, price, unit_measure_id, standard_code_id.
     * - withholding_taxes (opcional, array de retenciones).
     *
     * Se genera el reference_code automáticamente como:
     *    reference_code = numbering_range.prefix + numbering_range.current
     * y se incrementa el valor de current en el rango.
     *
     * Luego se envía la nota a Factus a través del servicio, y se actualiza el registro con la respuesta.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Nota: Se elimina 'reference_code' de la validación porque se genera automáticamente.
        $validator = Validator::make($request->all(), [
            'numbering_range_id'     => 'required|integer',
            'payment_method_code'    => 'nullable|string',
            'support_document_id'    => 'required|integer',
            'correction_concept_code'=> 'required|string',
            'observation'            => 'nullable|string|max:250',
            'items'                  => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'code'    => 422,
                'message' => 'Validación de datos incorrecta',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Obtener el rango de numeración
        $range = NumberingRange::find($request->numbering_range_id);
        if (!$range) {
            return response()->json([
                'status' => 'error',
                'code'   => 422,
                'message' => 'Rango de numeración no encontrado'
            ], 422);
        }

        // Generar el reference_code usando prefix y current del rango (por ejemplo, "NDS12")
        $referenceCode = $range->prefix . $range->current;

        // Crear la Nota de Ajuste localmente con el reference_code generado
        $adjustmentNote = AdjustmentNote::create([
            'reference_code'         => $referenceCode,
            'numbering_range_id'     => $request->numbering_range_id,
            'payment_method_code'    => $request->payment_method_code ?? '10',
            'support_document_id'    => $request->support_document_id,
            'correction_concept_code'=> $request->correction_concept_code,
            'observation'            => $request->observation,
        ]);

        // Crear los items asociados a la nota de ajuste
        foreach ($request->items as $itemData) {
            AdjustmentNoteItem::create([
                'adjustment_note_id' => $adjustmentNote->id,
                'service_id'         => $itemData['service_id'] ?? null,
                'code_reference'     => $itemData['code_reference'] ?? '',
                'name'               => $itemData['name'],
                'quantity'           => $itemData['quantity'],
                'discount_rate'      => $itemData['discount_rate'] ?? 0,
                'price'              => $itemData['price'],
                'unit_measure_id'    => $itemData['unit_measure_id'] ?? null,
                'standard_code_id'   => $itemData['standard_code_id'] ?? null,
                'withholding_taxes'  => isset($itemData['withholding_taxes']) ? json_encode($itemData['withholding_taxes']) : null,
            ]);
        }

        // Incrementar el campo "current" del rango de numeración
        $range->current += 1;
        $range->save();

        // Enviar la Nota de Ajuste a Factus
        $adjustmentNote->load('items');
        $factusResponse = $this->factusAdjustmentNoteService->sendAdjustmentNote($adjustmentNote);
        if ($factusResponse) {
            // La respuesta viene anidada en "data" -> "adjustment_note"
            $factusNote = $factusResponse['data']['adjustment_note'] ?? null;
            if ($factusNote) {
                $adjustmentNote->status    = $factusNote['status'] ?? null;
                $adjustmentNote->number    = $factusNote['number'] ?? null;
                $adjustmentNote->qr        = $factusNote['qr'] ?? null;
                $adjustmentNote->cuds      = $factusNote['cuds'] ?? null;
                $adjustmentNote->validated = $factusNote['validated'] ?? null;
                $adjustmentNote->qr_image  = $factusNote['qr_image'] ?? null;
                $adjustmentNote->errors    = isset($factusNote['errors']) ? json_encode($factusNote['errors']) : null;
                $adjustmentNote->total     = $factusNote['total'] ?? 0;
                $adjustmentNote->save();
            }
        }

        return response()->json([
            'status'          => 'success',
            'code'            => 201,
            'message'         => 'Nota de ajuste creada y validada exitosamente',
            'adjustment_note' => $adjustmentNote
        ], 201);
    }
}
