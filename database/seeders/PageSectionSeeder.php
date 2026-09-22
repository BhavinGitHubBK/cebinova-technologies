<?php

namespace Database\Seeders;

use App\Models\PageSection;
use Illuminate\Database\Seeder;

class PageSectionSeeder extends Seeder
{
    public function run(): void
    {
        PageSection::query()->updateOrCreate(
            ['page' => 'home', 'key' => 'hero'],
            [
                'heading' => 'Technology That Helps Every Business Grow.',
                'subheading' => config('cebinova.positioning'),
                'body' => 'From your first website to eCommerce, custom software and AI-powered automation, CEBINOVA gives your business the technology it needs to start, operate and grow.',
                'cta_label' => 'Get Free Consultation',
                'cta_url' => '/contact',
                'secondary_cta_label' => 'Explore Our Solutions',
                'secondary_cta_url' => '/solutions',
                'is_visible' => true,
                'sort_order' => 1,
            ]
        );

        PageSection::query()->updateOrCreate(
            ['page' => 'home', 'key' => 'cta'],
            [
                'heading' => 'Ready to Take Your Business Forward?',
                'subheading' => 'Next step',
                'body' => 'Start with the solution you need today. CEBINOVA can grow with you tomorrow.',
                'cta_label' => 'Get Free Consultation',
                'cta_url' => '/contact',
                'secondary_cta_label' => 'Talk to CEBINOVA',
                'secondary_cta_url' => '/whatsapp',
                'is_visible' => true,
                'sort_order' => 90,
            ]
        );
    }
}
