<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'transaction_id',
        'receipt_no',
        'transaction_no',
        'receipt_date',
        'description',
        'bank_id',
        'account_id',
        'account_head',
        'amount',
        'created_by',
        'status',
        'type',
        'entity',
        'branch_id'
    ];

    public function generateID($prefix)
	{
		$storeID = Auth::user()->branch_id;
		$year = date("y");
		$fullYear = date('Y');
		$branchInfo = Branch::find(Auth::user()->branch_id);

		$storeCode = $branchInfo['code'];
		$initialStage = $prefix.'-'.$storeCode.$year;

		$results = DB::select('SELECT SUBSTR(MAX(receipt_no),11,17) as serialNo from bank_transactions where branch_id = :branchID   AND  YEAR(receipt_date) = :receiptDate AND type = :receiptType', ['branchID' => $storeID ,'receiptDate' => $fullYear,'receiptType' => $prefix]);

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
	
	public function bankName()
	{
		return $this->belongsTo(Banks::class,'bank_id');
	}

    
    public function profileName()
	{	
		return $this->hasOneThrough(
			Profiler::class, //Final model we wish to access
			BankTransactionPayees::class, //The name of the intermediate model
			'bank_transaction_id', //Foreign key on sub_transaction table
			'id', //Foreign key on profile table
			'id', //Local key on transaction table
			'profile_id', //Local key on sub_transaction table
		)->withDefault([
			'profileName' => 'None'
		]);
	}
}
