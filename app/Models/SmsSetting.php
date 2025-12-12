<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'enable_notification',
        'domain_name',
        'account_email',
        'token_key',
        'test_no',
        'created_by',
        'status',
        'branch_id'
    ];

    protected $table = 'sms_setting'; 
}
