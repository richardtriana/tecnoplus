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
        $printer->text("Reporte de corte \n");
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
            $printer->text("Número total de facturas: ");
            $printer->setEmphasis(false);
            $printer->text($item['number_of_orders']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Nro. facturas credito: ");
            $printer->setEmphasis(false);
            $printer->text($item['credit']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Valor créditos: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['paid_credit'])); //regissrr
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Nro. cotizaciones: ");
            $printer->setEmphasis(false);
            $printer->text($item['quoted']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Nro. facturas de contado: ");
            $printer->setEmphasis(false);
            $printer->text($item['registered']);
            $printer->feed(1);

            $printer->setEmphasis(true);
            $printer->text("Valor total de venta: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_sale']));
            $printer->feed(1);
            $printer->setEmphasis(true);

            $printer->text("Costo total de venta: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f",  $item['total_sale_iva_exc']));
            $printer->feed(1);
            $printer->setEmphasis(true);

            $printer->text("Ganancia bruta: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_sale_iva_exc'] - $item['total_sale_iva_exc']));
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
            $printer->feed(1);
            $printer->text("----------------------------------------------\n");

            // $printer->feed(1);

        }
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
        $printer->text("Reporte general de venta");
        $printer->setEmphasis(false);
        $printer->setTextSize(1, 1);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->feed(2);
        $printer->text("----------------------------------------------\n");

        foreach ($listItems as $item) {

            $printer->setEmphasis(true);
            $printer->text("Número total de facturas: ");
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
            $printer->text("Nro. Creditos: ");
            $printer->setEmphasis(false);
            $printer->text($item['credit']);
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

            $printer->text("Total facturado: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_paid']));
            $printer->feed(1);
            $printer->setEmphasis(true);


            $printer->setEmphasis(true);
            $printer->text("Ganancia del día: ");
            $printer->setEmphasis(false);
            $printer->text(sprintf("%.2f", $item['total_iva_exc'] - $item['total_cost_price_tax_inc']));
            $printer->feed(1);
            $printer->setEmphasis(true);

            $printer->feed(1);
            $printer->text("----------------------------------------------\n");
        }
        $printer->feed(3);

        $printer->cut();
        $printer->pulse();
        $printer->close();
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
