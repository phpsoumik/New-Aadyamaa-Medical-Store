<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrinterReceipt extends Model
{
    use HasFactory;
    protected $fillable = ['receipt_heading','receipt_content','receipt_priority','status','branch_id'];
}
