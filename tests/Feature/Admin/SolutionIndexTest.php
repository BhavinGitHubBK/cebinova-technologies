<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Solution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SolutionIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_solution_list_shows_counts_and_can_filter_inactive(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);
        Solution::query()->create([
            'name' => 'Live Kirana',
            'slug' => 'live-kirana',
            'category' => 'Retail',
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);
        Solution::query()->create([
            'name' => 'Hidden Studio',
            'slug' => 'hidden-studio',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.solutions.index'))
            ->assertOk()
            ->assertSee('Live Kirana')
            ->assertSee('hidden-studio')
            ->assertSee('Featured')
            ->assertSee('Add solution')
            ->assertSee('lead-sort', false);

        $this->actingAs($admin)
            ->get(route('admin.solutions.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Hidden Studio')
            ->assertDontSee('Live Kirana');

        $this->actingAs($admin)
            ->get(route('admin.solutions.index', ['sort' => 'name', 'dir' => 'desc']))
            ->assertOk()
            ->assertViewHas('solutions', function ($solutions) {
                $names = $solutions->pluck('name');

                return $names->search('Live Kirana') < $names->search('Hidden Studio');
            });
    }
}
