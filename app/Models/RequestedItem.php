<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_id',
        'medicine_name',
        'quantity',
        'order_date',
        'advance_payment',
        'has_advance',
        'status',
        'branch_id'
    ];
    
    protected $casts = [
        'order_date' => 'date',
        'advance_payment' => 'decimal:2',
        'has_advance' => 'boolean'
    ];
    
    public function customer()
    {
        return $this->belongsTo(Profiler::class, 'customer_id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
