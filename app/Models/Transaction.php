<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'narration',
        'generated_source',
        'branch_id'
    ];

    public function voucher()
    {
        return $this->hasOne(Voucher::class);
    } 
    
    public function subTransaction()
    {
        return $this->hasMany(SubTransaction::class);
    }
    
    public function transactionEntries()
    {
        return $this->hasMany(SubTransaction::class,'transaction_id');
    }
    
    public function branchName()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
 
    public static function boot() {
        parent::boot();
        static::saving(function($transaction) { 
            $transaction->subTransaction()->each(function($st) {
                $st->delete();
             });
        });
    }
}
