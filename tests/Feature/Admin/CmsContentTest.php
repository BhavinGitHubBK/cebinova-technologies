<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\PageSection;
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

    public function test_editor_can_create_page_section(): void
    {
        $editor = $this->admin(['role' => UserRole::Editor]);

        $this->actingAs($editor)
            ->post(route('admin.page-sections.store'), [
                'page' => 'about',
                'key' => 'hero',
                'heading' => 'About CEBINOVA',
                'sort_order' => 1,
                'is_visible' => 1,
            ])
            ->assertRedirect(route('admin.page-sections.edit', PageSection::query()->where('key', 'hero')->where('page', 'about')->first()));

        $this->assertDatabaseHas('page_sections', [
            'page' => 'about',
            'key' => 'hero',
            'heading' => 'About CEBINOVA',
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

    public function test_faq_routes_are_removed(): void
    {
        $this->get('/admin/faqs')->assertNotFound();
        $this->actingAs($this->admin())
            ->get('/admin/faqs')
            ->assertNotFound();
    }

    public function test_viewer_forbidden_from_create(): void
    {
        $viewer = $this->admin(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.page-sections.create'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.page-sections.store'), [
                'page' => 'about',
                'key' => 'hero',
                'heading' => 'Should Fail',
            ])
            ->assertForbidden();
    }
}
