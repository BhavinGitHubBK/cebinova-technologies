<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        BlogCategory::query()->updateOrCreate(
            ['slug' => 'news'],
            [
                'name' => 'News',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
