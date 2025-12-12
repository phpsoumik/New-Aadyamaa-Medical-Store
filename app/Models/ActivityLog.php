<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_role',
        'activity_action',
        'user_id',
        'branch_id'
    ];

    public function branchDetails()
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}

    public function userDetails()
	{
		return $this->belongsTo(User::class,'user_id');
	}
}
