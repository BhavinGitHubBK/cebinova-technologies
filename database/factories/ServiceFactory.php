<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(),
            'category' => 'Support',
            'icon' => 'shield-check',
            'short_description' => fake()->sentence(),
            'full_description' => fake()->paragraph(),
            'featured_image' => null,
            'hero_title' => 'Service',
            'hero_subtitle' => 'Service subtitle',
            'badge' => null,
            'audience' => 'Business owners',
            'outcome' => 'Reliable support',
            'next_label' => 'Request support',
            'next_route' => null,
            'next_param' => null,
            'features' => [],
            'cta_label' => 'Get started',
            'cta_url' => '#',
            'sort_order' => 1,
            'is_featured' => false,
            'is_active' => true,
            'seo_title' => null,
            'seo_description' => null,
        ];
    }
}
