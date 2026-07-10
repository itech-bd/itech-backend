<?php

namespace Modules\NewsUpdates\Database\Seeders;

use Illuminate\Database\Seeder;

class NewsUpdatesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DemoNewsSeeder::class,
        ]);
    }
}
