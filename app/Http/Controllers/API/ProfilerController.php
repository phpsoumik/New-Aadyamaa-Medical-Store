<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Profiler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilerController extends Controller
{
	public function index(Request $request)
	{
		$options = Profiler::where('status', 'Active')
		->where('contact_no','LIKE','%'.$request->keyword.'%')
		->limit(20)
		->offset($request->start)
		->orderBy('id','DESC')
		->get();

		$totalRecords  = Profiler::where('status','Active')
		->where('contact_no','LIKE','%'.$request->keyword.'%')
		->count();
		
		return [
			'records' => $options,
			'limit' => 20,
			'totalRecords' => $totalRecords,
			'currentUserID' => Auth::user()->id
		];
	}

	public function store(Request $request)
	{
		$request->validate([
			'account_title'    => ['required'],
			'contact_no'       => ['required','unique:profilers'],
			'status'           => ['required']
		]);

		$item = new Profiler($request->all()); 
		$item->save();

		return response()->json([
			'alert' 		  =>'info',
			'msg'   		  =>'Profiler Created Successfully',
			'profileDetail'   => $item,
		]);
	}

   
	public function show($id)
	{
		$option = Profiler::find($id);
		return response()->json([$option]);
	}


	public function update(Request $request)
	{
		$request->validate([
			'id'               => ['required'],
			'account_title'    => ['required'],
			'contact_no'       => ['required',"unique:profilers,contact_no,$request->id"]
		]);

		$product = Profiler::find($request->id);
		$product->update($request->all());

		return response()->json([
			'alert' =>'info',
			'msg'=>'Profiler Updated Successfully'
		]);
	}
	
	public function delete(Request $request)
	{
		$product = Profiler::find($request->id);
		$product->update($request->all());

		return response()->json([
			'alert' =>'info',
			'msg'=>'Profiler Deleted Successfully'
		]);
	}

	public function searchProfile(Request $request)
	{
		$keyword = $request['keyword'];

		$options = Profiler::where('status', 'Active')
		->where('contact_no','=',$request->keyword)
		->orWhere('account_title','LIKE','%'.$request->keyword.'%')
		->orWhere('email_address','LIKE','%'.$request->keyword.'%')
		->limit(20)
		->offset($request->start)
		->orderBy('id','DESC')
		->get();
		
		return [
			'records' => $options
		];
	}

	// THIS IS SOUMIK CODE - Search only suppliers for expiry report
	public function searchSuppliers(Request $request)
	{
		$keyword = $request['keyword'];

		$options = Profiler::where('status', 'Active')
		->where('account_type', 'Supplier')
		->where(function($query) use ($request) {
			$query->where('contact_no','=',$request->keyword)
				->orWhere('account_title','LIKE','%'.$request->keyword.'%')
				->orWhere('email_address','LIKE','%'.$request->keyword.'%');
		})
		->limit(20)
		->orderBy('id','DESC')
		->get();
		
		return [
			'records' => $options
		];
	}
}
