<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\DateFilters;
use App\Models\Transaction;
use App\Models\SubTransaction;
use Illuminate\Support\Facades\DB;

class UserBalanceController extends Controller
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
		

		$options = Voucher::with([
			'branch:id,name as branchName,code as branchCode',
			'userName:id,name as userName',
		])
		->where('vouchers.status', 'Active')
		->where('vouchers.type', $filters->type)
		->where('vouchers.voucher_no','LIKE','%'.$filters->keyword.'%')
		->where('vouchers.branch_id',$filters->storeID)
		->whereDate('voucher_date','>=', $date1)
		->whereDate('voucher_date','<=', $date2)
		->limit(20)
		->offset($request->start)
		->orderBy('id','DESC')
		->get();
		
		
		$totalRecords = Voucher::where('vouchers.status', 'Active')
		->where('vouchers.type', $filters->type)
		->where('vouchers.voucher_no','LIKE','%'.$filters->keyword.'%')
		->whereDate('voucher_date','>=', $date1)
		->whereDate('voucher_date','<=', $date2)
		->where('vouchers.branch_id',$filters->storeID)
		->count();
		
		return [
			'records' => $options,
			'limit' => 20,
			'totalRecords' => $totalRecords,
			'statement' => 'Transactions between '.date('d-m-Y',strtotime($date1)).'-TO-'.date('d-m-Y',strtotime($date2)),
		];
	} 
	
	public function store(Request $request)
	{
		$request->validate([
			'profile_id'    	=> ['required'],
			'profile_name'  	=> ['required'],
			'balance_date' 		=> ['required'],
			'memo' 				=> ['required'],
			'total_amount' 		=> ['required'],
			'account_type' 		=> ['required'],
			'type' 				=> ['required'],
			'item_list'         => ['required'],
			'status'          	=> ['required']
		]);

		DB::beginTransaction();

		try
		{
			$itemLists = json_decode($request->item_list);

			$v = new  Voucher();
			$profileCount =  $v->checkProfileID($request->profile_id);

			if($itemLists != NULL AND $profileCount == 0)
			{
				$transaction = new Transaction([
					'narration'     => $request->memo,
					'generated_source' => $request->type,
					'branch_id'      => Auth::user()->branch_id,
				]);
				
				$transaction->save();

				foreach($itemLists as $item)
				{
					if($item->debitAmount != 0 AND $item->debitAmount != NULL)
					{
						$subTransaction = new SubTransaction([
							'transaction_id'     => $transaction->id,
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
							'transaction_id'     => $transaction->id,
							'account_id'     	 => $item->accountID,
							'account_name'	 	 => $item->accountHead,
							'amount'      	     => $item->creditAmount,
							'type'      		 => 'Credit',
						]);
						
						$subTransaction->save();
					}
				}
				
				
				$v = new  Voucher();
				$voucherNo =  $v->generateID($request->type);
			
				$item = new Voucher([
					'voucher_no'     => $voucherNo,
					'transaction_id' => $transaction->id,
					'profile_id'     => $request->profile_id,
					'profile_name'   => $request->profile_name,
					'voucher_date'   => $request->balance_date,
					'memo'           => $request->memo,
					'total_amount'   => $request->total_amount,
					'created_by'     => Auth::user()->id,
					'account_type'   => $request->account_type,
					'type'           => $request->type,
					'branch_id'      => Auth::user()->branch_id,
					'status'         => $request->status
				]);
				
				$item->save();

				$response = response()->json([
					'alert' =>'info',
					'msg'   =>'Balance Created Successfully'
				]);

			}
			else
			{
				$response = response()->json([
					'alert' =>'danger',
					'msg'   =>'Balance cannot be created or already added'
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
		$balance = Voucher::find($id);
		
		$response = response()->json([
			'balance' => $balance
		]);

		return $response;
	}  
	
	
	public function update(Request $request)
	{
		$request->validate([
			'id'    	        => ['required'],
			'profile_id'    	=> ['required'],
			'profile_name'  	=> ['required'],
			'balance_date' 		=> ['required'],
			'memo' 				=> ['required'],
			'total_amount' 		=> ['required'],
			'account_type' 		=> ['required'],
			'type' 				=> ['required'],
			'item_list'         => ['required']
		]);

		DB::beginTransaction();

		try
		{
			$itemLists = json_decode($request->item_list);

			if($itemLists != NULL)
			{
				$voucher = Voucher::find($request->id);
			   
				$voucher->profile_id     = $request->profile_id;
				$voucher->profile_name   = $request->profile_name;
				$voucher->voucher_date   = $request->balance_date;
				$voucher->total_amount   = $request->total_amount;
				$voucher->memo           = $request->memo;
                $voucher->account_type   = $request->account_type;
				$voucher->update();

				$trans = Transaction::find($voucher->transaction_id);
				$trans->narration = $request->memo;
				$trans->update();

				foreach($itemLists as $item)
				{
					if($item->debitAmount != 0 AND $item->debitAmount != NULL)
					{
						$subTransaction = new SubTransaction([
							'transaction_id'     => $voucher->transaction_id,
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
							'transaction_id'     => $voucher->transaction_id,
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
					'msg'   =>'Balance Updated Successfully'
				]);

			}
			else
			{
				$response = response()->json([
					'alert' =>'danger',
					'msg'   =>'Balance cannot be updated'
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
