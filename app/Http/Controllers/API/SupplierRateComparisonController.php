<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SupplierRateComparisonController extends Controller
{
    // THIS IS SOUMIK CODE - Get products with same MRP but different purchase rates
    public function getProductsWithDifferentRates(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            
            $branchId = Auth::user()->branch_id;
            
            // THIS IS SOUMIK CODE - Simplified query to find products with different rates
            $query = "
                SELECT 
                    psr.item_name as product_name,
                    psr.mrp,
                    psr.purchase_price,
                    psr.batch_no,
                    psr.expiry_date,
                    pr.bill_no,
                    DATE_FORMAT(pr.receipt_date, '%d-%m-%Y') as bill_date,
                    p.account_title as supplier_name,
                    p.id as supplier_id,
                    COALESCE(s.qty, 0) as current_stock
                FROM pos_sub_receipts psr
                INNER JOIN pos_receipts pr ON psr.pos_receipt_id = pr.id
                INNER JOIN profilers p ON pr.profile_id = p.id
                LEFT JOIN stocks s ON psr.stock_id = s.id
                WHERE pr.type = 'PUR'
                AND pr.branch_id = ?
                AND psr.mrp > 0
                ORDER BY psr.item_name, psr.mrp, psr.purchase_price
            ";
            
            $results = DB::select($query, [$branchId]);
            
            // Group by product and MRP
            $groupedData = [];
            foreach ($results as $row) {
                $key = $row->product_name . '_' . $row->mrp;
                
                if (!isset($groupedData[$key])) {
                    $groupedData[$key] = [
                        'product_name' => $row->product_name,
                        'mrp' => $row->mrp,
                        'suppliers' => []
                    ];
                }
                
                $groupedData[$key]['suppliers'][] = [
                    'supplier_id' => $row->supplier_id,
                    'supplier_name' => $row->supplier_name,
                    'purchase_price' => $row->purchase_price,
                    'batch_no' => $row->batch_no,
                    'expiry_date' => $row->expiry_date,
                    'bill_no' => $row->bill_no,
                    'bill_date' => $row->bill_date,
                    'current_stock' => $row->current_stock ?? 0
                ];
            }
            
            // Filter only products with different rates
            $flaggedProducts = [];
            foreach ($groupedData as $data) {
                $uniqueRates = array_unique(array_column($data['suppliers'], 'purchase_price'));
                if (count($uniqueRates) > 1) {
                    // Calculate min and max rates
                    $rates = array_column($data['suppliers'], 'purchase_price');
                    $data['min_rate'] = min($rates);
                    $data['max_rate'] = max($rates);
                    $data['rate_difference'] = $data['max_rate'] - $data['min_rate'];
                    $data['rate_difference_percent'] = ($data['rate_difference'] / $data['min_rate']) * 100;
                    
                    $flaggedProducts[] = $data;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $flaggedProducts,
                'total_flagged' => count($flaggedProducts)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
