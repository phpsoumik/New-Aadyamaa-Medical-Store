<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BankController extends Controller
{
   
    public function index(Request $request)
    {
        if($request->storeID == 0)
        {
            $request->storeID  = Auth::user()->branch_id;
        }

        $options = Banks::where('status','Active')
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get();

        $totalRecords  = Banks::where('status','Active')
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

    public function get_all_bank()
    {
        $user =  new User(); 
		$banks = $user->getUserBanks();

        return [
            'records' => $banks,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
			'bank'      => ['required'],
			'branch'    => ['required'],
			'code'      => ['required'],
			'title'     => ['required'],
			'number'    => ['required'],
			'type'      => ['required'],
			'status'    => ['required']
		]);
       
        $item = new Banks($request->all()); 
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Bank Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = Banks::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $request->validate([
			'id'     => ['required'],
			'bank'   => ['required'],
			'branch' => ['required'],
			'code'   => ['required'],
			'title'  => ['required'],
			'number' => ['required'],
			'type'   => ['required']
		]);

        $product = Banks::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Bank Updated Successfully'
        ]);
    } 

    
    public function delete(Request $request)
    {
        $product = Banks::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Bank Deleted Successfully'
        ]);
    }
}
