<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleService extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_name',
        'description',
        'charges',
        'income_account',
        'status',
        'branch_id'
    ];
}
