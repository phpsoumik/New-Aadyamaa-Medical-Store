<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChartAccount;
use App\Models\Transaction;
use App\Models\SubTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpenHeadController extends Controller
{
    public function accountHeadList(Request $request)
    {
        $options = ChartAccount::where('status', 'Active')
        ->where('account_name','LIKE','%'.$request->keyword.'%')
        ->get();

        $stored = Transaction::with('subTransaction')
        ->where('generated_source','OAH')
        ->where('branch_id',Auth::user()->branch_id)
        ->get();
        
        return [
            'records' => $options,
            'stored' => $stored,
        ];
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'description'      => ['required'],
            'item_list'        => ['required']
        ]);

        DB::beginTransaction();

		try
		{
			$itemLists = json_decode($request->item_list);

			if($itemLists != NULL)
			{

                $transaction_id = 0;
                $transaction = Transaction::where('generated_source', 'OAH' )
                ->where('branch_id',Auth::user()->branch_id)
                ->first();

                if($transaction != NULL) {
                    $transaction->narration = $request->description;
                    $transaction->update();
                    $transaction_id = $transaction->id;
                }
                else
                {
                    $transaction = new Transaction([
                        'narration'     => $request->description,
                        'generated_source' => 'OAH',
                        'branch_id'      => Auth::user()->branch_id,
                    ]);

                    $transaction->save();
                    $transaction_id = $transaction->id;
                }

                foreach($itemLists as $item)
				{
					if($item->debitAmount != 0 AND $item->debitAmount != NULL)
					{
						$subTransaction = new SubTransaction([
							'transaction_id'     => $transaction_id,
							'account_id'     	 => $item->accountID,
							'account_name'	 	 => $item->accountHead,
							'amount'      	     => $item->debitAmount,
							'type'      		 => 'Debit',
						]);
						
						$subTransaction->save();
					}

					if($item->creditAmount != 0 AND $item->creditAmount != NULL)
					{
						$subTransaction = new SubTransaction([
							'transaction_id'     => $transaction_id,
							'account_id'     	 => $item->accountID,
							'account_name'	 	 => $item->accountHead,
							'amount'      	     => $item->creditAmount,
							'type'      		 => 'Credit',
						]);
						
						$subTransaction->save();
					}
				}
				
                $response = response()->json([
					'alert' =>'info',
					'msg'   =>'Heads Uploaded Successfully'
				]);

                DB::commit();
            }
            else
            {
                $response = response()->json([
                    'alert' =>'danger',
                    'msg'   =>'Heads cannot be created'
                ]);
            }
        }
        catch (\Exception $e)
        {
            DB::rollBack();

            $response = response()->json([
                'alert' =>'danger',
                'msg'   => $e
            ]);

            throw $e;
        }
    
    
        return $response;    
    }
}
