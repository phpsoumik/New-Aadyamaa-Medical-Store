<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\OptionTagsController;
use App\Http\Controllers\API\BranchController;
use App\Http\Controllers\API\BankController;
use App\Http\Controllers\API\ChartAccountController;
use App\Http\Controllers\API\SaleServiceController;
use App\Http\Controllers\API\PrinterController;
use App\Http\Controllers\API\PrinterReceiptController;
use App\Http\Controllers\API\RequestedItemController;
use App\Http\Controllers\API\ProfilerController;
use App\Http\Controllers\API\VoucherController;
use App\Http\Controllers\API\BankTransactionController;
use App\Http\Controllers\API\UserBalanceController;
use App\Http\Controllers\API\OpenHeadController;
use App\Http\Controllers\API\ReceiptController;
use App\Http\Controllers\API\StockController;
use App\Http\Controllers\API\PosController;
use App\Http\Controllers\API\PaymentMethodController;
use App\Http\Controllers\API\UserPrivilegesController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\StoreReportController;
use App\Http\Controllers\API\SmsSettingController;
use App\Http\Controllers\API\ExpiryReturnReportController;
use App\Http\Controllers\API\SupplierRateComparisonController;


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::get('check_new_installation', [UserController::class, 'checkNewInstallation']);
Route::get('get_currency', [UserController::class, 'getCurrency']);
Route::post('install_app', [UserController::class, 'installApp']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::post('search_users', [UserController::class, 'searchUser'])->middleware('auth:sanctum');
Route::get('active_branch_detail', [UserController::class, 'activeBranchUser'])->middleware('auth:sanctum');

//RETURN VOUCHER ROUTES

Route::post('save_return_voucher', [StoreReportController::class, 'saveReturnVouchers'])->middleware(['auth:sanctum','permission:Option Tags']);

//OPTIONS ROUTES
Route::post('options_list', [OptionTagsController::class, 'index'])->middleware(['auth:sanctum','permission:Option Tags']);
Route::post('add_option', [OptionTagsController::class, 'store'])->middleware(['auth:sanctum','permission:Option Tags']);
Route::get('get_option/{id}', [OptionTagsController::class, 'show'])->middleware(['auth:sanctum','permission:Option Tags']);
Route::post('edit_option', [OptionTagsController::class, 'update'])->middleware(['auth:sanctum','permission:Option Tags']);
Route::post('delete_option', [OptionTagsController::class, 'delete'])->middleware(['auth:sanctum','permission:Option Tags']);


//PAYMENT NAME
Route::post('payment_method_list', [PaymentMethodController::class, 'index'])->middleware(['auth:sanctum','permission:Payment Methods']);
Route::post('add_payment_method', [PaymentMethodController::class, 'store'])->middleware(['auth:sanctum','permission:Payment Methods']);
Route::get('get_payment_method/{id}', [PaymentMethodController::class, 'show'])->middleware(['auth:sanctum','permission:Payment Methods']);
Route::post('edit_payment_method', [PaymentMethodController::class, 'update'])->middleware(['auth:sanctum','permission:Payment Methods']);
Route::post('delete_payment_method', [PaymentMethodController::class, 'delete'])->middleware(['auth:sanctum','permission:Payment Methods']);
Route::get('payment_method_list', [PaymentMethodController::class, 'methods'])->middleware(['auth:sanctum','permission:Payment Methods']);

//USER ROUTES
Route::post('user_list', [UserController::class, 'index'])->middleware(['auth:sanctum','permission:Users']);
Route::post('delete_user', [UserController::class, 'delete'])->middleware(['auth:sanctum','permission:Users']);
Route::post('change_pin', [UserController::class, 'changePin'])->middleware(['auth:sanctum','permission:Users']);
Route::get('get_user/{id}', [UserController::class, 'show'])->middleware(['auth:sanctum','permission:Users']);
Route::post('save_user', [UserController::class, 'store'])->middleware(['auth:sanctum','permission:Users']);
Route::post('edit_user', [UserController::class, 'update'])->middleware(['auth:sanctum','permission:Users']);
Route::post('upload_user_image', [UserController::class, 'imageUploadPost'])->middleware(['auth:sanctum','permission:Users']);

//STORES ROUTES
Route::post('store_list', [BranchController::class, 'index'])->middleware(['auth:sanctum','permission:Branches']);
Route::post('add_store', [BranchController::class, 'store'])->middleware(['auth:sanctum','permission:Branches']);
Route::get('get_store/{id}', [BranchController::class, 'show'])->middleware(['auth:sanctum','permission:Branches']);
Route::post('edit_store', [BranchController::class, 'update'])->middleware(['auth:sanctum','permission:Branches']);
Route::post('delete_store', [BranchController::class, 'delete'])->middleware(['auth:sanctum','permission:Branches']);

//Bank ROUTES
Route::post('bank_list', [BankController::class, 'index'])->middleware(['auth:sanctum','permission:Banks']);
Route::post('add_bank', [BankController::class, 'store'])->middleware(['auth:sanctum','permission:Banks']);
Route::get('get_bank/{id}', [BankController::class, 'show'])->middleware(['auth:sanctum','permission:Banks']);
Route::post('edit_bank', [BankController::class, 'update'])->middleware(['auth:sanctum','permission:Banks']);
Route::post('delete_bank', [BankController::class, 'delete'])->middleware(['auth:sanctum','permission:Banks']);
Route::get('get_all_bank', [BankController::class, 'get_all_bank'])->middleware(['auth:sanctum','permission:Banks']);


//Chart of Accounts ROUTES
Route::post('chart_accounts_list', [ChartAccountController::class, 'index'])->middleware(['auth:sanctum','permission:Chart Of Accounts']);
Route::post('add_chart_accounts', [ChartAccountController::class, 'store'])->middleware(['auth:sanctum','permission:Chart Of Accounts']);
Route::get('get_chart_accounts/{id}', [ChartAccountController::class, 'show'])->middleware(['auth:sanctum','permission:Chart Of Accounts']);
Route::post('edit_chart_accounts', [ChartAccountController::class, 'update'])->middleware(['auth:sanctum','permission:Chart Of Accounts']);
Route::post('delete_chart_accounts', [ChartAccountController::class, 'delete'])->middleware(['auth:sanctum','permission:Chart Of Accounts']);
Route::post('search_account_heads', [ChartAccountController::class, 'searchAccountHeads'])->middleware('auth:sanctum');


//Sale Services ROUTES
Route::post('sale_services_list', [SaleServiceController::class, 'index'])->middleware(['auth:sanctum','permission:Sale Services']);
Route::post('add_sale_services', [SaleServiceController::class, 'store'])->middleware(['auth:sanctum','permission:Sale Services']);
Route::get('get_sale_services/{id}', [SaleServiceController::class, 'show'])->middleware(['auth:sanctum','permission:Sale Services']);
Route::post('edit_sale_services', [SaleServiceController::class, 'update'])->middleware(['auth:sanctum','permission:Sale Services']);
Route::post('delete_sale_services', [SaleServiceController::class, 'delete'])->middleware(['auth:sanctum','permission:Sale Services']);


//Printer ROUTES 
Route::post('printer_list', [PrinterController::class, 'index'])->middleware(['auth:sanctum','permission:Printer Setup']);
Route::post('add_printer', [PrinterController::class, 'store'])->middleware(['auth:sanctum','permission:Printer Setup']);
Route::get('get_printer/{id}', [PrinterController::class, 'show'])->middleware(['auth:sanctum','permission:Printer Setup']);
Route::post('edit_printer', [PrinterController::class, 'update'])->middleware(['auth:sanctum','permission:Printer Setup']);
Route::post('delete_printer', [PrinterController::class, 'delete'])->middleware(['auth:sanctum','permission:Printer Setup']);


//Printer Receipt ROUTES
Route::post('printer_receipt_list', [PrinterReceiptController::class, 'index'])->middleware(['auth:sanctum','permission:Receipt Content']);
Route::post('add_printer_receipt', [PrinterReceiptController::class, 'store'])->middleware(['auth:sanctum','permission:Receipt Content']);
Route::get('get_printer_receipt/{id}', [PrinterReceiptController::class, 'show'])->middleware(['auth:sanctum','permission:Receipt Content']);
Route::post('edit_printer_receipt', [PrinterReceiptController::class, 'update'])->middleware(['auth:sanctum','permission:Receipt Content']);
Route::post('delete_printer_receipt', [PrinterReceiptController::class, 'delete'])->middleware(['auth:sanctum','permission:Receipt Content']);



//SMS SETTINGS ROUTES

//EXPIRY RETURN REPORT ROUTES[this is soumik code ]
//this is soumik code - back to auth:sanctum for user's machine compatibility
Route::get('suppliers', [ExpiryReturnReportController::class, 'getSuppliers'])->middleware(['auth:sanctum']);
Route::post('expiry_return_report', [ExpiryReturnReportController::class, 'getExpiryReturnReport'])->middleware(['auth:sanctum']);

//this is soumik code - POS search items route for sale page
Route::post('search_items', [StockController::class, 'searchItems'])->middleware(['auth:sanctum']);
//this is soumik code - get batch variations for product selection
Route::post('get_batch_variations', [PosController::class, 'getBatchVariations'])->middleware(['auth:sanctum']);
//this is soumik code - new routes for edit and delete expiry return records
Route::get('expiry_return/{id}', [ExpiryReturnReportController::class, 'getExpiryReturn'])->middleware(['auth:sanctum']);
Route::post('update_expiry_return', [ExpiryReturnReportController::class, 'updateExpiryReturn'])->middleware(['auth:sanctum']);
Route::post('delete_expiry_return', [ExpiryReturnReportController::class, 'deleteExpiryReturn'])->middleware(['auth:sanctum']);
Route::post('bulk_delete_expiry_return', [ExpiryReturnReportController::class, 'bulkDeleteExpiryReturn'])->middleware(['auth:sanctum']);

Route::post('sms_settings_list', [SmsSettingController::class, 'index'])->middleware(['auth:sanctum','permission:Sms Settings']);
Route::post('add_sms_settings', [SmsSettingController::class, 'store'])->middleware(['auth:sanctum','permission:Sms Settings']);
Route::get('get_sms_settings/{id}', [SmsSettingController::class, 'show'])->middleware(['auth:sanctum','permission:Sms Settings']);
Route::post('edit_sms_settings', [SmsSettingController::class, 'update'])->middleware(['auth:sanctum','permission:Sms Settings']);
Route::post('delete_sms_settings', [SmsSettingController::class, 'delete'])->middleware(['auth:sanctum','permission:Sms Settings']);


//Requested Items ROUTES
Route::post('requested_items_list', [RequestedItemController::class, 'index'])->middleware(['auth:sanctum','permission:Requested Items']);
Route::post('add_requested_items', [RequestedItemController::class, 'store'])->middleware(['auth:sanctum','permission:Requested Items']);
Route::get('get_requested_items/{id}', [RequestedItemController::class, 'show'])->middleware(['auth:sanctum','permission:Requested Items']);
Route::post('edit_requested_items', [RequestedItemController::class, 'update'])->middleware(['auth:sanctum','permission:Requested Items']);
Route::post('delete_requested_items', [RequestedItemController::class, 'delete'])->middleware(['auth:sanctum','permission:Requested Items']);
Route::post('search_medicine', [RequestedItemController::class, 'searchMedicine'])->middleware(['auth:sanctum','permission:Requested Items']);
Route::post('get_customer_phone', [RequestedItemController::class, 'getCustomerPhone'])->middleware(['auth:sanctum','permission:Requested Items']);
Route::post('store_customer', [RequestedItemController::class, 'storeCustomer'])->middleware(['auth:sanctum','permission:Requested Items']);


//Profilers ROUTES
Route::post('profilers_list', [ProfilerController::class,'index'])->middleware(['auth:sanctum','permission:Profilers']);
Route::post('add_profilers', [ProfilerController::class,'store'])->middleware(['auth:sanctum','permission:Profilers']);
Route::get('get_profilers/{id}', [ProfilerController::class, 'show'])->middleware(['auth:sanctum','permission:Profilers']);
Route::post('edit_profilers', [ProfilerController::class,'update'])->middleware(['auth:sanctum','permission:Profilers']);
Route::post('delete_profilers', [ProfilerController::class,'delete'])->middleware(['auth:sanctum','permission:Profilers']);
Route::post('search_profilers', [ProfilerController::class,'searchProfile'])->middleware(['auth:sanctum','permission:Profilers']);
// THIS IS SOUMIK CODE - Route for searching only suppliers
Route::post('search_suppliers', [ProfilerController::class,'searchSuppliers'])->middleware('auth:sanctum');
Route::get('supplier_rate_comparison', [SupplierRateComparisonController::class, 'getProductsWithDifferentRates'])->middleware('auth:sanctum');


//Voucher ROUTES
Route::post('voucher_list',[VoucherController::class,'index'])->middleware(['auth:sanctum','permission:Journal Voucher','permission:Other Voucher']);
Route::post('add_voucher',[VoucherController::class,'store'])->middleware(['auth:sanctum','permission:Journal Voucher','permission:Other Voucher']);
Route::get('get_voucher/{id}',[VoucherController::class, 'show'])->middleware(['auth:sanctum','permission:Journal Voucher','permission:Other Voucher']);
Route::post('edit_voucher', [VoucherController::class,'update'])->middleware(['auth:sanctum','permission:Journal Voucher','permission:Other Voucher']);
Route::get('get_filter_list',[VoucherController::class,'filterLists'])->middleware('auth:sanctum');
Route::post('get_voucher_receipt',[VoucherController::class,'getVoucherReceipt'])->middleware('auth:sanctum');


//BANK TRANSACTIONS ROUTES
Route::post('bank_transaction_list',[BankTransactionController::class,'index'])->middleware(['auth:sanctum','permission:Banking']);
Route::post('add_bank_transaction',[BankTransactionController::class,'store'])->middleware(['auth:sanctum','permission:Banking']);
Route::get('get_bank_transaction/{id}',[BankTransactionController::class, 'show'])->middleware(['auth:sanctum','permission:Banking']);
Route::post('edit_bank_transaction', [BankTransactionController::class,'update'])->middleware(['auth:sanctum','permission:Banking']);
Route::post('get_bank_transaction_receipt',[BankTransactionController::class,'getTransactionReceipt'])->middleware(['auth:sanctum','permission:Banking']);
Route::post('edit_bank_activity', [BankTransactionController::class, 'update_activity'])->middleware(['auth:sanctum','permission:Banking']);


//User Balance ROUTES
Route::post('balances_list',[UserBalanceController::class,'index'])->middleware(['auth:sanctum','permission:User Balance']);
Route::post('add_balance',[UserBalanceController::class,'store'])->middleware(['auth:sanctum','permission:User Balance']);
Route::get('get_balance/{id}',[UserBalanceController::class, 'show'])->middleware(['auth:sanctum','permission:User Balance']);
Route::post('edit_balance', [UserBalanceController::class,'update'])->middleware(['auth:sanctum','permission:User Balance']);

//Open Head ROUTES
Route::post('account_head_list', [OpenHeadController::class, 'accountHeadList'])->middleware(['auth:sanctum','permission:Open Head']);
Route::post('save_open_head', [OpenHeadController::class, 'store'])->middleware(['auth:sanctum','permission:Open Head']);

//RECEIPT ROUTES
Route::post('receipt_transaction_list',[ReceiptController::class,'index'])->middleware(['auth:sanctum','permission:Transaction Receipt']);
Route::post('add_receipt_transaction',[ReceiptController::class,'store'])->middleware(['auth:sanctum','permission:Transaction Receipt']);
Route::get('get_receipt_transaction/{id}',[ReceiptController::class, 'show'])->middleware(['auth:sanctum','permission:Transaction Receipt']);
Route::post('edit_receipt_transaction', [ReceiptController::class,'update'])->middleware(['auth:sanctum','permission:Transaction Receipt']);
Route::post('get_accounting_receipt',[ReceiptController::class,'getTransactionReceipt'])->middleware(['auth:sanctum','permission:Transaction Receipt']);
Route::post('search_receipt_transaction',[ReceiptController::class,'searchReceiptTransaction'])->middleware(['auth:sanctum','permission:Transaction Receipt']);
Route::post('save_receipt_payment',[ReceiptController::class,'saveReceiptTransaction'])->middleware(['auth:sanctum','permission:Transaction Receipt']);

Route::get('get_import_stock_list',[StockController::class,'index'])->middleware(['auth:sanctum','permission:Import Stock']);
Route::get('get_stock/{id}',[StockController::class, 'show'])->middleware(['auth:sanctum','permission:Stocks']);
Route::post('export_sample_stock',[StockController::class,'export'])->middleware(['auth:sanctum','permission:Import Stock']);
Route::post('import_sample_stock', [StockController::class, 'importStock'])->middleware(['auth:sanctum','permission:Import Stock']);
Route::post('save_csv_data', [StockController::class, 'saveStock'])->middleware(['auth:sanctum','permission:Import Stock']);

//sam
Route::post('save_new_item', [StockController::class, 'saveNewItem'])->middleware(['auth:sanctum','permission:Import Stock']);
Route::post('add_temp_stock', [StockController::class, 'addTempStock'])->middleware(['auth:sanctum','permission:Import Stock']);

Route::post('stock_list', [StockController::class, 'stockList'])->middleware(['auth:sanctum','permission:Stocks']);
Route::post('update_stock', [StockController::class, 'update'])->middleware(['auth:sanctum','permission:Stocks']);


//POS
//this is soumik code - removed duplicate search_items route, using PosController instead
//this is soumik code - new route for getting product variations
 Route::post('get_product_variations', [StockController::class, 'getProductVariations'])->middleware('auth:sanctum');
Route::get('pos_initialization', [PosController::class, 'index'])->middleware(['auth:sanctum','permission:Orders']);
Route::post('save_pos_receipt', [PosController::class, 'saveReceipt'])->middleware(['auth:sanctum','permission:Orders']);
Route::post('search_pos_receipt', [PosController::class, 'searchReceipt'])->middleware('auth:sanctum');
//sam
Route::post('update_pos_receipt', [PosController::class, 'updateReceipt'])->middleware(['auth:sanctum','permission:Orders']);

Route::post('search_purchase_receipt', [PosController::class, 'searchPurchaseReceipt'])->middleware(['auth:sanctum','permission:Purchasing']);
Route::post('save_purchase_receipt', action: [PosController::class, 'savePurchaseReceipt'])->middleware(['auth:sanctum','permission:Purchasing']);
Route::post('update_purchase_receipt', action: [PosController::class, 'updatePurchaseReceipt'])->middleware(['auth:sanctum','permission:Purchasing']);
Route::post('pos_transaction_list',[PosController::class,'transactions'])->middleware(['auth:sanctum','permission:Orders']);

Route::post('pos_payments',[PosController::class,'posPayments'])->middleware('auth:sanctum');
Route::post('stock_transfer_status',[PosController::class,'stockLeft'])->middleware(['auth:sanctum','permission:Orders']);
Route::post('save_transfer_stock',[PosController::class,'stockSaved'])->middleware(['auth:sanctum','permission:Orders']);
Route::post('transaction_void',[PosController::class,'voidStock'])->middleware(['auth:sanctum','permission:Transaction Receipt']);
Route::post('get_pos_receipt',[PosController::class,'getPosReceipt'])->middleware(['auth:sanctum','permission:Transaction Receipt']);


//PRIVILEGES LIST
Route::post('privileges_list',[UserPrivilegesController::class,'index'])->middleware(['auth:sanctum','permission:Privileges']);
Route::post('save_privileges',[UserPrivilegesController::class,'store'])->middleware(['auth:sanctum','permission:Privileges']);


//HOMEPAGE
Route::post('dashboard_information',[HomeController::class,'index'])->middleware(['auth:sanctum','permission:Dashboard']);

//REPORTS
Route::get('reports_filter_list',[StoreReportController::class,'index'])->middleware(['auth:sanctum']);
Route::post('sales_return_report',[StoreReportController::class,'saleReturn'])->middleware(['auth:sanctum','permission:Sales/Refund Report']);
Route::post('transfer_report',[StoreReportController::class,'transfer'])->middleware(['auth:sanctum','permission:Transfer Stock Report']);
Route::post('purchasing_report',[StoreReportController::class,'purchasing'])->middleware(['auth:sanctum','permission:Purchasing Report']);
Route::post('performance_report',[StoreReportController::class,'performance'])->middleware(['auth:sanctum','permission:Performance Report']);
Route::post('general_journal',[StoreReportController::class,'generalJournal'])->middleware(['auth:sanctum','permission:General Journal']);
Route::post('income_statement',[StoreReportController::class,'incomeStatement'])->middleware(['auth:sanctum','permission:Income Statement']);
Route::post('trial_balance',[StoreReportController::class,'trialBalance'])->middleware(['auth:sanctum','permission:Trial Balance']);
Route::post('ledger_statement',[StoreReportController::class,'ledgerStatement'])->middleware(['auth:sanctum','permission:Ledger Statement']);
Route::post('tax_report',[StoreReportController::class,'taxReport'])->middleware(['auth:sanctum','permission:Tax Report']);
Route::post('bank_statement',[StoreReportController::class,'bankStatement'])->middleware(['auth:sanctum','permission:Bank Reconciliation']);
Route::post('account_statement',[StoreReportController::class,'accountStatement'])->middleware(['auth:sanctum','permission:Account Statement']);
Route::post('expiry_report',[StoreReportController::class,'stockExpiryReport'])->middleware(['auth:sanctum','permission:Stock Expiry Report']);
Route::post('stock_report',[StoreReportController::class,'stockReport'])->middleware(['auth:sanctum','permission:Stock Report']);
Route::post('stock_alert_report',[StoreReportController::class,'stockAlertReport'])->middleware(['auth:sanctum','permission:Stock Alert Report']);
Route::post('user_activity_report',[StoreReportController::class,'userActivityReport'])->middleware(['auth:sanctum','permission:Activity Report']);
Route::post('user_report',[StoreReportController::class,'userReport'])->middleware(['auth:sanctum','permission:User Report']);


// THIS IS SOUMIK CODE - 28-11-2025
Route::post('non_moving_items_report', [StoreReportController::class, 'nonMovingItemsReport'])->middleware('auth:sanctum');
// THIS IS SOUMIK CODE - 28-11-2025
Route::post('search_product_names', [StoreReportController::class, 'searchProductNames'])->middleware('auth:sanctum');
Route::post('product_sale_history', [StoreReportController::class, 'productSaleHistory'])->middleware('auth:sanctum');
Route::post('top_selling_products', [StoreReportController::class, 'topSellingProducts'])->middleware('auth:sanctum');
Route::get('get_brands', [StoreReportController::class, 'getBrands'])->middleware('auth:sanctum');

// THIS IS SOUMIK CODE - Bulk Order Voucher (29-11-2025)
Route::post('bulk_order_voucher_data', [StoreReportController::class, 'getBulkOrderVoucherData'])->middleware('auth:sanctum');
