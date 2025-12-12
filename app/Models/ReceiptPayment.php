<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'receipt_id',
        'transaction_id',
        'account_no',
        'auth_code',
        'card_balance',
        'change',
        'entry_mode',
        'gift_card_ref',
        'host_response',
        'payment_type',
        'round_off',
        'tendered',
        'terminal_id',
        'trans_amount',
        'trans_date',
        'trans_id',
        'trans_ref',
        'trans_status',
        'trans_time',
        'trans_total_amount',
        'trans_type',
        'source_type',
        'description',
        'receipt_no',
        'created_by',
        'branch_id',
    ];
}
