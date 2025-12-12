<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profiler extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_title',
        'email_address',
        'contact_no',
        'national_id',
        'address',
        'description',
        'account_type',
        'status',
        'created_user'
    ];
}
