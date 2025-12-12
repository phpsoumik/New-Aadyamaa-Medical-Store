<?php

namespace App\Imports;

use App\Models\OptionTags;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class StocksImport implements ToModel
{
    private $records = [];
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
            
      //  die();
        // return new OptionTags([
        //    'option_name'     => $row[0],
        //    'option_type'    => $row[1], 
        //    'description' => $row[2], 
        //    'status' => 'fake', 
        // ]);
      
    }
}