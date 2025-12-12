<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OptionTags;
use Illuminate\Http\Request;

class OptionTagsController extends Controller
{
    public function index(Request $request)
    {    
        $options = OptionTags::where('status', 'Active')
        ->where('option_type',$request->tag)
        ->limit(20)
        ->offset($request->start)
        ->orderBy('id','DESC')
        ->get();

        $totalRecords  = OptionTags::where('status','Active')
        ->where('option_type',$request->tag)
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
			'optionName'   => ['required'],
		]);

        $item = new OptionTags([
            'option_name' => $request->optionName,
            'description' => $request->description,
            'option_type' => $request->optionType,
            'status'      => $request->status,
        ]);
        
        $item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Option Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = OptionTags::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $request->validate([
			'id'            => ['required'],
			'option_name'   => ['required'],
		]);
        
        $product = OptionTags::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Options Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = OptionTags::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Options Deleted Successfully'
        ]);
    }

    public function getRiskFactorQuestions()
    {    
        $options = OptionTags::where('status', 'Active')
        ->where(function ($query) {
            $query->where('option_type','=','Previous Pregnancy')
            ->orWhere('option_type','=','Previous Child Birth')
            ->orWhere('option_type','=','Current Pregnancy')
            ->orWhere('option_type','=','Previous Operation');
        })
        ->get();

        return [
            'records' => $options
        ];
    }


}
