<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    //sail artisan migrate:fresh --seed

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // BranchSeeder::class,
            // UserSeeder::class,
            // RolesSeeder::class,
            // OptionTagsSeeder::class,
            // COASeeder::class,
            // ProfilerSeeder::class,
            // AssignRoleSeeder::class,
                        PermissionSeeder::class,

        ]);
    }
}
