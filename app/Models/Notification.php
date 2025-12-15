<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_id',
        'requested_item_id', 
        'message',
        'type',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(Profiler::class, 'customer_id');
    }

    public function requestedItem()
    {
        return $this->belongsTo(RequestedItem::class, 'requested_item_id');
    }
}