<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnVouchers extends Model
{
    use HasFactory;
    protected $fillable = [
        'supplier_id',
        'voucher_number',
        'return_date',
        'product_name',
        'exp_date',
        'batch_no',
       'ret_quantity',
       'bill_no',
       'bill_date',
        'purchase_price',
        'tax1',
        'tax2',
        'total',
    ];

    
}


	