<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferStore extends Model
{
    use HasFactory;
    protected $fillable = [
    'receipt_id',
    'branch_id'
    ];

    public function transferBranch()
	{	
		return $this->belongsTo(Branch::class,'branch_id');
	}
}
