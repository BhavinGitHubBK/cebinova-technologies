<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('cebinova.page.faq', []) as $index => $item) {
            Faq::query()->updateOrCreate(
                [
                    'page' => 'general',
                    'question' => $item['q'],
                ],
                [
                    'answer' => $item['a'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
