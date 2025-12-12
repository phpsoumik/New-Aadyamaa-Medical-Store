<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OptionTagsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('option_tags')->insert([
            [
                'option_name'    => 'Nestle',
                'option_type'    => 'Brands',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'Uni Liver',
                'option_type'    => 'Brands',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'Fever',
                'option_type'    => 'Brand Sectors',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'Tablet',
                'option_type'    => 'Category',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'Finished Product',
                'option_type'    => 'Products Type',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'Raw Material',
                'option_type'    => 'Products Type',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],

            [
                'option_name'    => 'Liquid',
                'option_type'    => 'Products Type',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'MG',
                'option_type'    => 'Units',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'ML',
                'option_type'    => 'Units',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'KG',
                'option_type'    => 'Units',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'option_name'    => 'LTR',
                'option_type'    => 'Units',
                'description'    => NULL,
                'status'         => 'Active',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
