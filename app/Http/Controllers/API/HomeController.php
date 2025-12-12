<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Stock;

class HomeController extends Controller
{
	public function index(Request $request)
	{
		if($request->storeID == 0)
		{
			$request->storeID  = Auth::user()->branch_id;
		}
        
        $first_date = date('Y-m-d',strtotime('first day of this month'));
        $last_date = date('Y-m-d',strtotime('last day of this month'));

        $store_info = DB::table('branches')
        ->where('id','=',$request->storeID)
        ->get()->first();

        $shortage_qty = DB::table('stocks')
        ->where('branch_id','=',$request->storeID)
        ->where('qty','<','min_stock')
        ->count();
        
        $account_holders = DB::table('profilers')
        ->where('account_type','!=','Default Customer')
        ->count();
        
        $expense_today = DB::table('transactions')
        ->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
        ->where('transactions.branch_id','=',$request->storeID)
        ->where('transactions.generated_source','=','EXP')
        ->whereDate('transactions.created_at','=',date('Y-m-d'))
        ->where('sub_transactions.type','=','Credit')
        ->sum('sub_transactions.amount');
        
        $expense_month = DB::table('transactions')
        ->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
        ->where('transactions.branch_id','=',$request->storeID)
        ->where('transactions.generated_source','=','EXV')
        ->whereDate('transactions.created_at','>=',$first_date)
        ->whereDate('transactions.created_at','<=',$last_date)
        ->where('sub_transactions.type','=','Credit')
        ->sum('sub_transactions.amount');

        $items_qty = DB::table('stocks')
        ->where('status','=','Active')
        ->where('branch_id','=',$request->storeID)
        ->count();
        
        $sales_qty = DB::table('pos_receipts')
        ->where('type','=','INE')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->count();

        $sales_today = DB::table('pos_receipts')
        ->where('type','=','INE')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','=',date('Y-m-d'))
        ->sum('total_bill');
        
        $sales_month = DB::table('pos_receipts')
        ->where('type','=','INE')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','>=',$first_date)
        ->whereDate('created_at','<=',$last_date)
        ->sum('total_bill');

        $return_today = DB::table('pos_receipts')
        ->where('type','=','RFD')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','=',date('Y-m-d'))
        ->sum('total_bill');
        
        $return_month = DB::table('pos_receipts')
        ->where('type','=','RFD')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','>=',$first_date)
        ->whereDate('created_at','<=',$last_date)
        ->sum('total_bill');

        $purchase_today = DB::table('pos_receipts')
        ->where('type','=','PUR')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','=',date('Y-m-d'))
        ->sum('total_bill');
        
        $purchase_month = DB::table('pos_receipts')
        ->where('type','=','PUR')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','>=',$first_date)
        ->whereDate('created_at','<=',$last_date)
        ->sum('total_bill');
        
        $pur_return_today = DB::table('pos_receipts')
        ->where('type','=','RPU')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','=',date('Y-m-d'))
        ->sum('total_bill');
        
        $pur_return_month = DB::table('pos_receipts')
        ->where('type','=','RPU')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','>=',$first_date)
        ->whereDate('created_at','<=',$last_date)
        ->sum('total_bill');

        $transfer_today = DB::table('pos_receipts')
        ->where('type','=','TRN')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','=',date('Y-m-d'))
        ->sum('total_bill');
        
        $transfer_month = DB::table('pos_receipts')
        ->where('type','=','TRN')
        ->where('branch_id','=',$request->storeID)
        ->where('status','=','Active')
        ->whereDate('created_at','>=',$first_date)
        ->whereDate('created_at','<=',$last_date)
        ->sum('total_bill');

        $cash_today = DB::table('transactions')
        ->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
        ->where('sub_transactions.account_id','=',2)
        ->whereDate('sub_transactions.created_at','=',date('Y-m-d'))
        ->where('sub_transactions.type','=','Debit')
        ->where('transactions.branch_id','=',$request->storeID)
        ->sum('sub_transactions.amount');
        
        $cash_month = DB::table('transactions')
        ->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
        ->where('sub_transactions.account_id','=',2)
        ->whereDate('sub_transactions.created_at','>=',$first_date)
        ->whereDate('sub_transactions.created_at','<=',$last_date)
        ->where('sub_transactions.type','=','Debit')
        ->where('transactions.branch_id','=',$request->storeID)
        ->sum('sub_transactions.amount');

        $bank_today = DB::table('transactions')
        ->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
        ->where('sub_transactions.account_id','=',8)
        ->whereDate('sub_transactions.created_at','=',date('Y-m-d'))
        ->where('sub_transactions.type','=','Debit')
        ->where('transactions.branch_id','=',$request->storeID)
        ->sum('sub_transactions.amount');
        
        $bank_month = DB::table('transactions')
        ->join('sub_transactions', 'transactions.id', '=', 'sub_transactions.transaction_id')
        ->where('sub_transactions.account_id','=',8)
        ->whereDate('sub_transactions.created_at','>=',$first_date)
        ->whereDate('sub_transactions.created_at','<=',$last_date)
        ->where('sub_transactions.type','=','Debit')
        ->where('transactions.branch_id','=',$request->storeID)
        ->sum('sub_transactions.amount');
        
        $top_brands_amount = DB::table('pos_receipts')
        ->join('pos_sub_receipts', 'pos_receipts.id', '=', 'pos_sub_receipts.pos_receipt_id')
        ->whereDate('pos_sub_receipts.created_at','>=',$first_date)
        ->whereDate('pos_sub_receipts.created_at','<=',$last_date)
        ->where('pos_receipts.branch_id','=',$request->storeID)
        ->select(DB::raw('sum(pos_sub_receipts.mrp) as amount, pos_sub_receipts.item_name as name'))
        ->groupBy('pos_sub_receipts.item_name')
        ->get();
        
        $top_users = DB::table('pos_receipts')
        ->join('users', 'users.id', '=', 'pos_receipts.created_by')
        ->whereDate('pos_receipts.created_at','>=',$first_date)
        ->whereDate('pos_receipts.created_at','<=',$last_date)
        ->where('pos_receipts.type','=','INE')
        ->where('pos_receipts.branch_id','=',$request->storeID)
        ->select(DB::raw('sum(pos_receipts.total_bill) as amount, users.name as name'))
        ->groupBy('name')
        ->get();

        $top_customers = DB::table('pos_receipts')
        ->join('profilers', 'profilers.id', '=', 'pos_receipts.profile_id')
        ->whereDate('pos_receipts.created_at','>=',$first_date)
        ->whereDate('pos_receipts.created_at','<=',$last_date)
        ->where('pos_receipts.type','=','INE')
        ->where('pos_receipts.branch_id','=',$request->storeID)
        ->select(DB::raw('sum(pos_receipts.total_bill) as amount, profilers.account_title as name'))
        ->groupBy('account_title')
        ->get();

        $recently_added = DB::table('stocks')
        ->whereDate('created_at','>=',$first_date)
        ->whereDate('created_at','<=',$last_date)
        ->where('branch_id','=',$request->storeID)
        ->orderBy('id','DESC')
        ->limit(20)
        ->get();
        
        $revenueVsExpense = DB::select(
            DB::raw("SELECT MONTH(transactions.created_at) as trans_month , 
            (SUM(CASE WHEN sub_transactions.type = 'Credit' AND chart_accounts.account_nature = 'Revenue' THEN sub_transactions.amount ELSE 0  END) -
            SUM(CASE WHEN sub_transactions.type = 'Debit' AND chart_accounts.account_nature = 'Revenue' THEN sub_transactions.amount  ELSE 0  END)) as 
            'total_revenue',
            (SUM(CASE WHEN sub_transactions.type = 'Debit' AND chart_accounts.account_nature = 'Expense' THEN sub_transactions.amount ELSE 0  END) -
            SUM(CASE WHEN sub_transactions.type = 'Credit' AND chart_accounts.account_nature = 'Expense' THEN sub_transactions.amount  ELSE 0  END)) AS 
            'total_expense' 
            FROM sub_transactions 
            JOIN transactions ON transactions.id = sub_transactions.transaction_id
            JOIN chart_accounts ON chart_accounts.id = sub_transactions.account_id 
            WHERE transactions.branch_id = :store_id  AND  transactions.created_at BETWEEN :last_date AND  :first_date AND (chart_accounts.account_nature = 'Revenue' OR chart_accounts.account_nature = 'Expense')
            GROUP BY MONTH(transactions.created_at) ORDER BY trans_month ASC"), array(
                'first_date' => date('Y-m-d', strtotime('+1 month')),
                'last_date'  => date('Y-m-d', strtotime('-7 month')),
                'store_id'   => $request->storeID,
          ));

        $last_months = [
            date('m', strtotime('-6 month')),
            date('m', strtotime('-5 month')),
            date('m', strtotime('-4 month')),
            date('m', strtotime('-3 month')),
            date('m', strtotime('-2 month')),
            date('m', strtotime('-1 month')),
            date('m')
        ];   
        
        $user =  new User(); 
		$stores = $user->getUserStores();
		
		return [
            'shortage_qty' => $shortage_qty,
            'account_holders' => $account_holders,
            'monthly_expense' => $expense_month,
            'items_qty' => $items_qty,
            'sales_today' => $sales_today,
            'sales_qty' => $sales_qty,
            'sales_month' => $sales_month,
            'return_today' => $return_today,
            'return_month' => $return_month,
            'expense_today' => $expense_today,
            'purchase_today' => $purchase_today,
            'purchase_month' => $purchase_month,
            'purchase_return_today' => $pur_return_today,
            'purchase_return_month' => $pur_return_month,
            'transfer_today' => $transfer_today,
            'transfer_month' => $transfer_month,
            'cash_in_today' => $cash_today,
            'cash_in_month' => $cash_month,
            'cash_at_bank_today' => $bank_today,
            'cash_at_bank_month' => $bank_month,
            'top_brands_amount' => $top_brands_amount,
            'top_customers' => $top_customers,
            'top_users' => $top_users,
            'recently_added' => $recently_added,
            'store_info' => $store_info,
            'last_months' => $last_months,
            'revenue_Expense' => $revenueVsExpense,
            'stores' => $stores
		];
	}
}
