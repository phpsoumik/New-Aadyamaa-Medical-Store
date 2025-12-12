<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'branch_id'           => 1,
                'role'                => 3,
                'address'             => '',
                'contact'             => '',
                'image'               => 'default.jpg',
                'description'         => '',
                'name'                => 'Admin',
                'email'               => 'admin@spantiklab.com',
                'email_verified_at'   => date('Y-m-d H:i:s'),
                'password'            => '$2y$10$jXU6rWeaEgQY99hwBUVff.CaRxnWNm7DDwauloa08f12zeWDHfh96',
                'status'              => 'Active',
                'remember_token'      => 'Yes',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
