<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PrinterReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PrinterReceiptController extends Controller
{
    public function index(Request $request)
    {
        if($request->storeID == 0)
        {
            $request->storeID  = Auth::user()->branch_id;
        }

        $options = PrinterReceipt::where('status', 'Active')
        ->where('branch_id',$request->storeID)
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get();

        $totalRecords  = PrinterReceipt::where('status','Active')
        ->where('branch_id',$request->storeID)
        ->count();

        $user =  new User(); 
        $stores = $user->getUserStores();
        
        return [
            'stores' => $stores,
            'records' => $options,
            'limit' => 20,
            'totalRecords' => $totalRecords,
            'currentStoreID' => Auth::user()->branch_id
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
			'receipt_priority' => ['required'],
			'status'          => ['required'],
			'branch_id'       => ['required']
		]);
       
        $item = new PrinterReceipt($request->all()); 
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Printer receipt Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = PrinterReceipt::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $request->validate([
			'id'               => ['required'],
			'receipt_priority' => ['required']
		]);

        $product = PrinterReceipt::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Printer receipt Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = PrinterReceipt::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Printer receipt Deleted Successfully'
        ]);
    }
}
