<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where(['name' => 'Admin'])->first();
        
        $permission = Permission::get();

        if($permission != NULL)
        {
            foreach($permission as $p)
            {
                $p->assignRole($role);
            }
        }

        $user = User::find(1);
        $user->assignRole($role);
    }
}
