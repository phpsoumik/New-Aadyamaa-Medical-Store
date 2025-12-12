<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestedItem extends Model
{
    use HasFactory;
    protected $fillable = ['item_name','description','status','branch_id'];
}
