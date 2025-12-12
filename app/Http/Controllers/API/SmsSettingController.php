<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SmsSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Visit;
use Exception;

class SmsSettingController extends Controller
{
    public function index(Request $request)
    {
        if($request->storeID == 0)
        {
            $request->storeID  = Auth::user()->branch_id;
        }

        $options = SmsSetting::join('branches','sms_setting.branch_id','=','branches.id')
	   ->where('sms_setting.status','Active')
	   ->limit(20)
	   ->offset($request->start)
	   ->orderBy('sms_setting.id','DESC')
	   ->get(['sms_setting.*','branches.name as branchName','branches.code as branchCode']);


        $totalRecords  = SmsSetting::where('status','Active')
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
			'enable_notification'   => ['required'],
			'domain_name'           => ['required'],
			'account_email'         => ['required'],
			'token_key'             => ['required'],
			'test_no'               => ['required']
		]);
       
        $item = new SmsSetting([
			'enable_notification'    => $request->enable_notification,
			'domain_name'            => $request->domain_name,
			'account_email'          => $request->account_email,
			'token_key'              => $request->token_key,
			'test_no'                => $request->test_no,
			'status'                 => $request->status,
			'created_by'             => Auth::user()->id,
			'branch_id'              => $request->branch_id
		]);
		
		$item->save();

        return response()->json([
            'alert' =>'info',
            'msg'   =>'Created Successfully'
        ]);
    }

   
    public function show($id)
    {
        $option = SmsSetting::find($id);
        return response()->json([$option]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'enable_notification'   => ['required'],
			'domain_name'           => ['required'],
			'account_email'         => ['required'],
			'token_key'             => ['required'],
			'test_no'               => ['required']
		]);

        $product = SmsSetting::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Updated Successfully'
        ]);
    }
    
    public function delete(Request $request)
    {
        $product = SmsSetting::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Deleted Successfully'
        ]);
    }

    function sendSmsNotification(Request $request)
    {

        $list = json_decode($request->list);

        $records  = SmsSetting::where('status','Active')
        ->where('branch_id',Auth::user()->branch_id)
        ->first();

        // // Message details
        // $numbers = array($request->number);
        // $message = rawurlencode($request->message);
        // $numbers = implode(',', $numbers);
       

        if($records != NULL AND count($list) > 0)
        {
            if($records['enable_notification'] == 'Enable')
            {

                foreach($list as $visit_id)
                {

                    $v = Visit::join('patients','visits.patient_id','=','patients.id')
                    ->where('visits.id',$visit_id)
                    ->get(['patients.*','visits.visit_date','visits.doctor_name'])->first();

                    if($v != NULL)
                    {

                        try
                        {
                            $message  = 'Dear visitor your schedule visit Date/Time is '.$v->visit_date.' with Doctor '.$v->doctor_name.' Thank you';
                            $numbers =   $v->phone_no;

                            // Prepare data for POST request
                            $data = array(
                                'apiKey' => $records['token_key'],
                                'numbers' => $numbers,
                                "message" => rawurlencode($message)
                            );

                            // Send the POST request with cURL
                            $ch = curl_init( $records['domain_name'].'/api2/send/');
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);

                            curl_close($ch);

                            return response()->json([
                                'alert' =>'danger',
                                'msg'=>'Failed to send notification'
                            ]);

                        }
                        catch(Exception $e)
                        {
                            return response()->json([
                                'alert' =>'danger',
                                'msg'=>'Failed to send notification'
                            ]);
                        }
                    }
                }
            }
            else
            {
                return response()->json([
                    'alert' =>'info',
                    'msg'=>'Notification is disabled'
                ]);
            }
        }
        else
        {
            return response()->json([
                'alert' =>'info',
                'msg'=>'No Notification config details found'
            ]);
        }
    }
}
