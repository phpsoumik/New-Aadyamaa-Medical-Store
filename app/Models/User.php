<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Branch;
use App\Models\Banks;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'branch_id',
        'role',
        'address',
        'contact',
        'image',
        'description',
        'name',
        'email',
        'password',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getUserStores()
    {
       $branches = [];       
       $role =  Auth::user()->roles->pluck('name')->first();


       if($role != NULL)
       {
            if($role == 'Admin')
            {
                $branches = Branch::where('status', 'Active')
                ->orderBy('id','DESC')
                ->get();
            }
            else
            {
                $branches = Branch::where('id',Auth::user()->branch_id)
                ->get();
            }
       }
       else
       {
            $branches = Branch::where('id',Auth::user()->branch_id)
            ->get();    
       }
       
       return $branches;
    } 
    
    public function getUserBanks()
    {
       $banks = [];       
       $role =  Auth::user()->roles->pluck('name')->first();


       if($role != NULL)
       {
            if($role == 'Admin')
            {
                $banks = Banks::where('status', 'Active')
                ->orderBy('id','DESC')
                ->get();
            }
            else
            {
                $banks = Banks::where('branch_id',Auth::user()->branch_id)
                ->get();
            }
       }
       else
       {
            $banks = Banks::where('branch_id',Auth::user()->branch_id)
            ->get();    
       }
       
       return $banks;
    }
}
