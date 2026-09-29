<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CmsContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'password' => Hash::make('password'),
        ], $overrides));
    }

    public function test_editor_can_create_testimonial(): void
    {
        $editor = $this->admin(['role' => UserRole::Editor]);

        $this->actingAs($editor)
            ->post(route('admin.testimonials.store'), [
                'customer_name' => 'Priya Shah',
                'company' => 'Retail Demo',
                'position' => 'Owner',
                'review' => 'CEBINOVA delivered a clean storefront our team can actually use.',
                'rating' => 5,
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', [
            'customer_name' => 'Priya Shah',
            'company' => 'Retail Demo',
        ]);
    }

    public function test_public_portfolio_routes_are_removed(): void
    {
        $this->get('/portfolio')->assertNotFound();
        $this->get('/portfolio/kirana-store')->assertNotFound();
    }

    public function test_public_blog_routes_are_removed(): void
    {
        $this->get('/blog')->assertNotFound();
        $this->get('/blog/live-post')->assertNotFound();
    }

    public function test_viewer_forbidden_from_create(): void
    {
        $viewer = $this->admin(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.testimonials.create'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.testimonials.store'), [
                'customer_name' => 'Should Fail',
                'review' => 'Should fail.',
            ])
            ->assertForbidden();
    }
}
