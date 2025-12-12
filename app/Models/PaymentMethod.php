<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = ['card_name','card_charges','bank_id','status','description','charge_customer','created_by','branch_id'];

    public function BankName()
	{	
		return $this->belongsTo(Banks::class,'bank_id');
	}
}
