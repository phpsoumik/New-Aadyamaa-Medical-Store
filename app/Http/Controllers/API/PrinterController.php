<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PrinterController extends Controller
{
	public function index(Request $request)
	{
		if($request->storeID == 0)
		{
			$request->storeID  = Auth::user()->branch_id;
		}

		$options = Printer::where('status', 'Active')
		->where('branch_id',$request->storeID)
		->limit(20)
		->offset($request->start)
		->orderBy('id','DESC')
		->get();

		$totalRecords  = Printer::where('status','Active')
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
			'printer_name'    => ['required'],
			'printer_type'    => ['required'],
			'default_printer' => ['required'],
			'status'          => ['required'],
			'branch_id'       => ['required']
		]);
	   
		$item = new Printer($request->all()); 
		$item->save();

		return response()->json([
			'alert' =>'info',
			'msg'   =>'Printer Created Successfully'
		]);
	}

   
	public function show($id)
	{
		$option = Printer::find($id);
		return response()->json([$option]);
	}


	public function update(Request $request)
	{
		$request->validate([
			'id'              => ['required'],
			'printer_name'    => ['required'],
			'printer_type'    => ['required'],
			'default_printer' => ['required']
		]);

		$product = Printer::find($request->id);
		$product->update($request->all());

		return response()->json([
			'alert' =>'info',
			'msg'=>'Printer Updated Successfully'
		]);
	}
	
	public function delete(Request $request)
	{
		$product = Printer::find($request->id);
		$product->update($request->all());

		return response()->json([
			'alert' =>'info',
			'msg'=>'Printer Deleted Successfully'
		]);
	}
}
