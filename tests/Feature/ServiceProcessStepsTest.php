<?php

namespace Tests\Feature;

use App\Models\Service;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProcessStepsTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_steps_show_their_label_or_a_step_number(): void
    {
        $this->seed(ServiceSeeder::class);
        $service = Service::query()->where('slug', 'web-development')->firstOrFail();
        $service->sections()->where('type', 'process')->delete();
        $process = $service->sections()->create(['type' => 'process', 'title' => 'How we deliver', 'subtitle' => 'Simple steps.', 'is_active' => true, 'sort_order' => 1]);
        $process->items()->create(['title' => 'Discovery call', 'description' => 'Map the workflow.', 'value' => 'Week 1', 'is_active' => true, 'sort_order' => 1]);
        $process->items()->create(['title' => 'Build and launch', 'description' => 'Build it.', 'is_active' => true, 'sort_order' => 2]);

        $this->get(route('services.show', 'web-development'))
            ->assertOk()
            ->assertSeeInOrder(['Week 1', 'Discovery call', '02', 'Build and launch']);
    }
}
