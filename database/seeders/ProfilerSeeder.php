<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('profilers')->insert([
            [
                'account_title'         => 'Walk In',
                'email_address'         => '',
                'contact_no'            => '',
                'national_id'           => '',
                'address'               => '',
                'description'           => '',
                'account_type'          => 'Default Customer',
                'status'                => 'Active',
                'created_user'          => 0,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ]
        ]);
    }
}
