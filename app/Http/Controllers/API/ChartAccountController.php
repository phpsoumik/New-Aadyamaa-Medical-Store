<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use Illuminate\Http\Request;

class ChartAccountController extends Controller
{
    public function index(Request $request)
    {
        $options = ChartAccount::where('status', 'Active')
        ->where('account_name','LIKE','%'.$request->keyword.'%')
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get();

        $totalRecords  = ChartAccount::where('status','Active')
        ->count();
        
        return [
            'records' => $options,
            'limit' => 20,
            'totalRecords' => $totalRecords
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_code'      => ['required'],
            'account_name'      => ['required'],
            'account_nature'    => ['required'],
            'account_type'      => ['required'],
            'expense_type'      => ['required'],
            'status'            => ['required'],
        ]);

        $item = new ChartAccount($request->all()); 
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Account Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = ChartAccount::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'id'                => ['required'],
            'account_code'      => ['required',"unique:chart_accounts,account_code,$request->id"],
            'account_name'      => ['required'],
            'account_nature'    => ['required'],
            'account_type'      => ['required'],
            'expense_type'      => ['required'],
        ]);

        $product = ChartAccount::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Account Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = ChartAccount::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Account Deleted Successfully'
        ]);
    }

    public function searchAccountHeads(Request $request)
	{
		$keyword = $request['keyword'];

		$options = ChartAccount::where('status', 'Active')
		->where('account_code','=',$request->keyword)
		->orWhere('account_name','LIKE','%'.$request->keyword.'%')
		->orWhere('account_nature','LIKE','%'.$request->keyword.'%')
		->limit(20)
		->offset($request->start)
		->orderBy('id','DESC')
		->get();
		
		return [
			'records' => $options
		];
	}
}
