<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
	use HasFactory;
	protected $fillable = [
		'transaction_id',
		'receipt_no',
		'receipt_date',
		'receipt_due_date',
		'payment_method',
		'description',
		'profile_id',
		'total_discount',
		'gross_total',
		'total_tax1',
		'total_tax2',
		'total_tax3',
		'total_tax',
		'total_bill',
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

		$results = DB::select('SELECT SUBSTR(MAX(receipt_no),11,17) as serialNo from receipts where branch_id = :branchID   AND  YEAR(receipt_date) = :receiptDate AND type = :receiptType', ['branchID' => $storeID ,'receiptDate' => $fullYear,'receiptType' => $prefix]);

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
	
	public function cashTransactions()
	{	
		return $this->hasMany(Voucher::class,'transaction_id','transaction_id');
	}

	public function bankTransactions()
	{	
		return $this->hasMany(BankTransaction::class,'transaction_id','transaction_id');
	}
	
	public function receiptBalance()
	{	
		return $this->hasMany(ReceiptPayment::class,'receipt_id')
		->where('source_type', 'SLS')
		->orWhere('source_type', 'RFR')
		->orWhere('source_type', 'INV');
	}

	public function passBankTransaction($transaction_id,$receiptNo,$description,$paymentLists,$profile_id,$transType)
	{
		if($paymentLists != NULL)
		{
			
			if($paymentLists->cardName != 'Cash')
			{
				$item = new BankTransaction([
					'transaction_id' => $transaction_id,
					'receipt_no'     => $receiptNo,
					'transaction_no' => $paymentLists->cardName,
					'receipt_date'   => date('Y-m-d'),
					'description'    => $description,
					'bank_id'        => $paymentLists->bankId,
					'account_id'     => $this->getAccountID($transType)['id'],
					'account_head'   => $this->getAccountID($transType)['name'],
					'amount'         => $paymentLists->amount,
					'created_by'     => Auth::user()->id,
					'type'           => $transType,
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

	public function getAccountID($type)
	{
		$a = array('id'=> 0,'name' => '');

		if($type == 'INV' OR $type == 'SLS')
		{
			$a = array('id'=> 4,'name' => 'Accounts receivable');
		}
		else if($type == 'RFR')
		{
			$a = array('id'=> 5,'name' => 'Accounts payable');
		}

		return $a;
	}

	

	public static function boot() {
		parent::boot();
		static::saving(function($receipt) { 
			$receipt->subReceipt()->each(function($st) {
				$st->delete();
			 });
		});
	}
}
