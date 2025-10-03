<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DetailOrderController;
use App\Http\Controllers\KitProductController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ImportProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\DetailBillingController;
use App\Http\Controllers\PrintOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTicketController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ZoneController;
use App\Models\Configuration;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortionController;
use App\Http\Controllers\PortionHistoryController;
use App\Http\Controllers\PortionOrderController;
//implementacion de factura electronica 
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\ProductIdentificationStandardController;
use App\Http\Controllers\ProductPortionController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\PaymentFormController;
use App\Http\Controllers\PaymentMethodController;
//comprobantes
use App\Http\Controllers\NumberingRangeDocumentTypeController;
use App\Http\Controllers\NumberingRangeController;
//facturacion electronica
use App\Http\Controllers\FactusTokenController;
use App\Http\Controllers\FactusDataController;
use App\Http\Controllers\FactusSyncController;
//notas creditos
use App\Http\Controllers\CreditNoteController;

//rutas de tabals de factus locales 
use App\Http\Controllers\OrganizationTypeController;
use App\Http\Controllers\ClientTributeController;
use App\Http\Controllers\IdentityDocumentTypeController;
use App\Http\Controllers\ProductTributeController;
use App\Http\Controllers\CorrectionCodeController;
use App\Http\Controllers\OperationTypeController;
use App\Http\Controllers\AdjustmentNoteReasonController;
use App\Http\Controllers\EventCodeController;

//comprobantes factus
use App\Http\Controllers\FactusInvoiceController;
use App\Http\Controllers\FactusCreditNoteController;
use App\Http\Controllers\FactusAdjustmentNoteController;
use App\Http\Controllers\FactusSupportDocumentController;
use App\Http\Controllers\AdjustmentNoteController;

//prueb afactus
use App\Http\Controllers\FactusTestController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SupportDocumentController;
use App\Http\Controllers\FactusReceptionController;
use App\Http\Controllers\OrderCreditController;

<<<<<<< HEAD
=======
//caja
use App\Http\Controllers\CashReconciliationController;
use App\Http\Controllers\ProductObservationController;


>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::post('/login', [UserController::class, 'login']);

    Route::post('/factus/token', [FactusTokenController::class, 'getToken']);

    // Rutas públicas para obtener datos de la API Factus
    Route::prefix('factus')->group(function () {
    Route::get('/tributes/products', [FactusDataController::class, 'getProductTributes']);
    Route::get('/measurement-units', [FactusDataController::class, 'getMeasurementUnits']);
    Route::get('/municipalities', [FactusDataController::class, 'getMunicipalities']);
    Route::get('/numbering-ranges', [FactusDataController::class, 'getNumberingRanges']);
    });

    // Rutas públicas para sincronizar datos en la BD local
    Route::prefix('factus')->group(function () {
    Route::get('/sync/numbering-ranges', [FactusSyncController::class, 'syncNumberingRanges']);
    Route::get('/sync/municipalities', [FactusSyncController::class, 'syncMunicipalities']);
    Route::get('/sync/tributes', [FactusSyncController::class, 'syncTributes']);
    Route::get('/sync/measurement-units', [FactusSyncController::class, 'syncMeasurementUnits']);
    Route::get('/local-stats', [FactusSyncController::class, 'localStats']);
    });

    // Facturas
    Route::prefix('factus-invoice')->group(function () {
        Route::get('getBills', [FactusInvoiceController::class, 'getBills']);
        Route::get('download-pdf/{number}', [FactusInvoiceController::class, 'downloadPdf']);
        Route::get('download-xml/{number}', [FactusInvoiceController::class, 'downloadXml']);
        Route::delete('deleteBill/{referenceCode}', [FactusInvoiceController::class, 'deleteBill']);
        });

    // Notas de Ajuste
    Route::prefix('factus-adjustment-note')->group(function () {
        Route::get('getAdjustmentNotes', [FactusAdjustmentNoteController::class, 'getAdjustmentNotes']);
        Route::get('show/{number}', [FactusAdjustmentNoteController::class, 'showAdjustmentNote']);
        Route::get('download-pdf/{number}', [FactusAdjustmentNoteController::class, 'downloadAdjustmentNotePdf']);
        Route::get('download-xml/{number}', [FactusAdjustmentNoteController::class, 'downloadAdjustmentNoteXml']);
        Route::delete('delete/{referenceCode}', [FactusAdjustmentNoteController::class, 'deleteAdjustmentNote']);
    });

    // Notas de Crédito
    Route::prefix('factus-credit-note')->group(function () {
        Route::get('getCreditNotes', [FactusCreditNoteController::class, 'getCreditNotes']);
        Route::get('show/{number}', [FactusCreditNoteController::class, 'showCreditNote']);
        Route::get('download-pdf/{number}', [FactusCreditNoteController::class, 'downloadCreditNotePdf']);
        Route::get('download-xml/{number}', [FactusCreditNoteController::class, 'downloadCreditNoteXml']);
        Route::delete('delete/{referenceCode}', [FactusCreditNoteController::class, 'deleteCreditNote']);
    });

    // Documentos Soporte
    Route::prefix('factus-support-document')->group(function () {
        Route::get('getSupportDocuments', [FactusSupportDocumentController::class, 'getSupportDocuments']);
        Route::get('show/{number}', [FactusSupportDocumentController::class, 'showSupportDocument']);
        Route::get('download-pdf/{number}', [FactusSupportDocumentController::class, 'downloadSupportDocumentPdf']);
        Route::get('download-xml/{number}', [FactusSupportDocumentController::class, 'downloadSupportDocumentXml']);
        Route::delete('delete/{referenceCode}', [FactusSupportDocumentController::class, 'deleteSupportDocument']);
    });


    //recepcion de documentos
    Route::prefix('factus-receptions')->group(function () {
        // Cargar factura electrónica
        Route::post('upload', [FactusReceptionController::class, 'uploadInvoice']);
    
        // Consultar facturas electrónicas
        Route::get('bills', [FactusReceptionController::class, 'getBills']);
    
        // Emitir evento sobre una factura electrónica
        Route::post('bills/{bill_id}/radian/events/{event_type}', [FactusReceptionController::class, 'emitEvent']);
    });

    //reimprecion
    Route::get('orders/reprint-list', [OrderController::class, 'reprintList'])->withoutMiddleware('auth:api');
	 // Rutas para Payment Methods
	Route::get('payment_methods', [PaymentMethodController::class, 'index']);
	Route::get('payment_methods/{id}', [PaymentMethodController::class, 'show']);

	Route::apiResource('support-documents', SupportDocumentController::class);
	
	Route::resource('/taxes', TaxController::class);
    Route::post('/taxes/{tax}/activate',  [TaxController::class, 'activate']);

	Route::get('measurement-units', [MeasurementUnitController::class, 'index'])
	->withoutMiddleware('auth:api');

	Route::resource('/suppliers',  SupplierController::class);

	//con autenticacion 

Route::middleware('auth:api')->group(function () {

    Route::put('/users/changePassword',  [UserController::class, 'changePassword']);
    Route::get('/users/user-list', [UserController::class, 'listUsers'])->middleware('can:user.index');
    Route::get('/user/wairte', [UserController::class, 'listWaiter']);
    Route::resource('/users', UserController::class);
    Route::post('/users/{user}/activate',  [UserController::class, 'activate']);
    Route::post('/register', [UserController::class, 'register']);


    Route::post('categories/{id}/activate', [CategoryController::class, 'activate']);
    Route::post('/zones/{category}/activate',  [CategoryController::class, 'activate']);
    Route::get('/categories/category-list', [CategoryController::class, 'categoryList']);
    Route::resource('/categories', CategoryController::class);
    
    //servicios
    Route::post('services/{id}/activate', [ServiceController::class, 'activate'])->withoutMiddleware('auth:api');
    Route::get('/services/service-list', [ServiceController::class, 'serviceList'])->withoutMiddleware('auth:api');
    Route::resource('/services', ServiceController::class)->withoutMiddleware('auth:api');

    Route::post('/brands/{brand}/activate',  [BrandController::class, 'activate']);
    Route::get('/brands/brand-list', [BrandController::class, 'brandList']);
    Route::resource('/brands', BrandController::class);

    Route::get('/print-order/{id}', [PrintOrderController::class, 'printTicket'])->middleware('can:order.index');
    Route::get('orders/byBill/{bill_id}', [OrderController::class, 'getOrderByBill']);

    Route::get('/print-payment-ticket/{order}', [PrintOrderController::class, 'printPaymentTicket'])->middleware('can:order.index');
    Route::get('/orders/generatePdf/{order}', [OrderController::class, 'generatePdf']);
    Route::get('/orders/generatePaymentPdf/{order}', [OrderController::class, 'generatePaymentPdf']);
    Route::get('/orders/kitchen', [OrderController::class, 'ordersForKitchen']);
    Route::put('/orders/kitchen/{order}', [OrderController::class, 'prepareOrderKitchen']);
    Route::get('/orders/reprint/{id}', [OrderController::class, 'reprint']);
    Route::get('/print-precuenta/{id}', [PrintOrderController::class, 'printPreCuenta'])->middleware('can:order.index');
    Route::delete('orders/{orderId}/remove-product/{detailId}', [OrderController::class, 'removeProductFromOrder']);
    Route::resource('/orders',  OrderController::class);
<<<<<<< HEAD
    Route::resource('/order-details', DetailOrderController::class);
    //dividir cuenta
    Route::post('orders/{order}/split', [OrderController::class, 'split']);



=======
    Route::post('orders/{order}/split', [OrderController::class, 'split'])->middleware('can:order.update');
     



    Route::resource('/order-details', DetailOrderController::class);

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)
    Route::get('/billings/generatePdf/{billing}', [BillingController::class, 'generatePdf']);
    Route::resource('/billings',  BillingController::class);
    Route::resource('/billing-details', DetailBillingController::class);


    Route::get('/orders/byClient/{client_id}', [OrderController::class, 'creditByClient']);
    Route::post('/orders/payCreditByClient', [OrderController::class, 'payCreditByClient']);

    Route::resource('/products',  ProductController::class);
    Route::post('/products/{product}/activate',  [ProductController::class, 'activate']);
    Route::post('/products/search-product',  [ProductController::class, 'searchProduct']);
    Route::post('/products/filter-product-list',  [ProductController::class, 'filterProductList']);
    Route::post('/products/stock-update/{id}', [ProductController::class, 'updateStockById']);
    Route::get('products/{id}/portions', [ProductController::class, 'getPortions']);

    Route::resource('kit-products', KitProductController::class)->middleware('can:product.store');
<<<<<<< HEAD

    Route::resource('/suppliers',  SupplierController::class);
    Route::post('/suppliers/{supplier}/activate',  [SupplierController::class, 'activate']);
    Route::post('/suppliers/search-supplier',  [SupplierController::class, 'searchSupplier']);
    Route::post('/suppliers/filter-supplier-list',  [SupplierController::class, 'filterSupplierList']);

    Route::get('/clients/filter-client-list', [ClientController::class, 'filterClientList']);
    Route::post('/clients/search-client', [ClientController::class, 'searchClient']);
    Route::post('/clients/{client}/activate', [ClientController::class, 'activate']);
    Route::resource('/clients', ClientController::class)->except(['show']);


    Route::get('/roles/getAllRoles', [RoleController::class, 'getAllRoles']);
    Route::resource('/roles', RoleController::class);
    Route::get('/permissions', [RoleController::class, 'getPermissions']);

    Route::get('/departments', [DepartmentController::class, 'getDistinctDepartments']);
    Route::get('/departments/{department}/getMunicipalities', [DepartmentController::class, 'getMunicipalitiesByDepartment']);

    Route::resource('/configurations', ConfigurationController::class)->except(['create', 'edit', 'destroy', 'show'])->middleware('can:configuration');;
    Route::get('/company-logo', function () {
        $configuration = new Configuration();
        $image = $configuration->select('logo')->first();
        return $image;
    });
    Route::post('/import/upload-file-import', [ImportProductController::class, 'uploadFile'])->middleware('can:product.store');

    Route::get('/reports/sales-report', [ReportController::class, 'reportSales']);
    Route::get('/reports/general-sales-report', [ReportController::class, 'reportGeneralSales']);
    Route::get('/reports/product-sales-report', [ReportController::class, 'reportProductSales']);
    Route::get('/reports/total-products-report', [ReportController::class, 'reportTotalProducts']);
    Route::get('/reports/closing', [ReportController::class, 'reportClosing']);
    Route::get('/reports/invoiced-products', [ReportController::class, 'reportInvoicedProducts']);


    Route::get('/reports-ticket/test', [ReportTicketController::class, 'test']);
    Route::post('/reports-ticket/sales-report', [ReportTicketController::class, 'reportSales']); // ✅
    Route::post('/reports-ticket/closing', [ReportTicketController::class, 'reportClosing']); // ✅
    Route::post('/reports-ticket/general-sales-report', [ReportTicketController::class, 'reportGeneralSales']);
    Route::get('reports/sales-report/export', [ReportController::class, 'exportSalesReport']);
    Route::post('orders/{id}/resend-dian', [OrderController::class, 'resendDian']);
    Route::post('/reports-ticket/product-sales-report', [ReportTicketController::class, 'reportProductSales']); // ✅
    Route::post('/reports-ticket/total-products-report', [ReportTicketController::class, 'reportTotalProducts']);
    Route::post('/reports-ticket/invoiced-products', [ReportTicketController::class, 'reportInvoicedProducts']);


    Route::get('/boxes/box-list', [BoxController::class, 'boxList']);
    Route::get('/boxes/byUser', [BoxController::class, 'getBoxesByUser']);
    Route::resource('/boxes', BoxController::class);
    Route::post('/boxes/{box}/activate', [BoxController::class, 'activate']);
    Route::post('/boxes/base/{box}', [BoxController::class, 'updateBase']);

    Route::get('/boxes/{box}/consecutiveAll', [BoxController::class, 'consecutiveAllByBox'])->middleware('can:box.index');
    Route::get('/boxes/{box}/getAssignUserByBox', [BoxController::class, 'getAssignUserByBox'])->middleware('can:box.index');
    Route::post('/boxes/{box}/toAssignUserByBox', [BoxController::class, 'toAssignUserByBox'])->middleware('can:box.store');
    Route::post('boxes/{id}/assign-vouchers', [BoxController::class, 'toAssignVouchers']);
    Route::get('boxes/{id}/assigned-vouchers', [BoxController::class, 'assignedVouchers']);



    Route::get('/zones/zone-list', [ZoneController::class, 'zoneList']);
    Route::resource('/zones', ZoneController::class);

    Route::get('/tables/table-list', [TableController::class, 'tableList']);
    Route::resource('/tables', TableController::class);

    //porciones
    Route::resource('/porciones',PortionController::class);
    Route::post('/porciones/{id}/activate', [PortionController::class, 'activate']);

    //porciones de productos
    Route::get('products/{product}', [ProductController::class, 'show'])
     ->where('product', '[0-9]+');
    Route::get('product-portions', [ProductPortionController::class, 'index']);
    Route::post('product-portions', [ProductPortionController::class, 'store']);
    Route::get('products/filter', [ProductController::class, 'filter']);
    Route::put('product-portions/{id}', [ProductPortionController::class, 'update']);
    Route::delete('product-portions/{id}', [ProductPortionController::class, 'destroy']);

    Route::get('portions', [PortionController::class, 'index']);
    Route::post('portions', [PortionController::class, 'store']);
    Route::put('portions/{id}', [PortionController::class, 'update']);
    Route::post('portions/{id}/changeStatus', [PortionController::class, 'changeStatus']);
    Route::get('portions/exportExcel', [PortionController::class, 'exportExcel']);
    Route::get('portions/{id}/histories', [PortionHistoryController::class, 'index']);

    //ordenes de eporciones
    Route::get('portion_orders', [PortionOrderController::class, 'index']);
    Route::post('portion_orders', [PortionOrderController::class, 'store']);
    Route::put('portion_orders/{id}', [PortionOrderController::class, 'update']);

    //tablas de locales de factus 
    Route::get('/organization-types', [OrganizationTypeController::class, 'index']);
    Route::get('/client-tributes', [ClientTributeController::class, 'index']);
    Route::get('/identity-document-types', [IdentityDocumentTypeController::class, 'index']);
    Route::get('/product-tributes', [ProductTributeController::class, 'index'])
    ->withoutMiddleware('auth:api');
    Route::get('measurement-units', [MeasurementUnitController::class, 'index'])
	->withoutMiddleware('auth:api');
    Route::get('product-identification-standards', [ProductIdentificationStandardController::class, 'index']);
    Route::get('/correction-codes', [CorrectionCodeController::class, 'index']);
    Route::get('/operation-types', [OperationTypeController::class, 'index']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('event-codes', [EventCodeController::class, 'index'])->withoutMiddleware('auth:api');


    //// Rutas para los tipos de comprobantes (numbering_range_document_types)
    // Se utiliza solo GET, ya que se usa para obtener la lista de tipos.
    Route::get('numbering_range_document_types', [NumberingRangeDocumentTypeController::class, 'index']);
    Route::get('payment_forms', [PaymentFormController::class, 'index']);
    Route::get('payment_forms/{id}', [PaymentFormController::class, 'show']); // opcional si deseas ver 1

    //creditos
    Route::apiResource('order-credits', OrderCreditController::class)->except(['update']);
    Route::post('order-credits/{credit}/payments', [OrderCreditController::class, 'addPayment']);
    Route::post('/orders/payCreditByClient', [OrderController::class, 'payCreditByClient']);

    // Rutas para los comprobantes (numbering_ranges)
    // Permiten listar, crear y actualizar los comprobantes.
    Route::get('numbering_ranges', [NumberingRangeController::class, 'index']);
    Route::get('/numbering-ranges/credit-notes', [NumberingRangeController::class, 'creditNotes']);
    Route::post('numbering_ranges', [NumberingRangeController::class, 'store'])->withoutMiddleware('auth:api');
    Route::put('numbering_ranges/{id}', [NumberingRangeController::class, 'update'])->withoutMiddleware('auth:api');
    Route::get('numbering_ranges/{id}', [NumberingRangeController::class, 'show'])->withoutMiddleware('auth:api');

    Route::get('vouchers', [VoucherController::class, 'index']);
    Route::get('vouchers/{id}', [VoucherController::class, 'show']);
    Route::post('vouchers', [VoucherController::class, 'store']);
    Route::put('vouchers/{id}', [VoucherController::class, 'update']);
    Route::delete('vouchers/{id}', [VoucherController::class, 'destroy']);

    Route::get('adjustment-note-reasons', [AdjustmentNoteReasonController::class, 'index'])->withoutMiddleware('auth:api');
    Route::get('adjustment-note-reasons/{id}', [AdjustmentNoteReasonController::class, 'show'])->withoutMiddleware('auth:api');

    // Opcional: Si necesitas una ruta para mostrar un comprobante en particular.
    Route::get('numbering_range_document_types', [NumberingRangeDocumentTypeController::class, 'index'])
    ->withoutMiddleware('auth:api');

    Route::get('numbering_ranges', [NumberingRangeController::class, 'index'])
    ->withoutMiddleware('auth:api');
    Route::post('/factustest', [FactusTestController::class, 'test'])->withoutMiddleware('auth:api');
    Route::post('/factustest2', [FactusTestController::class, 'testDirect'])->withoutMiddleware('auth:api');
    //notas credito 
    Route::post('/credit-notes/validate', [CreditNoteController::class, 'store'])->withoutMiddleware('auth:api');
    Route::get('credit-notes/numbering-ranges', [CreditNoteController::class, 'getNumberingRanges'])->withoutMiddleware('auth:api');
    Route::get('credit-notes/adjustment-note-reasons', [CreditNoteController::class, 'getAdjustmentNoteReasons'])->withoutMiddleware('auth:api');
    Route::get('credit-notes/operation-types', [CreditNoteController::class, 'getOperationTypes'])->withoutMiddleware('auth:api');

    //documento soporte
    
    Route::apiResource('support-document', SupportDocumentController::class)->withoutMiddleware('auth:api');
    Route::post('adjustment-notes', [AdjustmentNoteController::class, 'store']);
=======

    Route::resource('/suppliers',  SupplierController::class);
    Route::post('/suppliers/{supplier}/activate',  [SupplierController::class, 'activate']);
    Route::post('/suppliers/search-supplier',  [SupplierController::class, 'searchSupplier']);
    Route::post('/suppliers/filter-supplier-list',  [SupplierController::class, 'filterSupplierList']);

    Route::get('/clients/filter-client-list', [ClientController::class, 'filterClientList']);
    Route::post('/clients/search-client', [ClientController::class, 'searchClient']);
    Route::post('/clients/{client}/activate', [ClientController::class, 'activate']);
    Route::resource('/clients', ClientController::class)->except(['show']);


    Route::get('/roles/getAllRoles', [RoleController::class, 'getAllRoles']);
    Route::resource('/roles', RoleController::class);
    Route::get('/permissions', [RoleController::class, 'getPermissions']);

    Route::get('/departments', [DepartmentController::class, 'getDistinctDepartments']);
    Route::get('/departments/{department}/getMunicipalities', [DepartmentController::class, 'getMunicipalitiesByDepartment']);

    Route::resource('/configurations', ConfigurationController::class)->except(['create', 'edit', 'destroy', 'show'])->middleware('can:configuration');;
    Route::get('/company-logo', function () {
        $configuration = new Configuration();
        $image = $configuration->select('logo')->first();
        return $image;
    });
    Route::get('configurations/shipments-status', [ConfigurationController::class, 'getShipmentsStatus']);



    Route::post('/import/upload-file-import', [ImportProductController::class, 'uploadFile'])->middleware('can:product.store');

    Route::get('/reports/sales-report', [ReportController::class, 'reportSales']);
    Route::get('/reports/general-sales-report', [ReportController::class, 'reportGeneralSales']);
    Route::get('/reports/product-sales-report', [ReportController::class, 'reportProductSales']);
    Route::get('/reports/total-products-report', [ReportController::class, 'reportTotalProducts']);
    Route::get('/reports/closing', [ReportController::class, 'reportClosing']);
    Route::get('/reports/invoiced-products', [ReportController::class, 'reportInvoicedProducts']);


    Route::get('/reports-ticket/test', [ReportTicketController::class, 'test']);
    Route::post('/reports-ticket/sales-report', [ReportTicketController::class, 'reportSales']); // ✅
    Route::post('/reports-ticket/closing', [ReportTicketController::class, 'reportClosing']); // ✅
    Route::post('/reports-ticket/general-sales-report', [ReportTicketController::class, 'reportGeneralSales']);
    Route::get('reports/sales-report/export', [ReportController::class, 'exportSalesReport']);
    Route::post('orders/{id}/resend-dian', [OrderController::class, 'resendDian']);
    Route::post('/reports-ticket/product-sales-report', [ReportTicketController::class, 'reportProductSales']); // ✅
    Route::post('/reports-ticket/total-products-report', [ReportTicketController::class, 'reportTotalProducts']);
    Route::post('/reports-ticket/invoiced-products', [ReportTicketController::class, 'reportInvoicedProducts']);
    Route::post('/reports-ticket/history', [ReportTicketController::class, 'reportHistory']);


    Route::get('/boxes/box-list', [BoxController::class, 'boxList']);
    Route::get('/boxes/byUser', [BoxController::class, 'getBoxesByUser']);
    Route::resource('/boxes', BoxController::class);
    Route::post('/boxes/{box}/activate', [BoxController::class, 'activate']);
    Route::post('/boxes/base/{box}', [BoxController::class, 'updateBase']);

    Route::get('/boxes/{box}/consecutiveAll', [BoxController::class, 'consecutiveAllByBox'])->middleware('can:box.index');
    Route::get('/boxes/{box}/getAssignUserByBox', [BoxController::class, 'getAssignUserByBox'])->middleware('can:box.index');
    Route::post('/boxes/{box}/toAssignUserByBox', [BoxController::class, 'toAssignUserByBox'])->middleware('can:box.store');
    Route::post('boxes/{id}/assign-vouchers', [BoxController::class, 'toAssignVouchers']);
    Route::get('boxes/{id}/assigned-vouchers', [BoxController::class, 'assignedVouchers']);



    Route::get('/zones/zone-list', [ZoneController::class, 'zoneList']);
    Route::resource('/zones', ZoneController::class);

    Route::get('/tables/table-list', [TableController::class, 'tableList']);
    Route::resource('/tables', TableController::class);

    //porciones
    Route::resource('/porciones',PortionController::class);
    Route::post('/porciones/{id}/activate', [PortionController::class, 'activate']);

    //porciones de productos
    Route::get('products/{product}', [ProductController::class, 'show'])
     ->where('product', '[0-9]+');
    Route::get('product-portions', [ProductPortionController::class, 'index']);
    Route::post('product-portions', [ProductPortionController::class, 'store']);
    Route::get('products/filter', [ProductController::class, 'filter']);
    Route::put('product-portions/{id}', [ProductPortionController::class, 'update']);
    Route::delete('product-portions/{id}', [ProductPortionController::class, 'destroy']);

    Route::get('portions', [PortionController::class, 'index']);
    Route::post('portions', [PortionController::class, 'store']);
    Route::put('portions/{id}', [PortionController::class, 'update']);
    Route::post('portions/{id}/changeStatus', [PortionController::class, 'changeStatus']);
    Route::get('portions/exportExcel', [PortionController::class, 'exportExcel']);
    Route::get('portions/{id}/histories', [PortionHistoryController::class, 'index']);

    //ordenes de eporciones
    Route::get('portion_orders', [PortionOrderController::class, 'index']);
    Route::post('portion_orders', [PortionOrderController::class, 'store']);
    Route::put('portion_orders/{id}', [PortionOrderController::class, 'update']);

    //tablas de locales de factus 
    Route::get('/organization-types', [OrganizationTypeController::class, 'index']);
    Route::get('/client-tributes', [ClientTributeController::class, 'index']);
    Route::get('/identity-document-types', [IdentityDocumentTypeController::class, 'index']);
    Route::get('/product-tributes', [ProductTributeController::class, 'index'])
    ->withoutMiddleware('auth:api');
    Route::get('measurement-units', [MeasurementUnitController::class, 'index'])
	->withoutMiddleware('auth:api');
    Route::get('product-identification-standards', [ProductIdentificationStandardController::class, 'index']);
    Route::get('/correction-codes', [CorrectionCodeController::class, 'index']);
    Route::get('/operation-types', [OperationTypeController::class, 'index']);
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('event-codes', [EventCodeController::class, 'index'])->withoutMiddleware('auth:api');


    //// Rutas para los tipos de comprobantes (numbering_range_document_types)
    // Se utiliza solo GET, ya que se usa para obtener la lista de tipos.
    Route::get('numbering_range_document_types', [NumberingRangeDocumentTypeController::class, 'index']);
    Route::get('payment_forms', [PaymentFormController::class, 'index']);
    Route::get('payment_forms/{id}', [PaymentFormController::class, 'show']); // opcional si deseas ver 1

    //creditos
    Route::apiResource('order-credits', OrderCreditController::class)->except(['update']);
    Route::post('order-credits/{credit}/payments', [OrderCreditController::class, 'addPayment']);
    Route::post('/orders/payCreditByClient', [OrderController::class, 'payCreditByClient']);

    // Rutas para los comprobantes (numbering_ranges)
    // Permiten listar, crear y actualizar los comprobantes.
    Route::get('numbering_ranges', [NumberingRangeController::class, 'index']);
    Route::get('/numbering-ranges/credit-notes', [NumberingRangeController::class, 'creditNotes']);
    Route::post('numbering_ranges', [NumberingRangeController::class, 'store'])->withoutMiddleware('auth:api');
    Route::put('numbering_ranges/{id}', [NumberingRangeController::class, 'update'])->withoutMiddleware('auth:api');
    Route::get('numbering_ranges/{id}', [NumberingRangeController::class, 'show'])->withoutMiddleware('auth:api');

    Route::get('vouchers', [VoucherController::class, 'index']);
    Route::get('vouchers/{id}', [VoucherController::class, 'show']);
    Route::post('vouchers', [VoucherController::class, 'store']);
    Route::put('vouchers/{id}', [VoucherController::class, 'update']);
    Route::delete('vouchers/{id}', [VoucherController::class, 'destroy']);

    Route::get('adjustment-note-reasons', [AdjustmentNoteReasonController::class, 'index'])->withoutMiddleware('auth:api');
    Route::get('adjustment-note-reasons/{id}', [AdjustmentNoteReasonController::class, 'show'])->withoutMiddleware('auth:api');

    // Opcional: Si necesitas una ruta para mostrar un comprobante en particular.
    Route::get('numbering_range_document_types', [NumberingRangeDocumentTypeController::class, 'index'])
    ->withoutMiddleware('auth:api');

    Route::get('numbering_ranges', [NumberingRangeController::class, 'index'])
    ->withoutMiddleware('auth:api');
    Route::post('/factustest', [FactusTestController::class, 'test'])->withoutMiddleware('auth:api');
    Route::post('/factustest2', [FactusTestController::class, 'testDirect'])->withoutMiddleware('auth:api');
    //notas credito 
    Route::post('/credit-notes/validate', [CreditNoteController::class, 'store'])->withoutMiddleware('auth:api');
    Route::get('credit-notes/numbering-ranges', [CreditNoteController::class, 'getNumberingRanges'])->withoutMiddleware('auth:api');
    Route::get('credit-notes/adjustment-note-reasons', [CreditNoteController::class, 'getAdjustmentNoteReasons'])->withoutMiddleware('auth:api');
    Route::get('credit-notes/operation-types', [CreditNoteController::class, 'getOperationTypes'])->withoutMiddleware('auth:api');

    //documento soporte
    
    Route::apiResource('support-document', SupportDocumentController::class)->withoutMiddleware('auth:api');
    Route::post('adjustment-notes', [AdjustmentNoteController::class, 'store']);

    //caja
    Route::get  ('cash-reconciliations/open',          [CashReconciliationController::class, 'open']);
    Route::post ('cash-reconciliations',               [CashReconciliationController::class, 'store']);
    Route::put  ('cash-reconciliations/{id}/close',    [CashReconciliationController::class, 'close']);
    Route::get  ('cash-reconciliations/closed',        [CashReconciliationController::class, 'closed']);
    Route::get  ('cash-reconciliations/session-report',[CashReconciliationController::class, 'sessionReport']);
    Route::get('cash-reconciliations/orders', [CashReconciliationController::class,'orders']);

    // Observaciones de productos
    Route::get('products/{product}/observations', [\App\Http\Controllers\ProductObservationController::class, 'index']);
    Route::post('products/{product}/observations', [\App\Http\Controllers\ProductObservationController::class, 'store']);
    Route::delete('products/observations/{observation}', [\App\Http\Controllers\ProductObservationController::class, 'destroy']);

>>>>>>> 0ed4468 (factuara electronica + reserva + caja)

});
