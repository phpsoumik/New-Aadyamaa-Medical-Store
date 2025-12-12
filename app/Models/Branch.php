<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'address',
        'description',
        'license_no',
        'email',
        'contact',
        'status',
        'show_1',
        'tax_name_1',
        'tax_value_1',
        'required_optional_1',
        'link1',
        'show_2',
        'tax_name_2',
        'tax_value_2',
        'required_optional_2',
        'link2',
        'show_3',
        'tax_name_3',
        'tax_value_3',
        'required_optional_3',
        'link3',
    ];

    public function taxName1()
    {
        return $this->belongsTo(ChartAccount::class,'link1');
    }
    
    public function taxName2()
    {
        return $this->belongsTo(ChartAccount::class,'link2');
    }
    
    public function taxName3()
    {
        return $this->belongsTo(ChartAccount::class,'link3');
    }
}