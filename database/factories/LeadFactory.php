<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'business_name' => fake()->company(),
            'phone' => '9876543210',
            'whatsapp' => '9876543210',
            'email' => fake()->safeEmail(),
            'business_type' => 'Retail',
            'service' => 'Website Development',
            'package_category' => null,
            'plan_duration' => null,
            'selected_price' => null,
            'city' => 'Ahmedabad',
            'budget' => 'To be discussed',
            'message' => fake()->sentence(),
            'free_consultation' => true,
            'source' => 'Website Contact',
            'status' => Lead::STATUS_NEW,
        ];
    }
}
