<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PosReceipt extends Model
{
	use HasFactory;
	protected $fillable = [
		'transaction_id',
		'receipt_no',
		'discount',
		'profile_id',
		'payment_method',
		'total_gross_amt',
		'total_bill',
		'total_tendered',
		'total_change',
		'total_tax1',
		'total_tax2',
		'total_tax3',
		'total_tax',
		'description',
		'doctor_details',
		'patient_details',
		'bill_no',
		'receipt_date',
		'return_receipt',
		'type',
		'status',
		'created_by',
		'branch_id',
	];

	public function generateID($prefix)
	{
		$storeID = Auth::user()->branch_id;
		$year = date("y");
		$fullYear = date('Y');
		$branchInfo = Branch::find(Auth::user()->branch_id);

		$storeCode = $branchInfo['code'];
		$initialStage = $prefix.'-'.$storeCode.$year;

		$results = DB::select('SELECT SUBSTR(MAX(receipt_no),11,17) as serialNo from pos_receipts where branch_id = :branchID   AND  YEAR(receipt_date) = :receiptDate AND type = :receiptType', ['branchID' => $storeID ,'receiptDate' => $fullYear,'receiptType' => $prefix]);

		if ($results[0]->serialNo != NULL)
		{
			$activityID = (int) $results[0]->serialNo;
			$activityID = $activityID + 1;
			$activityID = str_pad($activityID,8, '0', STR_PAD_LEFT);
			$activityID = $initialStage.$activityID;
		}
		else
		{
			$id = 0;
			$activityID = $initialStage.str_pad($id, 8, '0', STR_PAD_RIGHT);
		}

		return $activityID;
	}

	public function itemList()
	{
		return $this->hasMany(PosSubReceipt::class,'pos_receipt_id');
	}

	public function transaction()
	{
		return $this->belongsTo(Transaction::class);
	}
	
	public function branch()
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}

	public function userName()
	{
		return $this->belongsTo(User::class,'created_by');
	}

	public function profileName()
	{	
		return $this->belongsTo(Profiler::class,'profile_id');
	}
	
	public function subReceipt()
	{	
		return $this->hasMany(SubReceipt::class,'receipt_id');
	}
	
	public function receiptBalance()
	{	
		return $this->hasMany(ReceiptPayment::class,'receipt_id')
		->where('source_type','INE')
		->orWhere('source_type','RFD')
		->orWhere('source_type','PUR')
		->orWhere('source_type','RPU')
		->orWhere('source_type','TRN')
		->orWhere('source_type','ASR');
	}
	
	public function transferBranch()
	{	
		return $this->hasOne(TransferStore::class,'receipt_id','id');
	}

	public function passBankTransaction($transaction_id,$receiptNo,$description,$paymentLists,$profile_id)
	{
		if($paymentLists != NULL)
		{
			foreach($paymentLists as $item)
			{
				if($item->paymentType != 'Cash')
				{
					$item = new BankTransaction([
						'transaction_id' => $transaction_id,
						'receipt_no'     => $receiptNo,
						'transaction_no' => $item->paymentType,
						'receipt_date'   => date('Y-m-d'),
						'description'    => $description,
						'bank_id'        => $item->bankID,
						'account_id'     => $this->getAccountID($item->transType)['id'],
						'account_head'   => $this->getAccountID($item->transType)['name'],
						'amount'         => $item->transTotalAmount,
						'created_by'     => Auth::user()->id,
						'type'           => $item->transType,
						'entity'         => 'No',
						'branch_id'      => Auth::user()->branch_id,
						'status'         => 'Active'
					]);

					$item->save();

					$item = new BankTransactionPayees([
						'bank_transaction_id' => $item->id,
						'profile_id' => $profile_id,
					]);

					$item->save();
				}
			}
		}
	}

	public function getAccountID($type)
	{
		$a = array('id'=> 0,'name' => '');

		if($type == 'INE' OR $type == 'RFD' OR $type == 'TRN')
		{
			$a = array('id'=> 48,'name' => 'Sales Revenue');
		}
		else if($type == 'PUR' OR $type == 'RPU')
		{
			$a = array('id'=> 3,'name' => 'Inventory');
		}

		return $a;
	}
}
