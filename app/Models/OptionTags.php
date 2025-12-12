<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OptionTags extends Model
{
    use HasFactory;
    protected $fillable = ['option_name','option_type','description','status'];
}
