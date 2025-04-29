<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Configuration;
use App\Models\Order;
use App\Models\NumberingRange;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Str;
use PDF;

class PrintOrderController extends Controller
{
    /**
     * Envía un pulso a la impresora para abrir la caja.
     */
    public function openBox($order_id)
    {
        $order = Order::find($order_id);
        $box = $order->box()->first();

        $configuration = new Configuration();
        $company = $configuration->select()->first();

        $POS_printer = $box ? $box->printer : $company->printer;

        try {
            if (!$POS_printer || $POS_printer == '') {
                throw new Exception("Error, no hay impresoras configuradas", 400);
            }
            $connector = new WindowsPrintConnector($POS_printer);
            $printer = new Printer($connector);
            $printer->initialize();
            $printer->pulse();
            $printer->close();
        } catch (\Throwable $th) {
            Log::error("Error in openBox: " . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            throw new Exception("Error, no se pudo completar el proceso: " . $th->getMessage(), 400);
        }
    }

    /**
     * Imprime el ticket de la orden.
     */
    public function printTicket($order_id, $cash = null, $change = null)
    {
        $order = Order::find($order_id);
        if (!$order) {
            throw new Exception("Order not found", 404);
        }
        $order_details = $order->detailOrders()->get();
        $system_user = $order->user()->first();
        $payment_methods = (object) $order->payment_methods;
        $box = $order->box()->first();

        $configuration = new Configuration();
        $company = $configuration->select()->first();
        $POS_printer = $box ? $box->printer : $company->printer;

        if (!$POS_printer || $POS_printer == '') {
            throw new Exception("Error, no hay impresoras configuradas", 400);
        }

        $connector = new WindowsPrintConnector($POS_printer);

        try {
            $printer = new Printer($connector);
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            try {
                $logo = EscposImage::load($company->logo, false);
                $printer->bitImage($logo);
            } catch (Exception $e) {
                Log::error("Logo error in printTicket: " . $e->getMessage());
            }

            // Datos de la empresa
            $printer->setTextSize(1, 2);
            $printer->setEmphasis(true);
            $printer->text($company->name . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text("Responsabilidad: " . $company->condition_order . "\n");
            $printer->text("NIT: " . $company->nit . "\n");
            $printer->text("Dirección: " . $company->address . "\n");
            $printer->setEmphasis(true);
            $printer->text("Cajero(a): " . $system_user->name . "\n");
            $printer->setEmphasis(false);
            $printer->text("Fecha: " . date('Y-m-d h:i:s A') . "\n");
            if ($order->table) {
                $printer->text("Mesa: " . $order->table->table . "\n");
            }

            $printer->text("\n");
            // N° Factura en negrita
            $printer->setEmphasis(true);
            $printer->text("N° Factura: ");
            if (isset($order->bill_number)) {
                $printer->text($order->bill_number . "\n");
            } else {
                $printer->text($order->id . "\n");
            }
            $printer->setEmphasis(false);
            // Mostrar Forma y Método de Pago con su nombre
            $printer->text("Forma de Pago: " . $this->getPaymentFormName($order->payment_form_id) . "\n");
            $printer->text("Método de Pago: " . $this->getPaymentMethodName($order->payment_method_id) . "\n");

            // Estado y CUFE
            $estadoDian = ($order->factus_status == 1) ? 'Recibida' : 'No Recibida';
            $printer->text("Estado Dian: " . $estadoDian . "\n");
            $printer->text("CUFE: " . $order->cufe . "\n");
            $printer->setTextSize(1, 2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            switch ($order->state) {
                case '1':
                    $printer->text("Factura suspendida");
                    break;
                case '2':
                    $printer->text("Factura Electronica");
                    break;
                case '3':
                    $printer->text("Cotización");
                    break;
                case '5':
                    $printer->text("Crédito");
                    break;
                default:
                    $printer->text("Factura Electronica");
                    break;
            }
            $printer->setTextSize(1, 1);
            // Impresión de datos del cliente
            $printer->text("\n-----------------------------------------\n");
            $printer->text(sprintf('%-16s %-25s', 'Primer Nombre:', $order->client->first_name) . "\n");
            $printer->text(sprintf('%-16s %-25s', 'Segundo Nombre:', $order->client->second_name) . "\n");
            $printer->text(sprintf('%-16s %-25s', 'Primer Apellido:', $order->client->first_lastname) . "\n");
            $printer->text(sprintf('%-16s %-25s', 'Segundo Apellido:', $order->client->second_lastname) . "\n");
            $printer->text(sprintf('%-16s %-25s', 'Razón Social:', $order->client->razon_social) . "\n");
            if ($order->client->document) {
                $printer->text(sprintf('%-16s %-25s', 'Documento:', $order->client->document) . "\n");
            }
            if ($order->client->div_verification) {
                $printer->text(sprintf('%-16s %-25s', 'Div. Verif.:', $order->client->div_verification) . "\n");
            }
            if ($order->client->phone) {
                $printer->text(sprintf('%-16s %-25s', 'Teléfono:', $order->client->phone) . "\n");
            }
            if ($order->client->email) {
                $printer->text(sprintf('%-16s %-25s', 'Email:', $order->client->email) . "\n");
            }
            if ($order->client->address) {
                $printer->text(sprintf('%-16s %-25s', 'Dirección:', $order->client->address) . "\n");
            }
            if ($order->client->municipality) {
                $printer->text(sprintf('%-16s %-25s', 'Municipio:', $order->client->municipality->name) . "\n");
            }
            if ($order->client->clientTribute) {
                $printer->text(sprintf('%-16s %-25s', 'Tributo Cliente:', $order->client->clientTribute->name) . "\n");
            }
            if ($order->client->identityDocumentType) {
                $printer->text(sprintf('%-16s %-25s', 'Tipo Documento:', $order->client->identityDocumentType->name) . "\n");
            }
            if ($order->observations) {
                $printer->text(sprintf('%-16s %-25s', 'Observaciones:', $order->observations) . "\n");
            }

            $printer->text("\n");
            $printer->setLineSpacing(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\n-----------------------------------------\n\n");
            $printer->setLineSpacing(1);
            $printer->setEmphasis(true);
            $printer->text(sprintf('%-22s %+8s %+10.7s', 'ARTICULO', 'CANT', 'PRECIO'));
            $printer->text("\n-----------------------------------------\n\n");
            $printer->setEmphasis(false);
            $printer->text("\n");

            $total = 0;
            foreach ($order_details as $df) {
                $line = sprintf('%-22s %8.2f %10.2f ', '-' . mb_strimwidth($df->product, 0, 21, ''), $df->quantity, $df->price_tax_inc_total);
                $total += $df->price_tax_inc_total;
                $printer->text($line . "\n");
            }
            $printer->text("\n==========================================\n");
            $printer->setEmphasis(true);
            $printer->setLineSpacing(2);
            $printer->text("\n");
            $printer->text(sprintf('%-25s %+15.15s', 'Subtotal', number_format($order->total_iva_exc, 2)));
            $printer->text("\n");
            $printer->text(sprintf('%-25s %+15.15s', 'Iva', number_format($order->total_iva_inc - $order->total_iva_exc, 2, '.', ',')));
            $printer->text("\n");
            $printer->setTextSize(1, 2);
            $printer->text(sprintf('%-25s %+15.15s', 'TOTAL', number_format($total, 2, '.', ',')));
            $printer->text("\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);

            if (isset($payment_methods->cash) && $payment_methods->cash > 0) {
                $printer->text(sprintf('%-25s %+15.15s', 'Efectivo', number_format($payment_methods->cash, 2, '.', ',')) . "\n");
            }
            if (isset($payment_methods->card) && $payment_methods->card > 0) {
                $printer->text(sprintf('%-25s %+15.15s', 'Tarjeta', number_format($payment_methods->card, 2, '.', ',')) . "\n");
            }
            if (isset($payment_methods->nequi) && $payment_methods->nequi > 0) {
                $printer->text(sprintf('%-25s %+15.15s', 'Nequi', number_format($payment_methods->nequi, 2, '.', ',')) . "\n");
            }
            if (isset($payment_methods->others) && $payment_methods->others > 0) {
                $printer->text(sprintf('%-25s %+15.15s', 'Otros', number_format($payment_methods->others, 2, '.', ',')) . "\n");
            }
            $printer->setEmphasis(true);
            if (isset($payment_methods->change) && ($payment_methods->change >= 0)) {
                $printer->text(sprintf('%-25s %+15.15s', 'Cambio', number_format($payment_methods->change, 2, '.', ',')) . "\n");
            }
            $printer->setEmphasis(false);

            if ($change !== null && $change >= 0) {
                $printer->text("\n");
                $printer->text(sprintf('%-25s %+15.15s', 'Cambio', number_format($change, 2, '.', ',')));
            }
            $printer->text("\n");

            // Impresión de numeración usando numbering_range_id de la orden
            // Si la orden tiene asignado un numbering_range_id, se busca el registro directamente.
            if ($order->numbering_range_id) {
                $range = NumberingRange::find($order->numbering_range_id);
            } else {
                // Si no, se toma el primer registro asociado a la caja.
                $range = $box ? $box->numberingRanges()->first() : null;
            }
            if ($range && $range->start_date && $range->end_date) {
                $from_date = Carbon::createFromFormat('Y-m-d', $range->start_date);
                $until_date = Carbon::createFromFormat('Y-m-d', $range->end_date);
            
                $printer->setJustification(Printer::JUSTIFY_CENTER);
            
                // Primera línea: Resolución de facturación
                $printer->text("Resolución de facturación: " . $range->resolution_number . "\n");
            
                // Segunda línea: Prefijo y rango de numeración
                $printer->text("Autorizada con prefijo " . $range->prefix .
                    " desde el N° " . $range->from . " hasta " . $range->to . "\n");
            
                // Tercera línea: Vigencia en meses y fechas de inicio-fin
                $mesesVigencia = $until_date->month - $from_date->month;
                $printer->text("Vigencia de " . $mesesVigencia .
                    " meses, desde " . $range->start_date .
                    " hasta " . $range->end_date . "\n");
            }
            
            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setLineSpacing(2);
            $printer->setEmphasis(false);
            $printer->setFont(Printer::MODE_FONT_B);
            $printer->text("\n");

            // Impresión del código QR (imagen ampliada)
            if (isset($order->qr_image) && !empty($order->qr_image)) {
                $parts = explode(',', $order->qr_image);
                if (count($parts) === 2) {
                    $qr_image_data = base64_decode($parts[1]);
                    $tempPath = sys_get_temp_dir() . '/qr_temp.png';
                    file_put_contents($tempPath, $qr_image_data);
                    try {
                        // Se intenta imprimir la imagen QR; si se requiere mayor tamaño, se puede redimensionar previamente
                        $qrImage = EscposImage::load($tempPath, false);
                        $printer->bitImage($qrImage);
                    } catch (Exception $e) {
                        Log::error("QR Image error: " . $e->getMessage());
                    }
                }
            } elseif (isset($order->qr) && !empty($order->qr)) {
                // Se incrementa el tamaño del QR (factor 12)
                $printer->qrCode($order->qr, Printer::QR_ECLEVEL_L, 12);
            }

            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setLineSpacing(2);
            $printer->setEmphasis(false);
            $printer->setFont(Printer::MODE_FONT_B);
            $printer->text("Escanea este código para acceder a tu factura ");
            $printer->text("\n");
            $printer->text("\n");
            $printer->text("Tecnoplus");
            $printer->text("\nwww.tecnoplus.online\n");
            $printer->cut();
            $printer->pulse();
            $printer->close();

            return redirect()->back()->with("mensaje", "Ticket impreso");
        } catch (\Throwable $th) {
            Log::error("Error in printTicketRecently: " . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            throw new Exception("Error, no se pudo completar el proceso: " . $th->getMessage(), 400);
        }
    }

    // Funciones para obtener los nombres de Forma y Método de Pago
    protected function getPaymentFormName($paymentFormId)
    {
        // Ejemplo: 1 => Contado, 2 => Crédito.
        $mapping = [
            1 => "Contado",
            2 => "Crédito",
        ];
        return isset($mapping[$paymentFormId]) ? $mapping[$paymentFormId] : "Desconocido";
    }

    protected function getPaymentMethodName($paymentMethodId)
    {
        // Mapeo según la tabla payment_methods
        $mapping = [
            1  => "Efectivo",
            2  => "Consignación",
            3  => "Cheque",
            4  => "Transferencia",
            5  => "Bonos",
            6  => "Vales",
            7  => "Medio de pago no definido",
            8  => "Tarjeta Débito",
            9  => "Tarjeta Crédito",
            10 => "Otro*",
        ];
        return isset($mapping[$paymentMethodId]) ? $mapping[$paymentMethodId] : "Desconocido";
    }

    /**
     * Imprime una comanda de eliminación para el producto removido.
     *
     * @param Order $order La orden a la que pertenece el producto eliminado.
     * @param object $detail Objeto con los datos del detalle eliminado. Se espera que tenga:
     *                       product_id, quantity, barcode, observaciones (opcional).
     * @return void
     */
    public function printRemovalTicket(Order $order, $detail)
    {
        // Se obtiene el producto eliminado
        $product = \App\Models\Product::find($detail->product_id);
        if (!$product) {
            Log::error("Producto no encontrado para eliminación en la orden {$order->id}");
            return;
        }

        // Se asume que el producto tiene la relación "zones" para determinar a qué impresoras enviar la comanda.
        foreach ($product->zones as $zone) {
            $pos_printer = $zone->printer;
            if (!$pos_printer || $pos_printer == '') {
                Log::warning("No hay impresora configurada para la zona: " . $zone->zone);
                continue;
            }
            try {
                $connector = new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector($pos_printer);
                $printer = new \Mike42\Escpos\Printer($connector);
                $printer->initialize();

                // Encabezado con fuente grande y énfasis para "ELIMINACIÓN"
                $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
                $printer->setTextSize(3, 3);
                $printer->setEmphasis(true);
                $printer->text("ELIMINACIÓN\n");
                $printer->setEmphasis(false);
                $printer->setTextSize(1, 2);
                $printer->text("-----------------------------------------\n");

                // Datos adicionales: Mesero, Zona y Fecha
                $system_user = $order->user()->first();
                $waiter = $system_user ? $system_user->name : "Sin Mesero";
                $date = date('Y-m-d h:i:s A');
                $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
                $printer->text("Mesero: " . $waiter . "\n");
                $printer->text("Zona: " . $zone->zone . "\n");
                $printer->text("Fecha: " . $date . "\n");
                $printer->text("-----------------------------------------\n");

                // Datos del producto removido (cantidad en negativo)
                $printer->text("Producto: " . $product->product . "\n");
                // Se usa abs() para asegurarse que se imprima el valor absoluto y se antepone el signo negativo
                $printer->text("Cantidad: -" . abs($detail->quantity) . "\n");
                if (!empty($detail->observaciones)) {
                    $printer->text("Obs: " . $detail->observaciones . "\n");
                }
                $printer->text("Pedido N°: " . ($order->bill_number ?? $order->id) . "\n");
                $printer->text("-----------------------------------------\n");

                // Pie de impresión (opcional)
                $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
                $printer->text("ELIMINACIÓN REALIZADA\n");
                $printer->cut();
                $printer->pulse();
                $printer->close();
            } catch (\Throwable $e) {
                Log::error("Error al imprimir comanda de eliminación para zona {$zone->zone}: " . $e->getMessage());
            }
        }
    }

/**
 * Imprime la comanda (ticket recientemente) para órdenes de tipo pedido.
 *
 * @param int   $order_id    ID de la orden.
 * @param array $listProducts Lista de productos con cantidades y observaciones modificadas (solo en edición).
 * @param bool  $reprint     Si es reimpresión, marca con “** REIMPRESION **”.
 */
public function printTicketRecently($order_id, $listProducts = null, $reprint = false)
{
    $order = Order::find($order_id);
    $system_user = $order->user()->first();
    $configuration = new Configuration();
    $company = $configuration->select()->first();

    $productosPorZona = [];

    // Agrupamos productos por zona y asignamos cantidad, precio y observaciones
    foreach ($order->products as $product) {
        foreach ($product->zones as $zone) {
            if ($listProducts) {
                // En edición, tomamos datos de $listProducts
                $matched = null;
                foreach ($listProducts as $newProduct) {
                    if ($product->id == $newProduct['product_id']) {
                        $matched = $newProduct;
                        break;
                    }
                }
                if ($matched) {
                    $product->quantity            = $matched['quantity'];
                    $product->price_tax_inc_total = $matched['price_tax_inc_total'];
                    $product->observaciones       = $matched['observaciones'] ?? null;
                } else {
                    // Si no está en la lista modificada, no lo imprimimos
                    $product->quantity = 0;
                }
            } else {
                // En creación, tomamos datos de la base
                $detail = $order->detailOrders()->where('product_id', $product->id)->first();
                $product->quantity            = $detail->quantity;
                $product->price_tax_inc_total = $detail->price_tax_inc_total;
                $product->observaciones       = $detail->observaciones ?? null;
            }

            if ($product->quantity) {
                $productosPorZona[$zone->id]['zone']       = $zone;
                $productosPorZona[$zone->id]['productos'][] = $product;
            }
        }
    }

    // Para cada zona, enviamos la impresión
    foreach ($productosPorZona as $zonaData) {
        $zone      = $zonaData['zone'];
        $pos_printer = $zone->printer;
        if (! isset($zonaData['productos']) || ! $pos_printer) {
            continue;
        }

        try {
            $connector = new WindowsPrintConnector($pos_printer);
            $printer   = new Printer($connector);

            // Cabecera
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            if ($reprint) {
                $printer->text("** REIMPRESION **\n");
            }
            $printer->text($zone->zone . "\n");
            $printer->feed(1);

            // Mesero y fecha
            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text("Mesero(a): " . $system_user->name . "\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);
            $printer->text("Fecha: " . date('Y-m-d h:i:s A') . "\n");
            if ($order->bill_number) {
                $printer->setTextSize(2, 2);
                $printer->text($order->bill_number . "\n");
            }
            if ($order->table) {
                $printer->text("Mesa: " . $order->table->table . "\n");
            }

            // Encabezado de lista
            $printer->setTextSize(1, 2);
            $printer->text("\n================================================\n");
            $printer->feed(1);
            $printer->setEmphasis(true);
            $printer->text(sprintf('%-20s %16s %9s', 'ARTICULO', 'CANT', 'VALOR'));
            $printer->feed(1);
            $printer->text("\n================================================\n");
            $printer->feed(1);
            $printer->setEmphasis(false);

            // Imprimir cada producto y sus observaciones
            foreach ($zonaData['productos'] as $producto) {
                $maxWidthText = 25;
                $cantidad     = intval($producto->quantity);
                $valorTotal   = $producto->price_tax_inc_total;
                $wrappedText  = wordwrap($producto->product, $maxWidthText);
                $textLines    = explode("\n", $wrappedText);

                // Nombre, cantidad y valor
                foreach ($textLines as $i => $line) {
                    $quantityDisplay = $i === 0 ? sprintf("%12s", $cantidad) : str_repeat(' ', 5);
                    $valueDisplay    = $i === 0 ? sprintf("%9.2f", $valorTotal) : str_repeat(' ', 9);
                    $printer->text(sprintf("%-{$maxWidthText}s %s %s\n", $line, $quantityDisplay, $valueDisplay));
                }

                // Observaciones, si existen
                if (! empty($producto->observaciones)) {
                    $wrappedObs = wordwrap($producto->observaciones, $maxWidthText);
                    $obsLines   = explode("\n", $wrappedObs);
                    foreach ($obsLines as $obsLine) {
                        $printer->text(sprintf("  -> %-{$maxWidthText}s\n", $obsLine));
                    }
                }

                $printer->text("\n------------------------------------------------\n");
                $printer->feed(1);
            }

            // Pie e impresión
            $printer->feed(2);
            $printer->cut();
            $printer->pulse();
            $printer->close();
        } catch (\Throwable $e) {
            Log::error("Error al imprimir en la impresora de la zona {$zone->zone}: " . $e->getMessage());
        }
    }

    return redirect()->back()->with("mensaje", "Ticket impreso");
}


    public function printPreCuenta($order_id)
    {
        // Buscar la orden y obtener sus detalles
        $order = Order::find($order_id);
        if (!$order) {
            throw new Exception("Orden no encontrada", 404);
        }
        $order_details = $order->detailOrders()->get();
        $system_user = $order->user()->first();
        $table = $order->table ? $order->table->table : "Sin mesa";

        // Configuración e impresora
        $configuration = new Configuration();
        $company = $configuration->select()->first();
        $box = $order->box()->first();
        $POS_printer = $box ? $box->printer : $company->printer;

        if (!$POS_printer || $POS_printer == '') {
            throw new Exception("Error, no hay impresoras configuradas", 400);
        }

        $connector = new WindowsPrintConnector($POS_printer);

        try {
            $printer = new Printer($connector);
            $printer->initialize();

            // 1. Título grande: PRECUENTA
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2); // Fuente aumentada para el título
            $printer->setEmphasis(true);
            $printer->text("PRECUENTA\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);
            $printer->feed();

            // 2. Encabezado: Cajero y Mesa
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Cajero: " . $system_user->name . "\n");
            $printer->text("Mesa: " . $table . "\n");
            $printer->feed();

            // 3. Detalle de la orden (productos)
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\n-----------------------------------------\n\n");
            $printer->setLineSpacing(1);
            $printer->setEmphasis(true);
            // Encabezado de la lista
            $printer->text(sprintf('%-22s %8s %10s', 'ARTICULO', 'CANT', 'PRECIO'));
            $printer->text("\n-----------------------------------------\n\n");
            $printer->setEmphasis(false);
            $printer->text("\n");

            $total = 0;
            foreach ($order_details as $item) {
                // Se limita el ancho del nombre del producto y se formatea la línea
                $line = sprintf(
                    '%-22s %8.2f %10.2f',
                    mb_strimwidth($item->product, 0, 21, ''),
                    $item->quantity,
                    $item->price_tax_inc_total
                );
                $total += $item->price_tax_inc_total;
                $printer->text($line . "\n");
            }
            $printer->text("\n==========================================\n");
            $printer->setEmphasis(true);
            $printer->setLineSpacing(2);
            $printer->text(sprintf('%-25s %+15.15s', 'TOTAL', number_format($total, 2, '.', ',')));
            $printer->setEmphasis(false);
            $printer->setLineSpacing(1);
            $printer->text("\n");

            // 4. Nota al final
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\n-----------------------------------------\n");
            $printer->text("Este documento no es válido como comprobante de pago.\n");
            $printer->text("Solo es informativo del valor de su cuenta.\n");
            $printer->text("-----------------------------------------\n");
            $printer->feed();

            // Cortar y enviar pulso a la impresora
            $printer->cut();
            $printer->close();

            return response()->json([
                'status' => 'success',
                'message' => 'Precuenta impresa correctamente'
            ]);
        } catch (\Throwable $th) {
            Log::error("Error in printPreCuenta: " . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            throw new Exception("Error, no se pudo completar el proceso: " . $th->getMessage(), 400);
        }
    }

    /**
     * Imprime el ticket de pago.
     */
    public function printPaymentTicket(Order $order, Request $request)
    {
        $payment_id = $request->payment_id;
        $order = Order::find($order->id);
        $paymentCredits = $order->paymentCredits;
        $system_user = $order->user()->first();
        $box = $order->box()->first();
        $configuration = new Configuration();
        $company = $configuration->select()->first();
        $POS_printer = $box ? $box->printer : $company->printer;

        if (!$POS_printer || $POS_printer == '') {
            throw new Exception("Error, no hay impresoras configuradas", 400);
        }

        $connector = new WindowsPrintConnector($POS_printer);

        try {
            $printer = new Printer($connector);
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            try {
                $logo = EscposImage::load($company->logo, false);
                $printer->bitImage($logo);
            } catch (Exception $e) {
                Log::error("Logo error in printPaymentTicket: " . $e->getMessage());
            }
            $printer->setTextSize(1, 2);
            $printer->setEmphasis(true);
            $printer->text($company->name . "\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text("NIT: " . $company->nit . "\n");
            $printer->text("Dirección: " . $company->address . "\n");

            $printer->setEmphasis(true);
            $printer->text("Cajero(a): " . $system_user->name . "\n");
            $printer->setEmphasis(false);
            $printer->text("Fecha: " . date('Y-m-d h:i:s A') . "\n");
            if ($order->table) {
                $printer->text("Mesa: " . $order->table->table . "\n");
            }
            $printer->text("N° Factura: ");
            if (isset($order->bill_number)) {
                $printer->text($order->bill_number . "\n");
            } else {
                $printer->text($order->id . "\n");
            }
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            switch ($order->state) {
                case '1':
                    $printer->text("Factura suspendida");
                    break;
                case '2':
                    $printer->text("Facturada");
                    break;
                case '3':
                    $printer->text("Cotizacion");
                    break;
                case '5':
                    $printer->text("Credito");
                    break;
                default:
                    $printer->text("Factura");
                    break;
            }

            if ($order->client->type_document == 1 || $order->client->type_document == 'CC') {
                $type_document = 'CC';
            } elseif ($order->client->type_document == 2 || $order->client->type_document == 'TI') {
                $type_document = 'TI';
            } elseif ($order->client->type_document == 3 || $order->client->type_document == 'NIT') {
                $type_document = 'NIT';
            } elseif ($order->client->type_document == 4 || $order->client->type_document == 'CE') {
                $type_document = 'CE';
            } else {
                $type_document = '';
            }

            $printer->text("\n-----------------------------------------\n");
            $printer->text(sprintf('%-16s %-25s', 'Cliente:', $order->client->name . ' ' . $order->client->last_name) . "\n");
            if ($order->client->type_person) {
                $printer->text(sprintf('%-16s %-25s', 'Tipo de persona:', $order->client->type_person) . "\n");
            }
            if ($order->client->document) {
                $printer->text(sprintf('%-16s %-25s', 'Documento:', $type_document . '. ' . $order->client->document) . "\n");
            }
            if ($order->client->address) {
                $printer->text(sprintf('%-16s %-25s', 'Dirección:', $order->client->address) . "\n");
            }
            if ($order->client->contact) {
                $printer->text(sprintf('%-16s %-25s', 'Contacto:', $order->client->contact) . "\n");
            }
            if ($order->observations) {
                $printer->text(sprintf('%-16s %-25s', 'Observaciones:', $order->observations) . "\n");
            }

            $printer->text("\n");
            $printer->setLineSpacing(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("ABONOS");
            $printer->text("\n-----------------------------------------\n\n");
            $printer->setLineSpacing(1);
            $printer->setEmphasis(true);
            $printer->text(sprintf('%-22s %+18.7s', 'VALOR', 'FECHA'));
            $printer->text("\n-----------------------------------------\n\n");
            $printer->setEmphasis(false);
            $printer->text("\n");

            foreach ($paymentCredits as $df) {
                if (!$payment_id) {
                    $line = sprintf('%-22s %18.2f ', mb_strimwidth($df->pay, 0, 21, ''), mb_strimwidth($df->created_at, 0, 17, ''));
                    $printer->text($line . "\n");
                } elseif ($payment_id && $payment_id == $df->id) {
                    $line = sprintf('%-22s %18.2f ', mb_strimwidth($df->pay, 0, 21, ''), mb_strimwidth($df->created_at, 0, 17, ''));
                    $printer->text($line . "\n");
                }
            }

            $printer->text("\n==========================================\n");
            $printer->setEmphasis(true);
            $printer->setLineSpacing(2);
            $printer->text("\n");
            $printer->text(sprintf('%-25s %+15.15s', 'Subtotal', number_format($order->total_iva_exc, 2)));
            $printer->text("\n");
            $printer->text(sprintf('%-25s %+15.15s', 'Iva', number_format($order->total_iva_inc - $order->total_iva_exc, 2, '.', ',')));
            $printer->text("\n");
            $printer->setTextSize(1, 2);
            $printer->text(sprintf('%-25s %+15.15s', 'TOTAL', number_format($order->total_iva_inc, 2, '.', ',')));
            $printer->text("\n");
            $printer->text(sprintf('%-25s %+15.15s', 'Abono', number_format($order->paid_payment, 2, '.', ',')));
            $printer->text("\n");
            $printer->text(sprintf('%-25s %+15.15s', 'Saldo', number_format($order->total_iva_inc - $order->paid_payment, 2, '.', ',')));
            $printer->text("\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
            $printer->text("\n");

            if (isset($order->bill_number) && method_exists($order, 'consecutiveBox')) {
                $consecutiveBox = $order->consecutiveBox();
                if ($consecutiveBox) {
                    $from_date = Carbon::createFromFormat('Y-m-d', $consecutiveBox->start_date);
                    $until_date = Carbon::createFromFormat('Y-m-d', $consecutiveBox->end_date);
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("VENCE: " . $until_date->toDateString() . " MESES VIG. :  " . ($until_date->month - $from_date->month) . "\n");
                    $printer->text("PREFIJO: " . $order->box->prefix);
                    $printer->text(" del No. " . $consecutiveBox->from . " AL " . $consecutiveBox->to . " AUTORIZA\n");
                }
            }
            $printer->text("\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setLineSpacing(2);
            $printer->setEmphasis(false);
            $printer->setFont(Printer::MODE_FONT_B);
            $printer->text($company->condition_order . "\n");
            $printer->text("Tecnoplus");
            $printer->text("\nwww.tecnoplus.com\n");
            $printer->cut();
            $printer->pulse();
            $printer->close();

            return redirect()->back()->with("mensaje", "Ticket impreso");
        } catch (\Throwable $th) {
            Log::error("Error in printPaymentTicket: " . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            throw new Exception("Error, no se pudo completar el proceso: " . $th->getMessage(), 400);
        }
    }

    /**
     * Devuelve los créditos de un cliente.
     */
    public function creditByClient($clientId)
    {
        try {
            $orders = Order::where('client_id', $clientId)
                ->where('state', 5)
                ->get();

            return response()->json([
                'status' => 'success',
                'code' => 200,
                'orders' => $orders,
            ]);
        } catch (\Throwable $th) {
            Log::error("Error in creditByClient: " . $th->getMessage(), ['trace' => $th->getTraceAsString()]);
            throw new Exception("Error, no se pudo obtener los créditos: " . $th->getMessage(), 400);
        }
    }

    /**
     * Realiza abonos a órdenes de crédito.
     */
    public function payCreditByClient(Request $request)
    {
        $validate = \Validator::make($request->all(), [
            'id_client' => 'required|integer|exists:clients,id',
            'pay_payment' => 'required|numeric|min:1'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Validación de datos incorrecta',
                'errors' => $validate->errors(),
            ]);
        }
        $orders = \DB::table('orders as o')
            ->leftJoin('payment_credits as pc', 'pc.order_id', '=', 'o.id')
            ->select('o.id', 'o.total_paid', 'o.payment_methods', \DB::raw('SUM(pc.pay) as paid_payment'))
            ->where('o.client_id', $request->id_client)
            ->where('o.state', 5)
            ->groupByRaw('o.id, o.total_paid, o.payment_methods')
            ->orderBy('o.created_at')
            ->get();

        if ($request->pay_payment > $orders->sum('total_paid')) {
            return response()->json([
                'status' => 'error',
                'code' => 400,
                'message' => 'Validación de pago incorrecta',
            ]);
        }

        $user_id = \Auth::user()->id;

        foreach ($orders as $order) {
            if ($request->pay_payment > 0) {
                $payment_methods = (object)['pay_payment' => 0, 'cash' => 0];
                $order->payment_methods = $order->payment_methods ? json_decode($order->payment_methods) : $payment_methods;
                $pending = $order->total_paid - $order->paid_payment;

                if ($pending > $request->pay_payment) {
                    PaymentCredit::create([
                        'user_id' => $user_id,
                        'order_id' => $order->id,
                        'pay' => $request->pay_payment
                    ]);

                    Order::where('id', $order->id)->update([
                        'payment_methods->pay_payment' => $order->payment_methods->pay_payment + $request->pay_payment,
                        'payment_methods->cash' => $order->payment_methods->cash + $request->pay_payment
                    ]);
                    $request->pay_payment = 0;
                } else {
                    PaymentCredit::create([
                        'user_id' => $user_id,
                        'order_id' => $order->id,
                        'pay' => $pending
                    ]);
                    Order::where('id', $order->id)->update([
                        'state' => 2,
                        'payment_methods->pay_payment' => $order->payment_methods->pay_payment + $pending,
                        'payment_methods->cash' => $order->payment_methods->cash + $pending
                    ]);
                    $request->pay_payment = $request->pay_payment - $pending;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Abonos realizados correctamente',
        ]);
    }

    /**
     * Devuelve las órdenes para la cocina.
     */
    public function ordersForKitchen(Request $request)
    {
        $perPage = $request->nro_results ?? 15;

        $orders = Order::where('state', 1)
            ->where('proccess', false)
            ->with([
                'user:id,name',
                'table:id,table',
                'detailOrders'
            ])
            ->orderBy('created_at')
            ->paginate($perPage);

        $totalOrders = $this->getTotalOrders($request);

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'orders' => $orders,
            'totalOrders' => $totalOrders->orders
        ]);
    }

    /**
     * Marca la orden como preparada para la cocina.
     */
    public function prepareOrderKitchen(Order $order)
    {
        $order->proccess = true;
        $order->save();

        return [
            'order_information' => $order
        ];
    }

    /**
     * Reimpresión de una orden.
     */
    public function reprint($id)
    {
        $order = Order::findOrFail($id);

        try {
            $printOrder = new PrintOrderController();
            // Pasamos true como tercer parámetro para que aparezca "REIMPRESION"
            $printOrder->printTicketRecently($id, null, true);
        } catch (\Throwable $th) {
            Log::error('error_print_ticket', [
                'method' => __METHOD__,
                'class' => $th->getFile(),
                'line' => $th->getLine(),
                'message' => $th->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo reimprimir la orden',
                'error' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Orden reimpresa correctamente'
        ]);
    }

    /**
     * Genera el número de factura según el rango de numeración asociado a la caja.
     */
    public function generateBillNumber(Request $request)
    {
        // Obtenemos la última orden para la caja indicada (excepto las del estado 3)
        $latestOrder = Order::where('box_id', $request->box_id)
            ->where('state', '<>', '3')
            ->orderByDesc('id')
            ->first();
        $box = Box::find($request->box_id);
        $dateValidate = Carbon::now()->toDateString();

        // Obtenemos los rangos asociados a la caja usando la relación many-to-many definida en Box
        $numberingRanges = $box->numberingRanges();
        // Buscamos el rango asociado al numbering_range_id enviado
        $range = $numberingRanges->where('numbering_ranges.id', $request->numbering_range_id)->first();

        if ($request->state != 3) {
            // Si el rango es permanente (start_date y end_date son nulos), usamos el campo current
            if ($range && is_null($range->start_date) && is_null($range->end_date)) {
                \Log::info('Prefijo del rango: ' . $range->prefix);
                $bill_number = $range->prefix . $range->current;
                $range->current = $range->current + 1;
                $range->save();
                return $bill_number;
            }

            if ($latestOrder) {
                // Se remueve el prefijo del número actual usando el prefijo del rango
                $lastConsecutive = str_replace($range->prefix, '', $latestOrder->bill_number);
                $lastConsecutive = intval($lastConsecutive);

                // Buscamos el rango que contenga el número actual
                $consecutiveRange = $numberingRanges->where([
                        ['from', '<=', $lastConsecutive],
                        ['to', '>=', $lastConsecutive]
                    ])
                    ->where('end_date', '>=', $dateValidate)
                    ->orderBy('from')
                    ->first();

                if ($consecutiveRange && $consecutiveRange->to > $lastConsecutive) {
                    $bill_number = $consecutiveRange->prefix . ($lastConsecutive + 1);
                } else {
                    $continue = $numberingRanges
                        ->where('to', '>', $lastConsecutive)
                        ->where('start_date', '<=', $dateValidate)
                        ->where('end_date', '>=', $dateValidate)
                        ->where('numbering_ranges.id', '!=', isset($consecutiveRange->id) ? $consecutiveRange->id : null)
                        ->orderBy('from')
                        ->first();

                    if (!$continue) {
                        return abort(500, 'No se encontró un rango de numeración válido.');
                    }

                    $bill_number = $continue->prefix . $continue->from;
                }
            } else {
                $continue = $numberingRanges
                    ->where('start_date', '<=', $dateValidate)
                    ->where('end_date', '>=', $dateValidate)
                    ->orderBy('from')
                    ->first();

                if (!$continue) {
                    return abort(500, 'No se encontró un rango de numeración para la fecha actual.');
                }
                $bill_number = $continue->prefix . $continue->from;
            }
        } else {
            $bill_number = strtoupper(Str::random(10));
        }

        return $bill_number;
    }

    /**
     * Genera un PDF de la orden.
     */
    public function generatePdf(Order $order)
    {
        $data = [
            'orderInformation' => $order,
            'orderDetails' => $order->detailOrders()->get(),
            'user' => $order->user()->first(),
            'configuration' => Configuration::first(),
            'url' => \URL::to('/'),
            'consecutiveBox' => method_exists($order, 'consecutiveBox') ? $order->consecutiveBox() : null
        ];

        if ($data['consecutiveBox']) {
            $from_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->start_date);
            $until_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->end_date);

            $data['consecutive_expires'] = "Vence: " . $until_date->toDateString() . " Meses Vig. :  " . ($until_date->month - $from_date->month);
        }

        $pdf = PDF::loadView('templates.order', $data);
        $pdf = $pdf->download('prueba.pdf');

        $data = [
            'status' => 200,
            'pdf' => base64_encode($pdf),
            'message' => 'Orden generada en pdf'
        ];

        return response()->json($data);
    }

    /**
     * Genera un PDF del pago de la orden.
     */
    public function generatePaymentPdf(Order $order, Request $request)
    {
        $payment_id = $request->payment_id;

        $data = [
            'creditInformation' => $order,
            'orderDetails' => $order->detailOrders()->get(),
            'user' => $order->user()->first(),
            'configuration' => Configuration::first(),
            'payment_id' => $payment_id,
            'url' => \URL::to('/'),
            'consecutiveBox' => method_exists($order, 'consecutiveBox') ? $order->consecutiveBox() : null
        ];

        if ($data['consecutiveBox']) {
            $from_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->start_date);
            $until_date = Carbon::createFromFormat('Y-m-d', $data['consecutiveBox']->end_date);

            $data['consecutive_expires'] = "Vence: " . $until_date->toDateString() . " Meses Vig. :  " . ($until_date->month - $from_date->month);
        }

        $pdf = PDF::loadView('templates.payments-credit', $data);
        $pdf = $pdf->download('prueba.pdf');

        $data = [
            'status' => 200,
            'pdf' => base64_encode($pdf),
            'message' => 'Orden generada en pdf'
        ];

        return response()->json($data);
    }
    
//reimprecion comandas 
/**
 * Imprime una comanda de actualización:
 * lista sólo los productos cuyas cantidades subieron o bajaron.
 */
public function printUpdateComanda(Order $order, array $changes)
{
    // reutilizamos la lógica de zonas de printTicketRecently…
    $system_user  = $order->user()->first();
    $configuration = Configuration::first();
    $company      = $configuration;
    $productosPorZona = [];

    // agrupamos productos modificados por zona
    foreach ($changes as $item) {
        $prod = $order->detailOrders()->where('product_id', $item['product_id'])->first();
        if (! $prod) continue;
        foreach ($prod->product->zones as $zone) {
            $productosPorZona[$zone->id]['zone']       = $zone;
            $productosPorZona[$zone->id]['productos'][] = [
                'name'     => $prod->product,
                'quantity' => $item['quantity'],
                'observaciones' => $prod->observaciones ?? '',
            ];
        }
    }

    foreach ($productosPorZona as $zonaData) {
        $zone = $zonaData['zone'];
        $connector = new WindowsPrintConnector($zone->printer);
        $printer   = new Printer($connector);

        // ** CABECERA **
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->text("** ACTUALIZACIÓN **\n");
        $printer->setEmphasis(false);

        // Mesero y mesa
        $printer->setTextSize(1, 1);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Mesero: " . $system_user->name . "\n");
        if ($order->table) {
            $printer->text("Mesa:   " . $order->table->table . "\n");
        }

        // Detalle
        $printer->feed(1);
        $printer->setEmphasis(true);
        $printer->text(sprintf("%-22s %8s\n", 'ARTÍCULO', 'CANT.'));
        $printer->setEmphasis(false);
        $printer->text("--------------------------------\n");

        foreach ($zonaData['productos'] as $p) {
            $printer->text(sprintf(
                "%-22s %8d\n",
                mb_strimwidth($p['name'], 0, 21, ''),
                $p['quantity']
            ));
            if (!empty($p['observaciones'])) {
                $printer->text("   obs: " . $p['observaciones'] . "\n");
            }
        }

        $printer->feed(2);
        $printer->cut();
        $printer->pulse();
        $printer->close();
    }
}


}
