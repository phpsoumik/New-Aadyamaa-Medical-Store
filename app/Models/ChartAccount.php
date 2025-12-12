<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartAccount extends Model
{
    use HasFactory;
    protected $fillable = ['account_code','account_name','account_nature','account_type','expense_type','status'];
}
