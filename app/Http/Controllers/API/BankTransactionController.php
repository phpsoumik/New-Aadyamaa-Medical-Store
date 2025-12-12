<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\BankTransactionPayees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DateFilters;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\SubTransaction;
use App\Models\User;
use App\Models\Banks;

class BankTransactionController extends Controller
{
    public function index(Request $request)
	{
		$filters = json_decode($request->filters);
	
		if($filters->storeID == 0)
		{
			$filters->storeID  = Auth::user()->branch_id;
		}

		$dt = new DateFilters();
		
		$dt->set('filter',$filters->filterType);
		$dt->set('date1',$filters->date1);
		$dt->set('date2',$filters->date2);
		$date1 = $dt->getTheDates()[0];
		$date2 = $dt->getTheDates()[1];
		

		$options = BankTransaction::with([
			'branch:id,name as branchName,code as branchCode',
			'userName:id,name as userName',
			'profileName:profilers.id,profilers.account_title as profileName',
		])
		->where('type', $filters->type)
		->where('receipt_no','LIKE','%'.$filters->keyword.'%')
		->where('branch_id',$filters->storeID)
		->whereDate('receipt_date','>=', $date1)
		->whereDate('receipt_date','<=', $date2)
		->limit(20)
		->offset($request->start)
		->orderBy('id','DESC')
		->get();

        $banks = Banks::where('status', 'Active')->get();
		
		
		$totalRecords = BankTransaction::where('type', $filters->type)
		->where('receipt_no','LIKE','%'.$filters->keyword.'%')
		->whereDate('receipt_date','>=', $date1)
		->whereDate('receipt_date','<=', $date2)
		->where('branch_id',$filters->storeID)
		->count();
		
		return [
			'records' => $options,
			'banks' => $banks,
			'limit' => 20,
			'totalRecords' => $totalRecords,
			'statement' => 'Transactions between '.date('d-m-Y',strtotime($date1)).'-TO-'.date('d-m-Y',strtotime($date2)),
		];
	} 
	
	public function filterLists()
	{
		$dt = new DateFilters();
		$datesList = $dt->get('filterList');

		$user =  new User(); 
		$stores = $user->getUserStores();
		
		return [
			'datesList' => $datesList,
			'stores' 	=> $stores,
		];
	}

	public function store(Request $request)
	{
		$request->validate([
			'bank_id'    	=> ['required'],
			'amount'    	=> ['required'],
			'receipt_date'  => ['required'],
			'receipt_no' 	=> ['required'],
			'description' 	=> ['required'],
			'type' 			=> ['required'],
			'profile_id' 	=> ['required'],
			'account_head' 	=> ['required'],
			'account_id' 	=> ['required'],
			'status' 	    => ['required'],
			'item_list' 	=> ['required'],
		]);

		DB::beginTransaction();

		try
		{
			$itemLists = json_decode($request->item_list);	

			if($itemLists != NULL)
			{
				$transaction = new Transaction([
					'narration'     => $request->description,
					'generated_source' => $request->type,
					'branch_id'      => Auth::user()->branch_id,
				]);
				
				$transaction->save();

				foreach($itemLists as $item)
				{
					$subTransaction = new SubTransaction([
						'transaction_id'     => $transaction->id,
						'account_id'     	 => $item->accountID,
						'account_name'	 	 => $item->accountHead,
						'amount'      	     => $item->amount,
						'type'      		 => $item->type,
					]);
					
					$subTransaction->save();
				}
				
				
				$t = new  BankTransaction();
				$receiptNo =  $t->generateID($request->type);
			
				$item = new BankTransaction([
					'transaction_id' => $transaction->id,
                    'receipt_no'     => $receiptNo,
                    'transaction_no' => $request->receipt_no,
					'receipt_date'   => $request->receipt_date,
					'description'    => $request->description,
					'bank_id'        => $request->bank_id,
					'account_id'     => $request->account_id,
					'account_head'   => $request->account_head,
					'amount'         => $request->amount,
					'created_by'     => Auth::user()->id,
					'type'           => $request->type,
					'entity'         => 'Yes',
					'branch_id'      => Auth::user()->branch_id,
					'status'         => $request->status
				]);

				$item->save();

				if($request->type != 'DPT' AND $request->type != 'EXP')
				{
					$item = new BankTransactionPayees([
						'bank_transaction_id' => $item->id,
						'profile_id' => $request->profile_id,
					]);

					$item->save();
				}
				$response = response()->json([
					'alert' =>'info',
					'msg'   =>'Receipt Created Successfully'
				]);

			}
			else
			{
				$response = response()->json([
					'alert' =>'danger',
					'msg'   =>'Receipt cannot be created'
				]);
			}

			DB::commit();
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

   
	public function show($id)
	{
        $options = BankTransaction::with([
			'profileName:profilers.id,profilers.account_title as profileName',
		])
		->where('id',$id)
		->get();
		
		$response = response()->json(
			$options,
		);

		return $response;
	}  
	
	public function getTransactionReceipt(Request $request)
	{
		$request->validate([
			'id' => ['required']
		]);

		$receipt = BankTransaction::with([
			'profileName:profilers.id,profilers.account_title as profileName',
			'bankName:banks.id,banks.bank as bankName,banks.number as bankAccountNo',
		])
		->where('id',$request->id)
		->first();
		
		$storeDetail = BankTransaction::find($request->id)->branch;
		
		$response = response()->json([
			'receipt' => $receipt,
			'storeDetail' => $storeDetail
		]);

		return $response;
	}

	public function update_activity(Request $request)
    {
        $request->validate([
			'id'       => ['required'],
			'status'   => ['required']
		]);

        $product = BankTransaction::find($request->id);
        $product->update($request->all());

        return response()->json([
            'alert' =>'info',
            'msg'=>'Updated Successfully'
        ]);
    }

	public function update(Request $request)
	{
		$request->validate([
			'id'    	    => ['required'],
			'bank_id'    	=> ['required'],
			'amount'    	=> ['required'],
			'receipt_date'  => ['required'],
			'receipt_no' 	=> ['required'],
			'description' 	=> ['required'],
			'type' 			=> ['required'],
			'profile_id' 	=> ['required'],
			'account_head' 	=> ['required'],
			'account_id' 	=> ['required'],
			'status' 	    => ['required'],
			'item_list' 	=> ['required'],
		]);

		DB::beginTransaction();

		try
		{
			$itemLists = json_decode($request->item_list);

			if($itemLists != NULL)
			{
				$receipt = BankTransaction::find($request->id);

				$receipt->transaction_no = $request->receipt_no;
				$receipt->receipt_date   = $request->receipt_date;
				$receipt->description    = $request->description;
				$receipt->bank_id        = $request->bank_id;
				$receipt->account_id     = $request->account_id;
				$receipt->account_head   = $request->account_head;
				$receipt->amount         = $request->amount;
				$receipt->type           = $request->type;
				$receipt->update();

				if($request->type != 'DPT')
				{
					$receiptProfile = BankTransactionPayees::where('bank_transaction_id',$receipt->id)->first();
					$receiptProfile->profile_id = $request->profile_id;
					$receiptProfile->update();
				}
				else
				{
					BankTransactionPayees::where('bank_transaction_id',$receipt->id)->delete();
				}

				$trans = Transaction::find($receipt->transaction_id);
				$trans->narration = $request->description;
				$trans->update();

				foreach($itemLists as $item)
				{
					$subTransaction = new SubTransaction([
						'transaction_id'     => $receipt->transaction_id,
						'account_id'     	 => $item->accountID,
						'account_name'	 	 => $item->accountHead,
						'amount'      	     => (double) $item->amount,
						'type'      		 => $item->type,
					]);
					
					$subTransaction->save();
				}
				
				
				$response = response()->json([
					'alert' =>'info',
					'msg'   =>'Receipt Updated Successfully'
				]);

			}
			else
			{
				$response = response()->json([
					'alert' =>'danger',
					'msg'   =>'Receipt cannot be updated'
				]);
			}

			DB::commit();
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
