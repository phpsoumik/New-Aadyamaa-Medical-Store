<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SaleService;
use App\Models\ChartAccount;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SaleServiceController extends Controller
{
    public function index(Request $request)
    {
        if($request->storeID == 0)
        {
            $request->storeID  = Auth::user()->branch_id;
        }

        $services = SaleService::join('chart_accounts','sale_services.income_account','=','chart_accounts.id')
        ->where('sale_services.status','Active')
        ->where('sale_services.branch_id',$request->storeID)
        ->where('sale_services.service_name','LIKE','%'.$request->keyword.'%')
        ->limit(20)
        ->offset($request->start)
        ->orderBy('sale_services.id','DESC')
        ->get(['sale_services.*','chart_accounts.account_name','chart_accounts.account_code']);
 
        $revenueList = ChartAccount::where('status', 'Active')
        ->where('account_nature','Revenue')
        ->get();
 
        $totalRecords  = SaleService::where('status','Active')
        ->count();

        $user =  new User(); 
        $stores = $user->getUserStores();
         
         return [
             'stores'               => $stores,
             'incomeAccountList'    => $revenueList,
             'records'              => $services,
             'totalRecords'         => $totalRecords,
             'limit'                => 20,
             'currentStoreID'       => Auth::user()->branch_id
         ];
    }

    public function store(Request $request)
    {
        $request->validate([
			'service_name'       => ['required'],
			'charges'            => ['required'],
			'income_account'     => ['required'],
			'status'             => ['required'],
			'branch_id'          => ['required']
		]);
       
        $item = new SaleService($request->all()); 
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Sale Service Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = SaleService::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $request->validate([
			'id'                 => ['required'],
			'service_name'       => ['required'],
			'charges'            => ['required'],
			'income_account'     => ['required'],
		]);

        $product = SaleService::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Sale Service Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = SaleService::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Sale Service Deleted Successfully'
        ]);
    }
}
