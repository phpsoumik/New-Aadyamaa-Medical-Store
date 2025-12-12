<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankTransactionPayees extends Model
{
    use HasFactory;
    protected $fillable = [
        'bank_transaction_id',
        'profile_id'
    ];
}