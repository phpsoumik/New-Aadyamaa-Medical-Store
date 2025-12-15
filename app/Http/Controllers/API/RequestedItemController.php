<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RequestedItem;
use App\Models\Profiler;
use App\Models\Stock;
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

        $options = RequestedItem::with(['customer:id,account_title,contact_no', 'branch'])
        ->where('status', 'Active')
        ->where('branch_id',$request->storeID)
        ->where('medicine_name','LIKE','%'.$request->keyword.'%')
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get()
        ->map(function($item) {
            return [
                'id' => $item->id,
                'customer' => [
                    'name' => $item->customer ? $item->customer->account_title : 'N/A',
                    'phone' => $item->customer ? $item->customer->contact_no : 'N/A'
                ],
                'customer_phone' => $item->customer ? $item->customer->contact_no : 'N/A',
                'medicine_name' => $item->medicine_name,
                'quantity' => $item->quantity,
                'order_date' => $item->order_date ? $item->order_date->format('Y-m-d') : 'N/A',
                'advance_payment' => $item->advance_payment,
                'has_advance' => $item->has_advance,
                'status' => $item->status,
                'branch_id' => $item->branch_id,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at
            ];
        });

        $totalRecords  = RequestedItem::where('status','Active')
        ->where('branch_id',$request->storeID)
        ->count();

        $user =  new User(); 
        $stores = $user->getUserStores();
        
        // Get customers from profilers table
        $customers = Profiler::where('status', 'Active')
        ->where('account_type', 'Customer')
        ->select('id', 'account_title as name', 'contact_no as phone')
        ->distinct()
        ->orderBy('account_title')
        ->get()
        ->unique('name')
        ->values();
        
        return [
            'stores' => $stores,
            'records' => $options,
            'customers' => $customers,
            'limit' => 20,
            'totalRecords' => $totalRecords,
            'currentStoreID' => Auth::user()->branch_id
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
			'customer_id'     => ['nullable', 'integer'],
			'medicine_name'   => ['required'],
			'quantity'        => ['required', 'integer', 'min:1'],
			'order_date'      => ['required', 'date_format:Y-m-d'],
			'advance_payment' => ['nullable', 'numeric', 'min:0'],
			'has_advance'     => ['required', 'in:0,1'],
			'status'          => ['required'],
			'branch_id'       => ['required']
		]);
       
        $item = new RequestedItem([
            'customer_id' => $request->customer_id,
            'medicine_name' => $request->medicine_name,
            'quantity' => $request->quantity,
            'order_date' => $request->order_date,
            'advance_payment' => $request->advance_payment ?? 0,
            'has_advance' => $request->has_advance == '1',
            'status' => $request->status,
            'branch_id' => $request->branch_id
        ]);
        $item->save();

        return response()->json([
            'alert' =>'success',
            'msg'   =>'Medicine Request Created Successfully'
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
			'customer_id'     => ['nullable', 'integer'],
			'medicine_name'   => ['required'],
			'quantity'        => ['required', 'integer', 'min:1'],
			'order_date'      => ['required', 'date_format:Y-m-d'],
			'advance_payment' => ['nullable', 'numeric', 'min:0'],
			'has_advance'     => ['required', 'in:0,1']
		]);

        $product = RequestedItem::find($request->id);
        $product->update([
            'customer_id' => $request->customer_id,
            'medicine_name' => $request->medicine_name,
            'quantity' => $request->quantity,
            'order_date' => $request->order_date,
            'advance_payment' => $request->advance_payment ?? 0,
            'has_advance' => $request->has_advance == '1'
        ]);

        return response()->json([
            'alert' =>'success',
            'msg'=>'Medicine Request Updated Successfully'
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
    
    public function searchMedicine(Request $request)
    {
        $query = $request->get('query', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $medicines = Stock::where('status', 'Active')
            ->where('item_name', 'LIKE', '%' . $query . '%')
            ->select('id', 'item_name', 'generic_name', 'strength')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'display' => $item->item_name . ($item->generic_name ? ' (' . $item->generic_name . ')' : '') . ($item->strength ? ' - ' . $item->strength : '')
                ];
            });
            
        return response()->json($medicines);
    }
    
    public function getCustomerPhone(Request $request)
    {
        $customer = Profiler::find($request->customer_id);
        
        if ($customer) {
            return response()->json([
                'phone' => $customer->contact_no
            ]);
        }
        
        return response()->json(['phone' => '']);
    }
    
    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address'=> 'nullable|string|max:500',
            'status' => 'required|in:Active,Delete'
        ]);
        
        $customer = new Profiler([
            'account_title' => $request->name,
            'contact_no' => $request->phone,
            'email_address' => $request->email,
            'address' => $request->address,
            'account_type' => 'Customer',
            'status' => $request->status,
            'created_user' => Auth::user()->id
        ]);
        
        $customer->save();
        
        return response()->json([
            'alert' => 'success',
            'msg' => 'Customer added successfully',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->account_title,
                'phone' => $customer->contact_no
            ]
        ]);
    }
}
