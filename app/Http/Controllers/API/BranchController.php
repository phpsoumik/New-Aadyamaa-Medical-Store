<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\ChartAccount;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $options = Branch::where('status', 'Active')
        ->where('code','LIKE','%'.$request->keyword.'%')
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get(); 
        
        $chartList = ChartAccount::where('status', 'Active')
        ->where('account_nature','Liability')
        ->get();

        $totalRecords  = Branch::where('status','Active')
        ->count();
        
        return [
            'records' => $options,
            'limit' => 20,
            'totalRecords' => $totalRecords,
            'chartList' => $chartList,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required'],
            'code'     => ['required', 'unique:branches','min:4'],
            'address'  => ['required'],
            'status'   => ['required'],
        ]);

        $item = new Branch([
            'name'                  => $request->name,
            'code'                  => $request->code,
            'address'               => $request->address,
            'description'           => $request->description,
            'license_no'            => $request->licenseNo,
            'email'                 => $request->email,
            'contact'               => $request->contact,
            'status'                => $request->status,
            'show_1'                => ($request->show_1 == 'true' ? 'ON' : 'OFF'),
            'tax_name_1'            => $request->tax_name_1,
            'tax_value_1'           => $request->tax_value_1,
            'required_optional_1'   => $request->required_optional_1,
            'link1'                 => $request->link1,
            'show_2'                => ($request->show_2 == 'true' ? 'ON' : 'OFF'),
            'tax_name_2'            => $request->tax_name_2,
            'tax_value_2'           => $request->tax_value_2,
            'required_optional_2'   => $request->required_optional_2,
            'link2'                 => $request->link2,
            'show_3'                => ($request->show_3 == 'true' ? 'ON' : 'OFF'),
            'tax_name_3'            => $request->tax_name_3,
            'tax_value_3'           => $request->tax_value_3,
            'required_optional_3'   => $request->required_optional_3,
            'link3'                 => $request->link3,
        ]);
        
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Store Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = Branch::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $product = Branch::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Store Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = Branch::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Store Deleted Successfully'
        ]);
    }
}
