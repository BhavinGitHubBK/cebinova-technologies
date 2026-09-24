<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            ServiceSeeder::class,
            PackageSeeder::class,
            ProjectSeeder::class,
            FaqSeeder::class,
            PageSectionSeeder::class,
        ]);
    }
}
