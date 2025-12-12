<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnVouchers;
use App\Models\Profiler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ExpiryReturnReportController extends Controller
{
    //this is soumik code - Get all suppliers from return_vouchers (removed auth dependency)
    public function getSuppliers(Request $request)
    {
        try {
            $suppliers = ReturnVouchers::select(
                'return_vouchers.supplier_id',
                'profilers.account_title as name'
            )
            ->leftJoin('profilers', 'return_vouchers.supplier_id', '=', 'profilers.id')
            ->whereNotNull('return_vouchers.supplier_id')
            ->groupBy('return_vouchers.supplier_id', 'profilers.account_title')
            ->orderBy('profilers.account_title')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $suppliers->map(function($supplier) {
                    return [
                        'id' => $supplier->supplier_id,
                        'name' => $supplier->name ?? 'Unknown Supplier'
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching suppliers: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get expired return products by supplier
    public function getExpiryReturnReport(Request $request)
    {
        try {
            $supplierId = $request->input('supplier_id');
            
            if (!$supplierId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier ID is required'
                ], 400);
            }

            // THIS IS SOUMIK CODE - Group by supplier and date instead of individual bills
            $returnVouchers = ReturnVouchers::select(
                'return_vouchers.*',
                'profilers.account_title as supplier_name'
            )
            ->leftJoin('profilers', 'return_vouchers.supplier_id', '=', 'profilers.id')
            ->where('return_vouchers.supplier_id', $supplierId)
            ->orderBy('return_vouchers.created_at', 'desc')
            ->get();

            $groupedBills = [];
            $supplierName = '';
            $totalProducts = 0;
            
            foreach ($returnVouchers as $voucher) {
                if (empty($supplierName)) {
                    $supplierName = $voucher->supplier_name ?? 'Unknown Supplier';
                }
                
                // Group by date instead of bill_no
                $dateKey = date('Y-m-d', strtotime($voucher->created_at));
                $billNo = 'EXP-RET-' . date('Ymd', strtotime($voucher->created_at));
                
                if (!isset($groupedBills[$dateKey])) {
                    $groupedBills[$dateKey] = [
                        'bill_no' => $billNo,
                        'bill_date' => date('d-m-Y', strtotime($voucher->created_at)),
                        'total_products' => 0,
                        'total_value' => 0,
                        'products' => []
                    ];
                }
                
                $productValue = ($voucher->ret_quantity ?? 0) * ($voucher->purchase_price ?? 0);
                
                // THIS IS SOUMIK CODE - Get MRP from stocks table with session/sanctum auth
                $user = auth()->user() ?? auth('sanctum')->user();
                $branchId = $user ? $user->branch_id : 1;
                $stockMrp = DB::table('stocks')
                    ->where('product_name', $voucher->product_name)
                    ->where('batch_no', $voucher->batch_no)
                    ->where('branch_id', $branchId)
                    ->value('mrp') ?? 0;
                
                //this is soumik code - using date key for grouping
                $groupedBills[$dateKey]['products'][] = [
                    'id' => $voucher->id,
                    'product_name' => $voucher->product_name,
                    'batch_no' => $voucher->batch_no,
                    'expiry_date' => Carbon::parse($voucher->exp_date)->format('d-m-Y'),
                    'qty' => $voucher->ret_quantity ?? 0,
                    'purchase_price' => $voucher->purchase_price ?? 0,
                    'mrp' => $stockMrp,
                    'total_value' => $productValue
                ];
                
                $groupedBills[$dateKey]['total_products']++;
                $groupedBills[$dateKey]['total_value'] += $productValue;
                $totalProducts++;
            }
            
            // Remove bills with zero total value
            $groupedBills = array_filter($groupedBills, function($bill) {
                return $bill['total_value'] > 0;
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'supplier_name' => $supplierName,
                    'bills' => array_values($groupedBills)
                ],
                'total_products' => $totalProducts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching expiry return report: ' . $e->getMessage()
            ], 500);
        }
    }

    //this is soumik code - update expiry return record and sync with stock table
    public function updateExpiryReturn(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer',
                'product_name' => 'required|string',
                'batch_no' => 'required|string',
                'ret_quantity' => 'required|numeric|min:0',
                'purchase_price' => 'required|numeric|min:0',
                'exp_date' => 'required|date'
            ]);

            DB::beginTransaction();

            $returnVoucher = ReturnVouchers::find($request->id);
            
            if (!$returnVoucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return voucher not found'
                ], 404);
            }

            // Get the difference in quantity to update stock
            $oldQuantity = $returnVoucher->ret_quantity ?? 0;
            $newQuantity = $request->ret_quantity;
            $quantityDifference = $newQuantity - $oldQuantity;

            // Update return voucher
            $returnVoucher->update([
                'product_name' => $request->product_name,
                'batch_no' => $request->batch_no,
                'ret_quantity' => $request->ret_quantity,
                'purchase_price' => $request->purchase_price,
                'exp_date' => $request->exp_date
            ]);

            // Update stock table - adjust stock based on return quantity changes
            //this is soumik code - added fallback for branch_id
            $branchId = Auth::check() ? Auth::user()->branch_id : 1;
            $stock = DB::table('stocks')
                ->where('product_name', $request->product_name)
                ->where('batch_no', $request->batch_no)
                ->where('branch_id', $branchId)
                ->first();

            if ($stock) {
                // Calculate stock adjustment in units (not strips)
                // Return quantity is in packs/units, stock qty is also in units
                $stockAdjustment = ($oldQuantity - $newQuantity) * $stock->strip_size;
                $newStockQty = $stock->qty + $stockAdjustment;
                
                // Ensure stock doesn't go negative
                $newStockQty = max(0, $newStockQty);
                
                DB::table('stocks')
                    ->where('id', $stock->id)
                    ->update(['qty' => $newStockQty]);
                    
                error_log('Stock updated: Product=' . $request->product_name . ', Batch=' . $request->batch_no . ', Old Stock=' . $stock->qty . ', New Stock=' . $newStockQty . ', Old Return Qty=' . $oldQuantity . ', New Return Qty=' . $newQuantity . ', Stock Adjustment=' . $stockAdjustment);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expiry return record and stock updated successfully',
                'data' => $returnVoucher
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating expiry return record: ' . $e->getMessage()
            ], 500);
        }
    }

    //this is soumik code - delete expiry return record and restore stock
    public function deleteExpiryReturn(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer'
            ]);

            DB::beginTransaction();

            $returnVoucher = ReturnVouchers::find($request->id);
            
            if (!$returnVoucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return voucher not found'
                ], 404);
            }

            // Restore stock quantity when deleting return record
            //this is soumik code - added fallback for branch_id
            $branchId = Auth::check() ? Auth::user()->branch_id : 1;
            $stock = DB::table('stocks')
                ->where('product_name', $returnVoucher->product_name)
                ->where('batch_no', $returnVoucher->batch_no)
                ->where('branch_id', $branchId)
                ->first();

            if ($stock) {
                // Restore stock in units (return qty * strip_size)
                $restoreAmount = ($returnVoucher->ret_quantity ?? 0) * $stock->strip_size;
                $restoredQty = $stock->qty + $restoreAmount;
                
                DB::table('stocks')
                    ->where('id', $stock->id)
                    ->update(['qty' => $restoredQty]);
                    
                error_log('Stock restored on delete: Product=' . $returnVoucher->product_name . ', Batch=' . $returnVoucher->batch_no . ', Old Stock=' . $stock->qty . ', Restored Stock=' . $restoredQty . ', Return Qty=' . $returnVoucher->ret_quantity . ', Restore Amount=' . $restoreAmount);
            }

            $returnVoucher->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expiry return record deleted and stock restored successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting expiry return record: ' . $e->getMessage()
            ], 500);
        }
    }

    //this is soumik code - get single expiry return record for editing
    public function getExpiryReturn(Request $request, $id)
    {
        try {
            $returnVoucher = ReturnVouchers::select(
                'return_vouchers.*',
                'profilers.account_title as supplier_name'
            )
            ->leftJoin('profilers', 'return_vouchers.supplier_id', '=', 'profilers.id')
            ->where('return_vouchers.id', $id)
            ->first();
            
            if (!$returnVoucher) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return voucher not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $returnVoucher
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching expiry return record: ' . $e->getMessage()
            ], 500);
        }
    }

    //this is soumik code - bulk delete expiry return records
    public function bulkDeleteExpiryReturn(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer'
            ]);

            DB::beginTransaction();

            $deletedCount = 0;
            $errors = [];

            foreach ($request->ids as $id) {
                $returnVoucher = ReturnVouchers::find($id);
                
                if (!$returnVoucher) {
                    $errors[] = "Return voucher with ID {$id} not found";
                    continue;
                }

                // Restore stock quantity when deleting return record
                //this is soumik code - added fallback for branch_id in bulk delete
                $branchId = Auth::check() ? Auth::user()->branch_id : 1;
                $stock = DB::table('stocks')
                    ->where('product_name', $returnVoucher->product_name)
                    ->where('batch_no', $returnVoucher->batch_no)
                    ->where('branch_id', $branchId)
                    ->first();

                if ($stock) {
                    // Restore stock in units (return qty * strip_size)
                    $restoreAmount = ($returnVoucher->ret_quantity ?? 0) * $stock->strip_size;
                    $restoredQty = $stock->qty + $restoreAmount;
                    
                    DB::table('stocks')
                        ->where('id', $stock->id)
                        ->update(['qty' => $restoredQty]);
                        
                    error_log('Stock restored on delete: Product=' . $returnVoucher->product_name . ', Batch=' . $returnVoucher->batch_no . ', Old Stock=' . $stock->qty . ', Restored Stock=' . $restoredQty . ', Return Qty=' . $returnVoucher->ret_quantity . ', Restore Amount=' . $restoreAmount);
                }

                $returnVoucher->delete();
                $deletedCount++;
            }

            DB::commit();

            $message = "Successfully deleted {$deletedCount} records";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error in bulk delete: ' . $e->getMessage()
            ], 500);
        }
    }
}