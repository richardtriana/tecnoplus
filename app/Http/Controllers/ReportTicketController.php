<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Exception;
use Illuminate\Http\Request;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ReportTicketController extends Controller
{
    public function reportProductSales(Request $request)
{
    $listItems = $request->data;

    // Información empresarial
    $configuration = new Configuration();
    $company =  $configuration->select()->first();
    $POS_printer = $company->printer;

    if (!$POS_printer || $POS_printer == '') {
        throw new Exception("Error, no hay impresoras configuradas", 400);
    }

    // Configuración de la impresora
    $connector = new WindowsPrintConnector($POS_printer);
    $printer = new Printer($connector);
    $printer->initialize();
    $printer->setJustification(Printer::JUSTIFY_CENTER);

    // Imprimir el encabezado del reporte
    try {
        $logo = EscposImage::load($company->logo, false);
        $printer->bitImage($logo);
    } catch (Exception $e) {
        // Ignorar errores relacionados con imágenes
    }

    $printer->setTextSize(2, 2);
    $printer->setEmphasis(true);
    $printer->text("Reporte de productos\n");
    $printer->setEmphasis(false);
    $printer->setTextSize(1, 1);

    // Agrupar los productos por categoría
    $categories = [];
    foreach ($listItems as $item) {
        $categories[$item['category']][] = $item;
    }

    // Imprimir detalles por categoría
    $totalGeneral = 0;
    foreach ($categories as $categoryName => $products) {
        $printer->setEmphasis(true);
        $printer->text("\n$categoryName\n"); // Nombre de la categoría
        $printer->setEmphasis(false);
        $printer->text("----------------------------------------------\n");
        $totalCategory = 0;

        foreach ($products as $product) {
            $printer->text(sprintf(
                "%-28s %5s %10s\n",
                $product['product'], // Nombre del producto
                $product['quantity_of_products'], // Cantidad
                number_format($product['value'], 0, ',', '.') // Valor formateado
            ));
            $totalCategory += $product['value'];
        }

        $printer->text("----------------------------------------------\n");
        $printer->setEmphasis(true);
        $printer->text("Total $categoryName: " . number_format($totalCategory, 0, ',', '.') . "\n");
        $printer->setEmphasis(false);

        $totalGeneral += $totalCategory;
    }

    // Imprimir total general
    $printer->text("\n==============================================\n");
    $printer->setEmphasis(true);
    $printer->text("Total General: " . number_format($totalGeneral, 0, ',', '.') . "\n");
    $printer->setEmphasis(false);

    // Finalizar impresión
    $printer->feed(3);
    $printer->cut();
    $printer->pulse();
    $printer->close();
}

public function reportClosing(Request $request)
{
    $listItems     = $request->data;
    // Información empresarial
    $configuration = new Configuration();
    $company       = $configuration->select()->first();
    $POS_printer   = $company->printer;

    if (!$POS_printer || $POS_printer === '') {
        throw new Exception("Error, no hay impresoras configuradas", 400);
    }

    // Configuración de la impresora
    $connector = new WindowsPrintConnector($POS_printer);
    $printer   = new Printer($connector);
    $printer->initialize();
    $printer->setJustification(Printer::JUSTIFY_CENTER);

    try {
        $logo = EscposImage::load($company->logo, false);
        $printer->bitImage($logo);
    } catch (Exception $e) {
        // Si falla la carga de imagen, se omite
    }

    // Título
    $printer->setTextSize(2, 2);
    $printer->setEmphasis(true);
    $printer->text("Reporte de corte\n");
    $printer->setEmphasis(false);
    $printer->setTextSize(1, 1);
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->feed(2);
    $printer->text("----------------------------------------------\n");

    // Detalle de cada día
    foreach ($listItems as $item) {
        $printer->setEmphasis(true);
        $printer->text("Fecha de venta: ");
        $printer->setEmphasis(false);
        $printer->text($item['date_paid'] . "\n");

        $printer->setEmphasis(true);
        $printer->text("Número total de facturas: ");
        $printer->setEmphasis(false);
        $printer->text($item['number_of_orders'] . "\n");

        $printer->setEmphasis(true);
        $printer->text("Facturas crédito: ");
        $printer->setEmphasis(false);
        $printer->text($item['credit'] . "\n");

        $printer->setEmphasis(true);
        $printer->text("Valor créditos: ");
        $printer->setEmphasis(false);
        $printer->text(number_format($item['paid_credit'], 2) . "\n");

        $printer->setEmphasis(true);
        $printer->text("Cotizaciones: ");
        $printer->setEmphasis(false);
        $printer->text($item['quoted'] . "\n");

        $printer->setEmphasis(true);
        $printer->text("Facturas contado: ");
        $printer->setEmphasis(false);
        $printer->text($item['registered'] . "\n");

        $printer->setEmphasis(true);
        $printer->text("Valor venta: ");
        $printer->setEmphasis(false);
        $printer->text(number_format($item['total_sale'], 2) . "\n");

        $printer->setEmphasis(true);
        $printer->text("Costo venta: ");
        $printer->setEmphasis(false);
        $printer->text(number_format($item['total_sale_iva_exc'], 2) . "\n");

        $printer->setEmphasis(true);
        $printer->text("Ganancia bruta: ");
        $printer->setEmphasis(false);
        $gross = $item['total_sale'] - $item['total_sale_iva_exc'];
        $printer->text(number_format($gross, 2) . "\n");

        $printer->setEmphasis(true);
        $printer->text("Valor pago: ");
        $printer->setEmphasis(false);
        $printer->text(number_format($item['payment_value'], 2) . "\n");

        $printer->setEmphasis(true);
        $printer->text("Forma de pago: ");
        $printer->setEmphasis(false);
        $printer->text($item['payment_form'] . "\n");

        $printer->setEmphasis(true);
        $printer->text("Método de pago: ");
        $printer->setEmphasis(false);
        $printer->text($item['payment_method'] . "\n");

        $printer->feed(1);
        $printer->text("----------------------------------------------\n");
    }

    // --- CÁLCULO DE TOTALIZADOS --- 
    // Total general
    $totalGeneral = array_sum(array_column($listItems, 'total_sale'));

    // Total por Forma de pago
    $totalsByForm = [];
    foreach ($listItems as $i) {
        $form = $i['payment_form'];
        $totalsByForm[$form] = ($totalsByForm[$form] ?? 0) + $i['payment_value'];
    }

    // Total por Método de pago
    $totalsByMethod = [];
    foreach ($listItems as $i) {
        $method = $i['payment_method'];
        $totalsByMethod[$method] = ($totalsByMethod[$method] ?? 0) + $i['payment_value'];
    }

    // Impresión de totales
    $printer->feed(2);
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->setEmphasis(true);
    $printer->text("RESUMEN DE VENTAS\n");
    $printer->setEmphasis(false);
    $printer->setTextSize(1, 1);
    $printer->text("----------------------------------------------\n");

    // Totales por Forma de pago
    $printer->setEmphasis(true);
    $printer->text("Por Forma de pago:\n");
    $printer->setEmphasis(false);
    foreach ($totalsByForm as $form => $sum) {
        $printer->text(sprintf("%-20s %10.2f\n", $form . ":", $sum));
    }
    $printer->text("----------------------------------------------\n");

    // Totales por Método de pago
    $printer->setEmphasis(true);
    $printer->text("Por Método de pago:\n");
    $printer->setEmphasis(false);
    foreach ($totalsByMethod as $method => $sum) {
        $printer->text(sprintf("%-20s %10.2f\n", $method . ":", $sum));
    }
    $printer->text("----------------------------------------------\n");

    // Total General
    $printer->setTextSize(1, 2);
    $printer->setEmphasis(true);
    $printer->text("TOTAL VENTAS: " . number_format($totalGeneral, 2) . "\n");
    $printer->setEmphasis(false);
    $printer->setTextSize(1, 1);
    $printer->setJustification(Printer::JUSTIFY_LEFT);

    // Corte y cierre
    $printer->feed(3);
    $printer->cut();
    $printer->pulse();
    $printer->close();
}




    public function reportSales(Request $request)
    {
        $listItems = $request->data;
        // Información empresarial
        $configuration = new Configuration();
        $company =  $configuration->select()->first();
        $POS_printer = $company->printer;


        if (!$POS_printer || $POS_printer == '') {
            throw new Exception("Error, no hay impresoras configuradas", 400);
        }

        // Config de impresora
        $connector = new WindowsPrintConnector($POS_printer);
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
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        $printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->text("Reporte de venta \n");
        $printer->setEmphasis(false);
        $printer->setTextSize(1, 1);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->feed(2);
        $printer->text("----------------------------------------------\n");

        foreach ($listItems as $item) {

            $printer->setEmphasis(true);
            $printer->text("Fecha de venta: ");
            $printer->setEmphasis(false);
            $printer->text($item['date_paid']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Número de facturas: ");
            $printer->setEmphasis(false);
            $printer->text($item['number_of_orders']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Nro. facturas registradas: ");
            $printer->setEmphasis(false);
            $printer->text($item['registered']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Nro. facturas suspendidas: ");
            $printer->setEmphasis(false);
            $printer->text($item['suspended']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Nro. facturas cotizadas: ");
            $printer->setEmphasis(false);
            $printer->text($item['quoted']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Total precio de costo: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_cost_price_tax_inc']));
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Total IVA excluido: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_iva_exc']));
            $printer->feed(1);
            $printer->setEmphasis(true);

            $printer->text("Total IVA incluido: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f",  $item['total_iva_inc']));
            $printer->feed(1);
            $printer->setEmphasis(true);

            $printer->text("Total Descuento: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_discount']));
            $printer->feed(1);
            $printer->setEmphasis(true);

            $printer->text("Total Pago: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_paid']));
            $printer->feed(1);
            $printer->setEmphasis(true);

            $printer->text("Pagos recibidos: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_discount']));
            $printer->feed(1);
            $printer->setEmphasis(true);


            $cash = $item['cash'];
            $nequi = $item['nequi'];
            $card = $item['card'];
            $others = $item['others'];

            $formattedString = "";
            $formattedString .= sprintf("%-15s %10.2f\n", "* Efectivo:", $cash);
            $formattedString .= sprintf("%-15s %10.2f\n", "* Nequi:", $nequi);
            $formattedString .= sprintf("%-15s %10.2f\n", "* Tarjeta:", $card);
            $formattedString .= sprintf("%-15s %10.2f\n", "* Otros:", $others);


            $printer->text("Pagos recibidos: \n");
            $printer->setEmphasis(false);
            $printer->text($formattedString);

            $printer->setEmphasis(true);
            $printer->text("Total Ganancia: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_iva_exc'] - $item['total_cost_price_tax_inc']));
            $printer->feed(1);
            $printer->setEmphasis(true);


            $printer->feed(1);
            $printer->text("----------------------------------------------\n");

            // $printer->feed(1);

        }
        $printer->feed(3);

        $printer->cut();
        $printer->pulse();
        $printer->close();
    }

    public function reportGeneralSales(Request $request)
    {
        $listItems     = $request->input('data', []);  // Asegúrate de leer bien el array
        $configuration = new Configuration();
        $company       = $configuration->first();
        $POS_printer   = $company->printer;

        if (empty($POS_printer)) {
            return response()->json(['error' => 'No hay impresora configurada'], 400);
        }

        // Inicializa la impresora
        $connector = new WindowsPrintConnector($POS_printer);
        $printer   = new Printer($connector);

        try {
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            // Logo
            try {
                $logo = EscposImage::load($company->logo, false);
                $printer->bitImage($logo);
            } catch (Exception $e) {
                // omitimos si no carga logo
            }

            // Título
            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text("Reporte general de venta\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->feed(2);
            $printer->text(str_repeat('-', 46) . "\n");

            // Detalle
            foreach ($listItems as $item) {
                $printer->text("Número total de facturas: {$item['number_of_orders']}\n");
                $printer->text("Registradas: {$item['registered']}\n");
                $printer->text("Suspendidas: {$item['suspended']}\n");
                $printer->text("Cotizadas: {$item['quoted']}\n");
                $printer->text("Créditos: {$item['credit']}\n");
                $printer->text("Total costo: " . number_format($item['total_cost_price_tax_inc'], 2) . "\n");
                $printer->text("IVA excluido: " . number_format($item['total_iva_exc'], 2) . "\n");
                $printer->text("IVA incluido: " . number_format($item['total_iva_inc'], 2) . "\n");
                $printer->text("Descuento: " . number_format($item['total_discount'], 2) . "\n");
                $printer->text("Total facturado: " . number_format($item['total_paid'], 2) . "\n");
                $printer->text("Ganancia: " . number_format($item['total_iva_exc'] - $item['total_cost_price_tax_inc'], 2) . "\n");
                $printer->text("Valor pago: " . number_format($item['payment_value'], 2) . "\n");
                $printer->text("Forma de pago: {$item['payment_form']}\n");
                $printer->text("Método de pago: {$item['payment_method']}\n");
                $printer->text(str_repeat('-', 46) . "\n");
            }

            // Totalizados
            $totalGeneral = array_sum(array_column($listItems, 'total_paid'));
            $totalsByForm   = [];
            $totalsByMethod = [];
            foreach ($listItems as $i) {
                $totalsByForm[$i['payment_form']] = ($totalsByForm[$i['payment_form']] ?? 0) + $i['payment_value'];
                $totalsByMethod[$i['payment_method']] = ($totalsByMethod[$i['payment_method']] ?? 0) + $i['payment_value'];
            }

            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text("RESUMEN DE PAGOS\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);
            $printer->text(str_repeat('-', 46) . "\n");

            $printer->text("Por Forma de pago:\n");
            foreach ($totalsByForm as $form => $sum) {
                $printer->text(sprintf("%-20s %10.2f\n", "$form:", $sum));
            }
            $printer->text(str_repeat('-', 46) . "\n");

            $printer->text("Por Método de pago:\n");
            foreach ($totalsByMethod as $method => $sum) {
                $printer->text(sprintf("%-20s %10.2f\n", "$method:", $sum));
            }
            $printer->text(str_repeat('-', 46) . "\n");

            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text("TOTAL VENTAS: " . number_format($totalGeneral, 2) . "\n");
            $printer->setEmphasis(false);

            // Final
            $printer->feed(3);
            $printer->cut();
            $printer->pulse();
        } finally {
            $printer->close();
        }

        // Devolvemos una respuesta para que el front no se quede colgado
        return response()->json(['printed' => true]);
    }
    
//reporte productso vendidos 
public function reportInvoicedProducts(Request $request)
{
    $listItems = $request->data;

    // Información empresarial
    $configuration = new Configuration();
    $company = $configuration->select()->first();
    $POS_printer = $company->printer;

    if (!$POS_printer || $POS_printer == '') {
        throw new Exception("Error, no hay impresoras configuradas", 400);
    }

    // Configuración de la impresora
    $connector = new WindowsPrintConnector($POS_printer);
    $printer = new Printer($connector);
    $printer->initialize();
    $printer->setJustification(Printer::JUSTIFY_CENTER);

    // Imprimir encabezado y logo
    try {
        $logo = EscposImage::load($company->logo, false);
        $printer->bitImage($logo);
    } catch (Exception $e) {
        // Se ignoran errores en la carga de imagen
    }

    $printer->setTextSize(2, 2);
    $printer->setEmphasis(true);
    $printer->text("Reporte de productos facturados\n");
    $printer->setEmphasis(false);
    $printer->setTextSize(1, 1);

    // Agrupar los productos por categoría
    $categories = [];
    foreach ($listItems as $item) {
        $categories[$item['category']][] = $item;
    }

    $totalGeneral = 0;
    foreach ($categories as $categoryName => $products) {
        $printer->setEmphasis(true);
        $printer->text("\n$categoryName\n"); // Nombre de la categoría
        $printer->setEmphasis(false);
        $printer->text("----------------------------------------------\n");
        $totalCategory = 0;

        foreach ($products as $product) {
            $printer->text(sprintf(
                "%-28s %5s %10s\n",
                $product['product'], // Nombre del producto
                $product['quantity_of_products'], // Cantidad
                number_format($product['value'], 0, ',', '.') // Valor formateado
            ));
            $totalCategory += $product['value'];
        }

        $printer->text("----------------------------------------------\n");
        $printer->setEmphasis(true);
        $printer->text("Total $categoryName: " . number_format($totalCategory, 0, ',', '.') . "\n");
        $printer->setEmphasis(false);

        $totalGeneral += $totalCategory;
    }

    // Imprimir total general
    $printer->text("\n==============================================\n");
    $printer->setEmphasis(true);
    $printer->text("Total General: " . number_format($totalGeneral, 0, ',', '.') . "\n");
    $printer->setEmphasis(false);

    // Finalizar impresión
    $printer->feed(3);
    $printer->cut();
    $printer->pulse();
    $printer->close();
}


}
