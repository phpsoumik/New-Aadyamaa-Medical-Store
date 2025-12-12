<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DateFilters;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\SubTransaction;
use App\Models\User;
use App\Models\Branch;
use App\Models\SubReceipt;
use App\Models\ReceiptPayment;

class ReceiptController extends Controller
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


		$options = Receipt::with([
			'branch:id,name as branchName,code as branchCode',
			'userName:id,name as userName',
			'profileName:profilers.id,profilers.account_title as profileName',
			'receiptBalance',
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

		$storeTaxes = Branch::with([
			'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
		])
		->where('id',Auth::user()->branch_id)
		->get();


		$totalRecords = Receipt::where('type', $filters->type)
		->where('receipt_no','LIKE','%'.$filters->keyword.'%')
		->whereDate('receipt_date','>=', $date1)
		->whereDate('receipt_date','<=', $date2)
		->where('branch_id',$filters->storeID)
		->count();

		return [
			'records' => $options,
			'limit' => 20,
			'totalRecords' => $totalRecords,
			'storeTaxes' => $storeTaxes,
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
			'profile_id'     			=> ['required'],
			'receipt_date'   			=> ['required'],
			'receipt_due_date'   		=> ['required'],
			'memo' 			 			=> ['required'],
			'type' 			 			=> ['required'],
			'item_list' 	 			=> ['required'],
			'counter_entry'  			=> ['required'],
			'status' 		 			=> ['required'],
			'total_tax1' 	 			=> ['required'],
			'total_tax2' 	 			=> ['required'],
			'total_tax3' 	 			=> ['required'],
			'total_gross' 	 			=> ['required'],
			'total_discount' 			=> ['required'],
			'total_tax' 	 			=> ['required'],
			'total_bill' 	 			=> ['required'],
		]);

		DB::beginTransaction();

		try
		{
			$itemLists = json_decode($request->item_list);
			$counterEntry = json_decode($request->counter_entry);
			$paymentList = json_decode($request->payment_list);


			$paymentList->amount = $request->total_paid;

			if($request->type == 'SLS' AND $request->memo == '')
			{
				$request->memo = 'Transaction occurred from sales receipt';
			}
			else if($request->type == 'RFR' AND $request->memo == '')
			{
				$request->memo = 'Transaction occurred from refund receipt';
			}
			else if($request->type == 'INV' AND $request->memo == '')
			{
				$request->memo = 'Transaction occurred from invoice receipt';
			}

			if($itemLists != NULL AND $counterEntry != null)
			{
				$transaction = new Transaction([
					'narration'     => $request->memo,
					'generated_source' => $request->type,
					'branch_id'      => Auth::user()->branch_id,
				]);

				$transaction->save();

				foreach($counterEntry as $item)
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


				$t = new  Receipt();
				$receiptNo =  $t->generateID($request->type);

				$receiptItem = new Receipt([
					'transaction_id' => $transaction->id,
					'receipt_no'     => $receiptNo,
					'receipt_date'   => $request->receipt_date,
					'receipt_due_date'   => $request->receipt_due_date,
					'payment_method' => $paymentList->cardName,
					'description'    => $request->memo,
					'profile_id'     => $request->profile_id,
					'total_discount' => $request->total_discount,
					'gross_total'    => $request->total_gross,
					'total_tax1'   	 => $request->total_tax1,
					'total_tax2'   	 => $request->total_tax2,
					'total_tax3'   	 => $request->total_tax3,
					'total_tax'   	 => $request->total_tax,
					'total_bill'   	 => $request->total_bill,
					'created_by'     => Auth::user()->id,
					'type'           => $request->type,
					'branch_id'      => Auth::user()->branch_id,
					'status'         => $request->status
				]);

				$receiptItem->save();

				foreach($itemLists as $item)
				{
					$subReceipt = new SubReceipt([
						'receipt_id'     		=> $receiptItem->id,
						'sub_transaction_id'    => $item->accountID,
						'qty'	 	  			=> $item->quantity,
						'price'       			=> $item->price,
						'discount'    			=> $item->discount,
						'tax1'        			=> $item->tax1Value,
						'tax2'        			=> $item->tax2Value,
						'tax3'        			=> $item->tax3Value,
						'sub_total'   			=> $item->subTotal,
					]);

					$subReceipt->save();
				}

				if(($request->type == 'SLS' OR $request->type == 'RFR') AND $request->total_paid > 0)
				{
					$t = new  Receipt();
					$t->passBankTransaction($transaction->id,$receiptNo,$request->memo,$paymentList,$request->profile_id,$request->type);

					$payments = new ReceiptPayment([
						'transaction_id'    	=> $transaction->id,
						'receipt_id'    		=> $receiptItem->id,
						'account_no'    		=> '',
						'auth_code'    			=> '',
						'card_balance'	 		=> 0,
						'change'       			=> 0,
						'entry_mode'    		=> '',
						'gift_card_ref'  		=> '',
						'host_response'        	=> '',
						'payment_type'        	=> $paymentList->cardName,
						'round_off'   			=> 0,
						'tendered'   			=> 0,
						'terminal_id'   		=> '',
						'trans_amount'   		=> $request->total_paid,
						'trans_date'   			=> date('Y-m-d'),
						'trans_id'   			=> '',
						'trans_ref'   			=> '',
						'trans_status'   		=> 'Active',
						'trans_time'   			=> date('H:i:s'),
						'trans_total_amount'   	=> $request->total_paid,
						'trans_type'   			=> $request->type,
						'source_type'   	    => $request->type,
						'description'   	    => $request->memo,
						'receipt_no'   	    	=> $receiptNo,
						'created_by'   	    	=> Auth::user()->id,
						'branch_id'   	    	=> Auth::user()->branch_id,
					]);

					$payments->save();
				}

				$response = response()->json([
					'alert' =>'info',
					'msg'   =>'Receipt Created ',
					'rno' => receiptNo
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
		$receipt = Receipt::with([
			'profileName:profilers.id,profilers.account_title as profileName',
			'receiptBalance',
		])
		->where('id',$id)
		->get();

		$sub_receipt = SubReceipt::with([
			'chartName:chart_accounts.id,chart_accounts.account_name as chartName',
		])
		->where('receipt_id',$id)
		->get();

		$response = response()->json([
			'receipt' 		=> $receipt,
			'subReceipt'   => $sub_receipt
		]);

		return $response;
	}

	public function searchReceiptTransaction(Request $request)
	{
		$request->validate([
			'receipt_no' => ['required']
		]);

		$receipt = Receipt::with([
			'profileName:profilers.id,profilers.account_title as profileName',
		])
		->where('receipt_no',$request->receipt_no)->get()->first();

		if($receipt != NULL)
		{
			$sub_receipt = SubReceipt::with([
				'chartName:chart_accounts.id,chart_accounts.account_name as chartName',
			])
			->where('receipt_id',$receipt->id)
			->get();
		}
		else
		{
			$sub_receipt = [];
		}


		$response = response()->json([
			'receipt'   => $receipt,
			'subReceipt'   => $sub_receipt
		]);

		return $response;
	}

	public function getTransactionReceipt(Request $request)
	{
		$request->validate([
			'id' => ['required']
		]);

		$receipt = Receipt::with([
			'profileName:profilers.id,profilers.account_title as profileName',
			'receiptBalance',
		])
		->where('id',$request->id)
		->get()->first();

		$sub_receipt = SubReceipt::with([
			'chartName:chart_accounts.id,chart_accounts.account_name as chartName',
		])
		->where('receipt_id',$request->id)
		->get();

		$storeDetail = Branch::with([
			'taxName1:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName2:chart_accounts.id,chart_accounts.account_name as chartName',
			'taxName3:chart_accounts.id,chart_accounts.account_name as chartName',
		])
		->where('id',$receipt['branch_id'])
		->first();

		$response = response()->json([
			'storeDetail' 	=> $storeDetail,
			'receipt' 		=> $receipt,
			'subReceipt'   => $sub_receipt
		]);

		return $response;
	}


	public function update(Request $request)
	{
		$request->validate([
			'id'     		 => ['required'],
			'profile_id'     => ['required'],
			'receipt_date'   => ['required'],
			'receipt_due_date'   => ['required'],
			'memo' 			 => ['required'],
			'type' 			 => ['required'],
			'item_list' 	 => ['required'],
			'counter_entry'  => ['required'],
			'status' 		 => ['required'],
			'total_tax1' 	 => ['required'],
			'total_tax2' 	 => ['required'],
			'total_tax3' 	 => ['required'],
			'total_gross' 	 => ['required'],
			'total_discount' => ['required'],
			'total_tax' 	 => ['required'],
			'total_bill' 	 => ['required'],
			'total_paid' 	 => ['required'],
		]);

		DB::beginTransaction();

		try
		{
			$itemLists = json_decode($request->item_list);
			$counterEntry = json_decode($request->counter_entry);

			if($itemLists != NULL AND $counterEntry != null)
			{
				$receipt = Receipt::find($request->id);


				$receipt->receipt_date   = $request->receipt_date;
				$receipt->receipt_due_date   = $request->receipt_due_date;
				$receipt->description    = $request->memo;
				$receipt->profile_id     = $request->profile_id;
				$receipt->total_discount = $request->total_discount;
				$receipt->gross_total    = $request->total_gross;
				$receipt->total_tax1   	 = $request->total_tax1;
				$receipt->total_tax2   	 = $request->total_tax2;
				$receipt->total_tax3   	 = $request->total_tax3;
				$receipt->total_tax   	 = $request->total_tax;
				$receipt->total_bill   	 = $request->total_bill;
				$receipt->type   	 	 = $request->type;
				$receipt->update();

				foreach($itemLists as $item)
				{
					$subReceipt = new SubReceipt([
						'receipt_id'     		=> $receipt->id,
						'sub_transaction_id'    => $item->accountID,
						'qty'	 	  			=> $item->quantity,
						'price'       			=> $item->price,
						'discount'    			=> $item->discount,
						'tax1'        			=> $item->tax1Value,
						'tax2'        			=> $item->tax2Value,
						'tax3'        			=> $item->tax3Value,
						'sub_total'   			=> $item->subTotal,
					]);

					$subReceipt->save();
				}


				$trans = Transaction::find($receipt->transaction_id);
				$trans->narration = $request->memo;
				$trans->update();

				foreach($counterEntry as $item)
				{
					$subTransaction = new SubTransaction([
						'transaction_id'     => $receipt->transaction_id,
						'account_id'     	 => $item->accountID,
						'account_name'	 	 => $item->accountHead,
						'amount'      	     => $item->amount,
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

	public function saveReceiptTransaction(Request $request)
	{
		$request->validate([
			'type'     				=> ['required'],
			'amount'    			=> ['required'],
			'method'   				=> ['required'],
			'id'   					=> ['required'],
			'total_paid'   			=> ['required'],
		]);

		DB::beginTransaction();

		try
		{
			$method = json_decode($request->method);
			$counterEntry = json_decode($request->counter_entry);

			$method->amount = $request->total_paid;


			if($request->type == 'SLS' AND $request->description == '')
			{
				$request->description = 'Received sales receipt payment';
			}
			else if($request->type == 'RFR' AND $request->description == '')
			{
				$request->description = 'Paid refund receipt payment';
			}
			else if($request->type == 'INV' AND $request->description == '')
			{
				$request->description = 'Received invoice receipt payment';
			}

			$transaction = new Transaction([
				'narration'         => $request->description,
				'generated_source'  => $request->type,
				'branch_id'         => Auth::user()->branch_id,
			]);

			$transaction->save();

			foreach($counterEntry as $item)
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


			//IF USED CARDS
			$t = new  Receipt();
			$t->passBankTransaction($transaction->id,$request->receiptNo,$request->description,$method,$request->profileId,$request->type);

			$payments = new ReceiptPayment([
				'transaction_id'    	=> $transaction->id,
				'receipt_id'    		=> $request->id,
				'account_no'    		=> '',
				'auth_code'    			=> '',
				'card_balance'	 		=> 0,
				'change'       			=> 0,
				'entry_mode'    		=> '',
				'gift_card_ref'  		=> '',
				'host_response'        	=> '',
				'payment_type'        	=> $method->cardName,
				'round_off'   			=> 0,
				'tendered'   			=> 0,
				'terminal_id'   		=> '',
				'trans_amount'   		=> $method->amount,
				'trans_date'   			=> date('Y-m-d'),
				'trans_id'   			=> '',
				'trans_ref'   			=> '',
				'trans_status'   		=> 'Active',
				'trans_time'   			=> date('H:i:s'),
				'trans_total_amount'   	=> $method->amount,
				'trans_type'   			=> $request->type,
				'source_type'   	    => $request->type,
				'description'   	    => $request->description,
				'receipt_no'   	    	=> $request->receiptNo,
				'created_by'   	    	=> Auth::user()->id,
				'branch_id'   	    	=> Auth::user()->branch_id,
			]);

			$payments->save();


			$response = response()->json([
				'alert' =>'info',
				'msg'   =>'Payment proceeded successfully'
			]);

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
