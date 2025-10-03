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
<<<<<<< HEAD
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



=======
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
                    $product['product'],                  // Nombre del producto
                    $product['quantity_of_products'],     // Cantidad
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

    /**
     * Imprime por ticket el reporte de corte.
     * POST /api/reports-ticket/closing
     */
    public function reportClosing(Request $request)
    {
        $listItems = $request->data;

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
            // ignorar si no carga logo
        }

        // Título
        $printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->text("Reporte de corte\n");
        $printer->setEmphasis(false);
        $printer->setTextSize(1, 1);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->feed(2);
        $printer->text(str_repeat('-', 46) . "\n");

        // Detalle de cada día
        foreach ($listItems as $item) {
            // Fecha de venta
            $printer->setEmphasis(true);
            $printer->text("Fecha de venta: ");
            $printer->setEmphasis(false);
            $printer->text($item['date_paid'] . "\n");

            // Número total de facturas
            $printer->setEmphasis(true);
            $printer->text("Total facturas: ");
            $printer->setEmphasis(false);
            $printer->text($item['number_of_orders'] . "\n");

            // Facturas a crédito
            $printer->setEmphasis(true);
            $printer->text("Facturas crédito: ");
            $printer->setEmphasis(false);
            $printer->text($item['credit_invoices'] . "\n");

            // Valor ventas a crédito
            $printer->setEmphasis(true);
            $printer->text("Ventas crédito: ");
            $printer->setEmphasis(false);
            $printer->text(number_format($item['credit_sales'], 2) . "\n");

            // Valor venta total
            $printer->setEmphasis(true);
            $printer->text("Valor venta: ");
            $printer->setEmphasis(false);
            $printer->text(number_format($item['total_sale'], 2) . "\n");

            // Costo venta
            $printer->setEmphasis(true);
            $printer->text("Costo venta: ");
            $printer->setEmphasis(false);
            $printer->text(number_format($item['total_sale_iva_exc'], 2) . "\n");

            // Ganancia bruta
            $printer->setEmphasis(true);
            $printer->text("Ganancia bruta: ");
            $printer->setEmphasis(false);
            $gross = $item['total_sale'] - $item['total_sale_iva_exc'];
            $printer->text(number_format($gross, 2) . "\n");

            // Valor pago
            $printer->setEmphasis(true);
            $printer->text("Pago recibido: ");
            $printer->setEmphasis(false);
            $printer->text(number_format($item['payment_value'], 2) . "\n");

            // Forma de pago
            $printer->setEmphasis(true);
            $printer->text("Forma pago: ");
            $printer->setEmphasis(false);
            $printer->text($item['payment_form'] . "\n");

            // Método de pago
            $printer->setEmphasis(true);
            $printer->text("Método pago: ");
            $printer->setEmphasis(false);
            $printer->text($item['payment_method'] . "\n");

            $printer->feed(1);
            $printer->text(str_repeat('-', 46) . "\n");
        }

        // Totales generales
        $totalGeneral = array_sum(array_column($listItems, 'total_sale'));

        // Totales por forma de pago
        $totalsByForm = [];
        foreach ($listItems as $i) {
            $form = $i['payment_form'];
            $totalsByForm[$form] = ($totalsByForm[$form] ?? 0) + $i['payment_value'];
        }

        // Totales por método de pago
        $totalsByMethod = [];
        foreach ($listItems as $i) {
            $method = $i['payment_method'];
            $totalsByMethod[$method] = ($totalsByMethod[$method] ?? 0) + $i['payment_value'];
        }

        // Impresión de sección de totales
        $printer->feed(2);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->text("RESUMEN DE VENTAS\n");
        $printer->setEmphasis(false);
        $printer->setTextSize(1, 1);
        $printer->text(str_repeat('-', 46) . "\n");

        // Por forma de pago
        $printer->setEmphasis(true);
        $printer->text("Por Forma de pago:\n");
        $printer->setEmphasis(false);
        foreach ($totalsByForm as $form => $sum) {
            $printer->text(sprintf("%-20s %10.2f\n", "$form:", $sum));
        }
        $printer->text(str_repeat('-', 46) . "\n");

        // Por método de pago
        $printer->setEmphasis(true);
        $printer->text("Por Método de pago:\n");
        $printer->setEmphasis(false);
        foreach ($totalsByMethod as $method => $sum) {
            $printer->text(sprintf("%-20s %10.2f\n", "$method:", $sum));
        }
        $printer->text(str_repeat('-', 46) . "\n");

        // Total general
        $printer->setTextSize(1, 2);
        $printer->setEmphasis(true);
        $printer->text("TOTAL VENTAS: " . number_format($totalGeneral, 2) . "\n");
        $printer->setEmphasis(false);
        $printer->setTextSize(1, 1);
        $printer->setJustification(Printer::JUSTIFY_LEFT);

        // Cierre de impresión
        $printer->feed(3);
        $printer->cut();
        $printer->pulse();
        $printer->close();
    }

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

    public function reportSales(Request $request)
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

        try {
            $logo = EscposImage::load($company->logo, false);
            $printer->bitImage($logo);
        } catch (Exception $e) {
            // Ignorar errores relacionados con imágenes
        }

        $printer->setTextSize(2, 2);
        $printer->setEmphasis(true);
        $printer->text("Reporte de venta\n");
        $printer->setEmphasis(false);
        $printer->setTextSize(1, 1);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->feed(2);
        $printer->text(str_repeat('-', 46) . "\n");

        foreach ($listItems as $item) {
            // Fecha de venta (fallback a created_at si no existe date_paid)
            $date = $item['date_paid'] ?? $item['created_at'] ?? '';
            $printer->setEmphasis(true);
            $printer->text("Fecha de venta: ");
            $printer->setEmphasis(false);
            $printer->text("$date\n");
            $printer->feed(1);

            // Número total de facturas (si viene agregada) o mostramos 1 por defecto
            $num = $item['number_of_orders'] ?? 1;
            $printer->setEmphasis(true);
            $printer->text("Número de facturas: ");
            $printer->setEmphasis(false);
            $printer->text("$num\n");
            $printer->feed(1);

            // Registradas, suspendidas, cotizadas
            foreach (['registered','suspended','quoted'] as $key) {
                if (isset($item[$key])) {
                    $label = ucfirst($key) . ': ';
                    $printer->setEmphasis(true);
                    $printer->text("$label");
                    $printer->setEmphasis(false);
                    $printer->text($item[$key] . "\n");
                    $printer->feed(1);
                }
            }

            // Totales: costo, IVA exc, IVA inc, descuento, pagado
            foreach ([
                'total_cost_price_tax_inc' => 'Total costo: ',
                'total_iva_exc'            => 'IVA excluido: ',
                'total_iva_inc'            => 'IVA incluido: ',
                'total_discount'           => 'Descuento: ',
                'total_paid'               => 'Total pagado: '
            ] as $field => $label) {
                if (isset($item[$field])) {
                    $printer->setEmphasis(true);
                    $printer->text($label);
                    $printer->setEmphasis(false);
                    $printer->text(sprintf("%.2f", $item[$field]) . "\n");
                    $printer->feed(1);
                }
            }

            // Forma de Pago
            $formName = $item['payment_form']['name'] ?? '-';
            $printer->setEmphasis(true);
            $printer->text("Forma de Pago: ");
            $printer->setEmphasis(false);
            $printer->text("$formName\n");
            $printer->feed(1);

            // Método de Pago
            $methodName = $item['payment_method']['name'] ?? '-';
            $printer->setEmphasis(true);
            $printer->text("Método de Pago: ");
            $printer->setEmphasis(false);
            $printer->text("$methodName\n");

            $printer->feed(1);
            $printer->text(str_repeat('-', 46) . "\n");
        }

        // Finalizar impresión
        $printer->feed(3);
        $printer->cut();
        $printer->pulse();
        $printer->close();
    }


    public function reportGeneralSales(Request $request)
    {
<<<<<<< HEAD
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
=======
        $listItems     = $request->input('data', []);
        $configuration = new Configuration();
        $company       = $configuration->first();
        $POS_printer   = $company->printer;

        if (empty($POS_printer)) {
            return response()->json(['error' => 'No hay impresora configurada'], 400);
        }

        $connector = new WindowsPrintConnector($POS_printer);
        $printer   = new Printer($connector);

        try {
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            try {
                $logo = EscposImage::load($company->logo, false);
                $printer->bitImage($logo);
            } catch (Exception $e) {
                // omitimos si no carga logo
            }

            $printer->setTextSize(2, 2);
            $printer->setEmphasis(true);
            $printer->text("Reporte general de venta\n");
            $printer->setEmphasis(false);
            $printer->setTextSize(1, 1);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->feed(2);
            $printer->text(str_repeat('-', 46) . "\n");

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

            $totalGeneral = array_sum(array_column($listItems, 'total_paid'));
            $totalsByForm   = [];
            $totalsByMethod = [];
            foreach ($listItems as $i) {
                $totalsByForm[$i['payment_form']]     = ($totalsByForm[$i['payment_form']] ?? 0) + $i['payment_value'];
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

            $printer->feed(3);
            $printer->cut();
            $printer->pulse();
        } finally {
            $printer->close();
        }

        return response()->json(['printed' => true]);
    }

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
            $printer->text("\n$categoryName\n");
            $printer->setEmphasis(false);
            $printer->text("----------------------------------------------\n");
            $totalCategory = 0;

            foreach ($products as $product) {
                $printer->text(sprintf(
                    "%-28s %5s %10s\n",
                    $product['product'],
                    $product['quantity_of_products'],
                    number_format($product['value'], 0, ',', '.')
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

public function reportHistory(Request $request)
>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
{
    $list = $request->data;
    if (empty($list)) {
        throw new Exception("No se recibió ningún registro para imprimir.", 400);
    }

    // Información de la empresa
    $config       = Configuration::first();
    $companyName  = $config->name           ?? 'Mi Empresa';
    $companyTax   = $config->tax_id         ?? '';
    $companyAddr  = $config->address        ?? '';
    $companyPhone = $config->phone          ?? '';
    $logoPath     = $config->logo;          // ruta al logo
    $printerName  = $config->printer;
    if (!$printerName) {
        throw new Exception("No hay impresora configurada.", 400);
    }

    // Conexión a impresora
    $connector = new WindowsPrintConnector($printerName);
    $printer   = new Printer($connector);

    try {
        // Aumentar tamaño de letra al máximo (triple ancho, triple alto)
        $printer->setTextSize(3, 3);
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        // Logo
        try {
            if ($logoPath) {
                $logo = EscposImage::load($logoPath, false);
                $printer->bitImage($logo);
            }
        } catch (\Exception $e) {
            // si falla logo, seguimos
        }

        // Encabezado empresa
        $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
        $printer->text($companyName . "\n");
        $printer->selectPrintMode();
        if ($companyTax)   $printer->text("NIT: " . $companyTax . "\n");
        if ($companyAddr)  $printer->text($companyAddr . "\n");
        if ($companyPhone) $printer->text("Tel: " . $companyPhone . "\n");
        $printer->feed(1);

        foreach ($list as $hist) {
            // Título del reporte
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("REPORTE DE CORTE DE CAJA\n");
            $printer->text(str_repeat("=", 32) . "\n");

            // Datos básicos
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("N° Cierre:       {$hist['id']}\n");
            $printer->text("Caja:            " . ($hist['box']['name'] ?? $hist['box_id']) . "\n");
            $printer->text("Apertura:        " . date('Y-m-d H:i', strtotime($hist['opened_at'])) . "\n");
            $printer->text("Cierre:          " . date('Y-m-d H:i', strtotime($hist['closed_at'])) . "\n");
            $printer->text("Usu. Apertura:   " . ($hist['opening_user']['name'] ?? '') . "\n");
            $printer->text("Usu. Cierre:     " . ($hist['closing_user']['name'] ?? '') . "\n");
            $printer->text(str_repeat("-", 32) . "\n");

            // Valores intermedios
            $printer->text(sprintf("Base Inicial:    %12.2f\n", $hist['opening_balance']));
            $printer->text(sprintf("Entradas:        %12.2f\n", $hist['entries']));
            $printer->text(sprintf("Salidas:         %12.2f\n", $hist['exits']));
            $printer->text(sprintf("Créditos:        %12.2f\n", $hist['credits']));
            $printer->text(str_repeat("-", 32) . "\n");

            // Campos clave en negrita (Calculado, Reportado, Diferencia)
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text(sprintf("Calculado:       %12.2f\n", $hist['computed_balance']));
            $printer->text(sprintf("Reportado:       %12.2f\n", $hist['reported_balance']));
            $printer->text(sprintf("Diferencia:      %12.2f\n", $hist['difference']));
            $printer->selectPrintMode();
            $printer->text(str_repeat("-", 32) . "\n");

            // Conclusión
            $diff = floatval($hist['difference']);
            if ($diff > 0) {
                $conclusion = 'Sobrante';
            } elseif ($diff < 0) {
                $conclusion = 'Faltante';
            } else {
                $conclusion = 'Correcto';
            }
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("Conclusión:      {$conclusion}\n");
            $printer->selectPrintMode();

            // Pie de ticket
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->feed(2);
            $printer->text("¡Gracias por su gestión!\n");
            $printer->cut();
        }
    } finally {
        $printer->close();
    }

    return response()->json(['printed' => true], 200);
}






}
