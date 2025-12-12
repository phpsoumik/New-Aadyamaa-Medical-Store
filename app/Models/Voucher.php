<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Voucher extends Model
{
	use HasFactory;
	protected $fillable = [
		'voucher_no',
		'transaction_id',
		'profile_id',
		'profile_name',
		'voucher_date',
		'memo',
		'total_amount',
		'created_by',
		'account_type',
		'type',
		'branch_id',
		'status'
	];

	public function generateID($prefix)
	{
		$storeID = Auth::user()->branch_id;
		$year = date("y");
		$fullYear = date('Y');
		$branchInfo = Branch::find(Auth::user()->branch_id);

		$storeCode = $branchInfo['code'];
		$initialStage = $prefix.'-'.$storeCode.$year;

		$results = DB::select('SELECT SUBSTR(MAX(voucher_no),11,17) as serialNo from vouchers where branch_id = :branchID   AND  YEAR(voucher_date) = :receiptDate AND type = :voucherType', ['branchID' => $storeID ,'receiptDate' => $fullYear,'voucherType' => $prefix]);

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

	public function checkProfileID($profileID)
	{
		$count = Voucher::where('type', 'OPB')
		->where('profile_id',$profileID)
		->count();

		return $count; 
	}
}
