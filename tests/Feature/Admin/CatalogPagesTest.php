<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_and_forms_render_for_an_admin(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        foreach ([
            'admin.services.index' => 'Add service',
            'admin.services.create' => 'Short description',
            'admin.industries.index' => 'Add industry',
            'admin.industries.create' => 'Short description',
            'admin.packages.index' => 'Add package',
            'admin.packages.create' => 'Teaser',
            'admin.pricing-options.index' => 'Add charge',
            'admin.page-sections.index' => 'Add section',
            'admin.demos.index' => 'Add demo',
            'admin.users.index' => 'Add user',
            'admin.users.create' => 'Confirm password',
            'admin.activity-logs.index' => 'All modules',
            'admin.settings.edit' => 'GST percent',
            'admin.profile.edit' => 'Current password',
        ] as $route => $text) {
            $this->get(route($route))->assertOk()->assertSee($text);
        }
    }
}
