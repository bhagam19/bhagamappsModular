<?php

namespace Modules\Apps\database\seeders;

use Illuminate\Database\Seeder;

class AppsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            AppSeeder::class,
        ]);
    }
}
