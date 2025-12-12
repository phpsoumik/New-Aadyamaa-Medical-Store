<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Banks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        if($request->storeID == 0)
        {
            $request->storeID  = Auth::user()->branch_id;
        }

        $banks = Banks::where('status', 'Active')
        ->orderBy('id','DESC')
        ->get();

        $options = PaymentMethod::with([
            'BankName'
        ])
        ->where('status', 'Active')
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get();

        $totalRecords  = PaymentMethod::where('status','Active')
        ->count();
        
        return [
            'banks' => $banks,
            'records' => $options,
            'limit' => 20,
            'totalRecords' => $totalRecords
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
			'card_name'              => ['required'],
			'card_charges'           => ['required'],
			'bank_id'                => ['required'],
			'charge_customer'        => ['required'],
		]);

        $item = new PaymentMethod([
            'card_name' => $request->card_name,
            'card_charges' => $request->card_charges,
            'bank_id'     => $request->bank_id,
            'description' => $request->description,
            'charge_customer' => $request->charge_customer,
            'status'      => $request->status,
            'created_by'  => Auth::user()->id,
            'branch_id'   => Auth::user()->branch_id,
        ]);
        
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Card Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = PaymentMethod::find($id);
        return response()->json([$option]);
    } 
    
    public function methods()
    {
        $option = PaymentMethod::where('status','Active')
        ->get();
        
        return [
            'option' => $option,
        ];
    }


    public function update(Request $request)
    {
        $request->validate([
            'id'                     => ['required'],
            'card_name'              => ['required'],
            'card_charges'           => ['required'],
            'bank_id'                => ['required'],
            'charge_customer'        => ['required'],
        ]);
        
        $product = PaymentMethod::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Card Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = PaymentMethod::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Card Deleted Successfully'
        ]);
    }
}
