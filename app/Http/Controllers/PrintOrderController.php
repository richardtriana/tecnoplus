<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Configuration;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class PrintOrderController extends Controller
{
	public function openBox($order_id)
	{

		// Orden
		$order = Order::find($order_id);
		$box = $order->box()->first();

		$configuration = new Configuration();
		$company =  $configuration->select()->first();

		$POS_printer = $box ? $box->printer : $company->printer;

		try {
			if (!$POS_printer || $POS_printer == '') {
				throw new Exception("Error, no hay impresoras configuradas", 400);
			}

			if ($POS_printer) {
				try {
					$connector = new WindowsPrintConnector($POS_printer);
					$printer = new Printer($connector);
					$printer->initialize();
					$printer->pulse();
					$printer->close();
				} catch (\Throwable $th) {
					return $th;
				}
			}
		} catch (\Throwable $th) {
			if (!$POS_printer || $POS_printer == '') {
				// throw new Exception("Error, no hay impresoras configuradas", 200);
				return $th;
			}
		}
	}

	public function printTicket($order_id, $cash = null, $change = null)
	{
		// Orden
		$order = Order::find($order_id);
		$order_details = $order->detailOrders()->get();
		$system_user = $order->user()->first();
		$payment_methods = (object) ($order->payment_methods);
		$box = $order->box()->first();

		// Información empresarial
		$configuration = new Configuration();
		$company =  $configuration->select()->first();
		$POS_printer = $box ? $box->printer : $company->printer;


		if (!$POS_printer || $POS_printer == '') {
			throw new Exception("Error, no hay impresoras configuradas", 400);
		}

		// Config de impresora
		$connector = new WindowsPrintConnector($POS_printer);

		try {

			$printer = new Printer($connector);
			$printer->initialize();
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			try {
				$logo = EscposImage::load($company->logo, false);
				$printer->bitImage($logo);
			} catch (Exception $e) {
				/* Images not supported on your PHP, or image file not found */
				//$printer->text($e->getMessage() . "\n");
			}

			$printer->setTextSize(1, 2);
			$printer->setEmphasis(true);
			$printer->text($company->name . "\n");
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setTextSize(1, 1);
			$printer->setEmphasis(false);
			$printer->text("NIT: ");
			$printer->text($company->nit . "\n");
			$printer->text("Dirección: ");
			$printer->text($company->address . "\n");

			$printer->setEmphasis(true);
			$printer->text("Cajero(a): ");
			$printer->text($system_user->name . "\n");
			$printer->setEmphasis(false);
			$printer->text("Fecha: ");
			$printer->text(date('Y-m-d h:i:s A') .  "\n");
			if ($order->table) {
				$printer->text("Mesa: ");
				$printer->text($order->table->table . "\n");
			}

			$printer->text("N° Factura: ");
			if (isset($order->bill_number)) {
				$printer->text($order->bill_number . "\n");        // 
			} else {
				$printer->text($order->id . "\n");        // 
			}
			if ($order->state) {
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
					case '1':
						$printer->text("Factura");
						break;
				}
			}

			if ($order->client->type_document = 1 || $order->client->type_document = 'CC') {
				$type_document = 'CC';
			}
			if ($order->client->type_document = 2 || $order->client->type_document = 'TI') {
				$type_document = 'TI';
			}
			if ($order->client->type_document = 3 || $order->client->type_document = 'NIT') {
				$type_document = 'NIT';
			}
			if ($order->client->type_document = 4 || $order->client->type_document = 'CE') {
				$type_document = 'CE';
			}

			$printer->text("\n-----------------------------------------" . "\n");
			$printer->text(sprintf('%-16s %-25s', 'Cliente:', $order->client->name . $order->client->last_name) . "\n");
			if ($order->client->type_person) {
				$printer->text(sprintf('%-16s %-25s', 'Tipo de persona:', $order->client->type_person) . "\n");
			}
			if ($order->client->document) {
				$printer->text(sprintf('%-16s %-25s', 'Documento:', $type_document . '. '  . $order->client->document) . "\n");
			}
			if ($order->client->address) {
				$printer->text(sprintf('%-16s %-25s', 'Dirección:',  $order->client->address) . "\n");
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
			$printer->text("\n-----------------------------------------" . "\n\n");
			$printer->setLineSpacing(1);
			$printer->setEmphasis(true);
			$printer->text(sprintf('%-22s %+8s %+10.7s', 'ARTICULO', 'CANT', 'PRECIO'));
			$printer->text("\n-----------------------------------------" . "\n\n");
			$printer->setEmphasis(false);
			$printer->text("\n");
			$total = 0;
			foreach ($order_details as $df) {
				$line = sprintf('%-22s %8.2f %10.2f ', '-' . mb_strimwidth($df->product, 0, 21, ''), $df->quantity, $df->price_tax_inc_total);
				$total +=  $df->price_tax_inc_total;
				$printer->text($line);
				$printer->text("\n");
			}
			$printer->text("\n==========================================" . "\n");
			$printer->setEmphasis(true);
			$printer->setLineSpacing(2);
			$printer->text("\n");
			$printer->text(sprintf('%-25s %+15.15s', 'Subtotal', number_format($order->total_iva_exc, 2), '.', ','));
			$printer->text("\n");
			$printer->text(sprintf('%-25s %+15.15s', 'Iva', number_format($order->total_iva_inc - $order->total_iva_exc, 2, '.', ',')));
			$printer->text("\n");
			$printer->setTextSize(1, 2);
			$printer->text(sprintf('%-25s %+15.15s', 'TOTAL', number_format($total, 2, '.', ',')));
			$printer->text("\n");
			$printer->setTextSize(1, 1);
			$printer->setEmphasis(false);

			isset($payment_methods->cash)  &&  $payment_methods->cash  > 0 ?		$printer->text(sprintf('%-25s %+15.15s', 'Efectivo', number_format($payment_methods->cash, 2, '.', ',') . "\n")) : '';
			isset($payment_methods->card)  &&  $payment_methods->card  > 0 ?		$printer->text(sprintf('%-25s %+15.15s', 'Tarjeta', number_format($payment_methods->card, 2, '.', ',') . "\n")) : '';
			isset($payment_methods->nequi)  && $payment_methods->nequi  > 0 ?		$printer->text(sprintf('%-25s %+15.15s', 'Nequi', number_format($payment_methods->nequi, 2, '.', ',') . "\n")) : '';
			isset($payment_methods->others)  && $payment_methods->others > 0 ?		$printer->text(sprintf('%-25s %+15.15s', 'Otros', number_format($payment_methods->others, 2, '.', ',') . "\n")) : '';
			$printer->setEmphasis(true);
			if (isset($payment_methods->change) && ($payment_methods->change >= 0)) {
				$printer->text(sprintf('%-25s %+15.15s', 'Cambio', number_format($payment_methods->change, 2, '.', ',') . "\n"));
			}
			$printer->setEmphasis(false);

			if ($change != null && $change >= 0) {
				$printer->text("\n");
				$change > 0 ?	$printer->text(sprintf('%-25s %+15.15s', 'Cambio', number_format($change, 2, '.', ','))) : $printer->text(sprintf('%-25s %+15.15s', 'Cambio', number_format(0, 2, '.', ',')));
			}
			$printer->text("\n");

			if (isset($order->bill_number)) {

				$consecutiveBox = $order->consecutiveBox();
				if ($consecutiveBox) {
					$from_date = Carbon::createFromFormat('Y-m-d', $consecutiveBox->from_date);
					$until_date = Carbon::createFromFormat('Y-m-d', $consecutiveBox->until_date);

					$printer->setJustification(Printer::JUSTIFY_CENTER);
					$printer->text("VENCE: " . $until_date->toDateString() . " MESES VIG. :  " . ($until_date->month - $from_date->month) . "\n");
					$printer->text("PREFIJO: " . $order->box->prefix);

					$printer->text(" del No. " . $consecutiveBox->from_nro . " AL " . $consecutiveBox->until_nro . " AUTORIZA\n");
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
			throw new Exception("Error, no se pudo completar el proceso", 400);
			return [
				'code' => $th->getCode()
			];
		}
	}

// imprecion y reimprecion de comanda 
public function printTicketRecently($order_id, $listProducts = null, $reprint = false)
{
    // Orden comanda
    $order = Order::find($order_id);
    $system_user = $order->user()->first();

    // Información empresarial
    $configuration = new Configuration();
    $company = $configuration->select()->first();

    $productosPorZona = [];

    foreach ($order->products as $product) {
        foreach ($product->zones as $zone) {
            // Validar y reemplazar cantidad si viene $listProducts
            if ($listProducts) {
                foreach ($listProducts as $newProduct) {
                    if ($product->id == $newProduct['product_id']) {
                        $product->quantity = $newProduct['quantity'];
                        break;
                    }
                }
            } else {
                // Obtener la cantidad y el precio desde detail_orders
                $p = $order->detailOrders()->where('product_id', $product->id)->first();
                $product->quantity = $p->quantity;
                $product->price_tax_inc_total = $p->price_tax_inc_total;
                
                // Observaciones por producto:
                // Asignamos a $product->observaciones lo que venga en detail_orders
                $product->observaciones = $p->observaciones ?? null;
            }

            // Solo agregar productos con cantidad válida
            if ($product->quantity) {
                $productosPorZona[$zone->id]['zone'] = $zone;
                $productosPorZona[$zone->id]['productos'][] = $product;
            }
        }
    }

    // Recorrer zonas y productos para imprimir
    foreach ($productosPorZona as $zonaData) {
        $zone = $zonaData['zone'];
        $pos_printer = $zone->printer;

        if (isset($zonaData['productos'])) {
            $productos = $zonaData['productos'];

            try {
                // Configurar la conexión con la impresora
                $connector = new WindowsPrintConnector($pos_printer);
                $printer = new Printer($connector);

                // Imprimir encabezado
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->setTextSize(3, 3);

                // Aviso si es reimpresión
                if ($reprint) {
                    $printer->text("** REIMPRESION **\n");
                }

                // Nombre de la zona
                $printer->text($zone->zone . "\n");
                $printer->feed(1);

                // Datos del mesero
                $printer->setTextSize(2, 2);
                $printer->setEmphasis(true);
                $printer->text("Mesero(a): ");
                $printer->text($system_user->name . "\n");
                $printer->setEmphasis(false);

                // Fecha
                $printer->setTextSize(1, 1);
                $printer->text("Fecha: ");
                $printer->text(date('Y-m-d h:i:s A') . "\n");

                // Factura
                $printer->setTextSize(2, 2);
                if (isset($order->bill_number)) {
                    $printer->text($order->bill_number . "\n");
                } else {
                    $printer->text($order->id . "\n");
                }
                // Mesa (si existe)
                if ($order->table) {
                    $printer->text("Mesa: ");
                    $printer->text($order->table->table . "\n");
                }

                // Encabezados de la tabla de productos
                $printer->setTextSize(1, 2);
                $printer->text("\n");
                $printer->setLineSpacing(2);
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("\n================================================");
                $printer->feed(1);
                $printer->setLineSpacing(1);
                $printer->setEmphasis(true);
                $printer->text(sprintf('%-20s %16s %9s', 'ARTICULO', 'CANT', 'VALOR'));
                $printer->feed(1);
                $printer->text("\n================================================");
                $printer->feed(1);
                $printer->setEmphasis(false);
                $printer->text("\n");

                // Listar productos
                $printer->setTextSize(1, 2);
                foreach ($productos as $producto) {
                    $maxWidthText = 25;

                    // Cantidad sin decimales
                    $cantidad = intval($producto->quantity);

                    // Valor total
                    $valorTotal = isset($producto->price_tax_inc_total) ? $producto->price_tax_inc_total : 0;

                    // Ajustar texto del nombre del producto al ancho
                    $wrappedText = wordwrap($producto->product, $maxWidthText);
                    $textLines = explode("\n", $wrappedText);

                    // Primera línea con cantidad y valor,
                    // subsiguientes líneas dejan espacios en cantidad y valor
                    foreach ($textLines as $i => $line) {
                        $quantityDisplay = $i === 0 ? sprintf("%12s", $cantidad) : str_repeat(' ', 5);
                        $valueDisplay    = $i === 0 ? sprintf("%9.2f", $valorTotal) : str_repeat(' ', 9);

                        $printer->text(sprintf(
                            "%-{$maxWidthText}s %s %s\n",
                            $line,
                            $quantityDisplay,
                            $valueDisplay
                        ));
                    }

                    // Observaciones por producto
                    if (!empty($producto->observaciones)) {
                        // Impresión de observaciones en una línea aparte
                        $wrappedObs = wordwrap($producto->observaciones, $maxWidthText);
                        $obsLines = explode("\n", $wrappedObs);

                        foreach ($obsLines as $obsLine) {
                            // Se deja espacio donde iría cantidad y valor
                            $printer->text(sprintf("  -> %-{$maxWidthText}s\n", $obsLine));
                        }
                    }

                    $printer->text("\n------------------------------------------------");
                    $printer->feed(1);
                }

                // Observaciones generales de la Orden (si existen)
                if (!empty($order->observations)) {
                    $printer->feed(1);
                    $printer->setEmphasis(true);
                    $printer->text("Observaciones: \n");
                    $printer->setEmphasis(false);
                    $printer->text($order->observations . "\n");
                }

                $printer->feed(2);

                // Cortar papel y cerrar conexión
                $printer->cut();
                $printer->pulse();
                $printer->close();
            } catch (Exception $e) {
                Log::error("Error al imprimir en la impresora de la zona {$zone->zone}: {$e->getMessage()}");
            }
        }
    }
}


	public function printPaymentTicket(Order $order, Request $request)
	{
		$payment_id = $request->payment_id;
		// Orden
		$order = Order::find($order->id);
		$paymentCredits = $order->paymentCredits;
		$system_user = $order->user()->first();
		$box = $order->box()->first();

		// Información empresarial
		$configuration = new Configuration();
		$company =  $configuration->select()->first();
		$POS_printer = $box ? $box->printer : $company->printer;

		if (!$POS_printer || $POS_printer == '') {
			throw new Exception("Error, no hay impresoras configuradas", 400);
		}

		// Config de impresora
		$connector = new WindowsPrintConnector($POS_printer);

		try {

			$printer = new Printer($connector);
			$printer->initialize();
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			try {
				$logo = EscposImage::load($company->logo, false);
				$printer->bitImage($logo);
			} catch (Exception $e) {
				/* Images not supported on your PHP, or image file not found */
				//$printer->text($e->getMessage() . "\n");
			}

			$printer->setTextSize(1, 2);
			$printer->setEmphasis(true);
			$printer->text($company->name . "\n");
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setTextSize(1, 1);
			$printer->setEmphasis(false);
			$printer->text("NIT: ");
			$printer->text($company->nit . "\n");
			$printer->text("Dirección: ");
			$printer->text($company->address . "\n");

			$printer->setEmphasis(true);
			$printer->text("Cajero(a): ");
			$printer->text($system_user->name . "\n");
			$printer->setEmphasis(false);
			$printer->text("Fecha: ");
			$printer->text(date('Y-m-d h:i:s A') .  "\n");
			if ($order->table) {
				$printer->text("Mesa: ");
				$printer->text($order->table->table . "\n");
			}
			$printer->text("N° Factura: ");

			if (isset($order->bill_number)) {
				$printer->text($order->bill_number . "\n");        // 
			} else {
				$printer->text($order->id . "\n");        // 
			}
			if ($order->state) {
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
					case '1':
						$printer->text("Factura");
						break;
				}
			}

			if ($order->client->type_document = 1 || $order->client->type_document = 'CC') {
				$type_document = 'CC';
			}
			if ($order->client->type_document = 2 || $order->client->type_document = 'TI') {
				$type_document = 'TI';
			}
			if ($order->client->type_document = 3 || $order->client->type_document = 'NIT') {
				$type_document = 'NIT';
			}
			if ($order->client->type_document = 4 || $order->client->type_document = 'CE') {
				$type_document = 'CE';
			}

			$printer->text("\n-----------------------------------------" . "\n");
			$printer->text(sprintf('%-16s %-25s', 'Cliente:', $order->client->name . $order->client->last_name) . "\n");
			if ($order->client->type_person) {
				$printer->text(sprintf('%-16s %-25s', 'Tipo de persona:', $order->client->type_person) . "\n");
			}
			if ($order->client->document) {
				$printer->text(sprintf('%-16s %-25s', 'Documento:', $type_document . '. '  . $order->client->document) . "\n");
			}
			if ($order->client->address) {
				$printer->text(sprintf('%-16s %-25s', 'Dirección:',  $order->client->address) . "\n");
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
			$printer->text("\n-----------------------------------------" . "\n\n");
			$printer->setLineSpacing(1);
			$printer->setEmphasis(true);
			$printer->text(sprintf('%-22s %+18.7s', 'VALOR', 'FECHA'));
			$printer->text("\n-----------------------------------------" . "\n\n");
			$printer->setEmphasis(false);
			$printer->text("\n");

			foreach ($paymentCredits as $df) {
				if (!$payment_id) {
					$line = sprintf('%-22s %18.2f ',  mb_strimwidth($df->pay, 0, 21, ''), mb_strimwidth($df->created_at, 0, 17, ''));
					$printer->text($line);
					$printer->text("\n");
				} elseif ($payment_id && $payment_id == $df->id) {
					$line = sprintf('%-22s %18.2f ',  mb_strimwidth($df->pay, 0, 21, ''), mb_strimwidth($df->created_at, 0, 17, ''));
					$printer->text($line);
					$printer->text("\n");
				}
			}

			$printer->text("\n==========================================" . "\n");
			$printer->setEmphasis(true);
			$printer->setLineSpacing(2);
			$printer->text("\n");
			$printer->text(sprintf('%-25s %+15.15s', 'Subtotal', number_format($order->total_iva_exc, 2), '.', ','));
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

			if (isset($order->bill_number)) {
				$consecutiveBox = $order->consecutiveBox();
				if ($consecutiveBox) {
					$from_date = Carbon::createFromFormat('Y-m-d', $consecutiveBox->from_date);
					$until_date = Carbon::createFromFormat('Y-m-d', $consecutiveBox->until_date);

					$printer->setJustification(Printer::JUSTIFY_CENTER);
					$printer->text("VENCE: " . $until_date->toDateString() . " MESES VIG. :  " . ($until_date->month - $from_date->month) . "\n");
					$printer->text("PREFIJO: " . $order->box->prefix);

					$printer->text(" del No. " . $consecutiveBox->from_nro . " AL " . $consecutiveBox->until_nro . " AUTORIZA\n");
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
			throw new Exception("Error, no se pudo completar el proceso", 400);
			return [
				'code' => $th->getCode()
			];
		}
	}

	public function printTicketBilling($billing_id, $cash = null, $change = null)
	{
		// Orden
		$billing = Billing::find($billing_id);
		$billing_details = $billing->detailBillings()->get();
		$system_user = $billing->user()->first();

		// Información empresarial
		$configuration = new Configuration();
		$company =  $configuration->select()->first();

		if (!$company->printer || $company->printer == '') {
			throw new Exception("Error, no hay impresoras configuradas", 400);
		}

		// Config de impresora
		$connector = new WindowsPrintConnector($company->printer);

		try {

			$printer = new Printer($connector);
			$printer->initialize();
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			try {
				$logo = EscposImage::load($company->logo, false);
				$printer->bitImage($logo);
			} catch (Exception $e) {
				/* Images not supported on your PHP, or image file not found */
				//$printer->text($e->getMessage() . "\n");
				$logo = EscposImage::load('images/logo.jpeg', false);
			}

			$printer->setTextSize(1, 2);
			$printer->setEmphasis(true);
			$printer->text($company->name . "\n");
			$printer->setJustification(Printer::JUSTIFY_LEFT);
			$printer->setTextSize(1, 1);
			$printer->setEmphasis(false);
			$printer->text("NIT: ");
			$printer->text($company->nit . "\n");
			$printer->text("Dirección: ");
			$printer->text($company->address . "\n");

			$printer->setEmphasis(true);
			$printer->text("Cajero(a): " . $system_user->name . "\n");
			$printer->setEmphasis(false);
			$printer->text("Fecha: ");
			$printer->text(date('Y-m-d h:i:s A') .  "\n");
			$printer->text("N° compra: " . $billing->id . "\n");

			if ($billing->state) {
				$printer->setJustification(Printer::JUSTIFY_CENTER);

				switch ($billing->state) {
					case '1':
						$printer->text("Factura suspendida \n");
						break;
					case '2':
						$printer->text("Facturada \n");
						break;
					case '3':
						$printer->text("Cotizacion \n");
						break;
					case '5':
						$printer->text("Credito \n");
						break;
					default:
					case '1':
						$printer->text("Factura \n");
						break;
				}
				$printer->setJustification(Printer::JUSTIFY_LEFT);
			}

			$printer->text("\n-----------------------------------------" . "\n");
			$printer->text("Cliente: ");
			$printer->text($billing->supplier->name . "\n");
			if ($billing->supplier->type_person) {
				$printer->text('Tipo de persona: ' . $billing->supplier->type_type_person . "\n");
			}
			if ($billing->supplier->document) {
				$printer->text('Documento: ' . $billing->supplier->type_document . ' '  . $billing->supplier->document . "\n");
			}
			if ($billing->supplier->address) {
				$printer->text('Dirección: ' . $billing->supplier->address . "\n");
			}
			if ($billing->supplier->contact) {
				$printer->text('Contacto: ' . $billing->supplier->contact . "\n");
			}
			$printer->text("\n");
			$printer->setLineSpacing(2);
			$printer->setJustification(Printer::JUSTIFY_CENTER);
			$printer->text("\n-----------------------------------------" . "\n\n");
			$printer->setLineSpacing(1);
			$printer->setEmphasis(true);
			$printer->text(sprintf('%-22s %+8s %+10.7s', 'ARTICULO', 'CANT', 'PRECIO'));
			$printer->text("\n-----------------------------------------" . "\n\n");
			$printer->setEmphasis(false);
			$printer->text("\n");
			$total = 0;
			foreach ($billing_details as $df) {
				$line = sprintf('%-22s %8.2f %10.2f ', '-' . mb_strimwidth($df->product, 0, 21, ''), $df->quantity, $df->price_tax_inc_total);
				$total +=  $df->price_tax_inc_total;
				$printer->text($line);
				$printer->text("\n");
			}
			$printer->text("\n==========================================" . "\n");
			$printer->setEmphasis(true);
			$printer->setLineSpacing(2);
			$printer->text("\n");
			$printer->text(sprintf('%-25s %+15.15s', 'Subtotal', number_format($billing->total_iva_exc, 2), '.', ','));
			$printer->text("\n");
			$printer->text(sprintf('%-25s %+15.15s', 'Iva', number_format($billing->total_iva_inc - $billing->total_iva_exc, 2, '.', ',')));
			$printer->text("\n");
			$printer->setTextSize(1, 2);
			$printer->text(sprintf('%-25s %+15.15s', 'TOTAL', number_format($total, 2, '.', ',')));
			$printer->setTextSize(1, 1);
			$printer->setEmphasis(false);
			if ($cash != null && $change != null) {
				$printer->text("\n");
				$printer->text(sprintf('%-25s %+15.15s', 'Efectivo', number_format($cash, 2, '.', ',')));
				$printer->text("\n");
				$printer->text(sprintf('%-25s %+15.15s', 'Cambio', number_format($change, 2, '.', ',')));
			}
			$printer->text("\n");


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
			throw new Exception("Error, no se pudo completar el proceso", 400);
			return [
				'code' => $th->getCode()
			];
		}
	}
}
