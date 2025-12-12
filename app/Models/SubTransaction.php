<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'account_id',
        'account_name',
        'amount',
        'type'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function voucherItems()
    {
        return $this->belongsTo(Voucher::class);
    }
}
