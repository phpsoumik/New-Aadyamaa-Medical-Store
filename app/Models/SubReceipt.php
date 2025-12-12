<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubReceipt extends Model
{
    use HasFactory;
    protected $fillable = [
        'receipt_id',
        'sub_transaction_id',
        'qty',
        'price',
        'discount',
        'tax1',
        'tax2',
        'tax3',
        'sub_total',
    ];

	public function chartName()
	{
		return $this->belongsTo(ChartAccount::class,'sub_transaction_id');
	}
}
