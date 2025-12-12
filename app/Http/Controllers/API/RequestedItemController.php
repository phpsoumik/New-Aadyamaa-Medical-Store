<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RequestedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RequestedItemController extends Controller
{
    public function index(Request $request)
    {
        if($request->storeID == 0)
        {
            $request->storeID  = Auth::user()->branch_id;
        }

        $options = RequestedItem::where('status', 'Active')
        ->where('branch_id',$request->storeID)
        ->where('item_name','LIKE','%'.$request->keyword.'%')
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get();

        $totalRecords  = RequestedItem::where('status','Active')
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
			'item_name'       => ['required'],
			'status'          => ['required'],
			'branch_id'       => ['required']
		]);
       
        $item = new RequestedItem($request->all()); 
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Requested Item Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = RequestedItem::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $request->validate([
			'item_name'       => ['required']
		]);

        $product = RequestedItem::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Requested Item Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = RequestedItem::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Requested Item Deleted Successfully'
        ]);
    }
}
