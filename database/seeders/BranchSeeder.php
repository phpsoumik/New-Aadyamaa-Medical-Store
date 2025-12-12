<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('branches')->insert([
            [
                'name'                  => 'Medix The Pharmacy Manager',
                'code'                  => '0001',
                'address'               => '13th Street. 47 W 13th St, New York, NY 10011',
                'description'           => '',
                'license_no'            => '123456987',
                'email'                 => '',
                'contact'               => '03472394224',
                'status'                => 'Active',
                'show_1'                => 'true',
                'tax_name_1'            => 'SGST',
                'tax_value_1'           => 3,
                'required_optional_1'   => 'Required',
                'link1'                 => 10,
                'show_2'                => 'true',
                'tax_name_2'            => 'CGST',
                'tax_value_2'           => 3,
                'required_optional_2'   => 'Required',
                'link2'                 => 9,
                'show_3'                => 'false',
                'tax_name_3'            => '',
                'tax_value_3'           => 0,
                'required_optional_3'   => 'Optional',
                'link3'                 => 5,
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ]
        ]);
    }
}
