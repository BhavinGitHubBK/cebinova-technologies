<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Project;
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

    public function test_editor_can_create_project(): void
    {
        $editor = $this->admin(['role' => UserRole::Editor]);

        $this->actingAs($editor)
            ->post(route('admin.projects.store'), [
                'title' => 'Demo Retail Store',
                'slug' => 'demo-retail-store',
                'preview' => 'store',
                'type' => 'Demo Solution',
                'short_description' => 'A published portfolio project.',
                'technologies_text' => "Retail\nWhatsApp",
                'status' => 'published',
                'sort_order' => 1,
            ])
            ->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('projects', [
            'slug' => 'demo-retail-store',
            'status' => 'published',
            'title' => 'Demo Retail Store',
        ]);
    }

    public function test_public_portfolio_shows_published_project(): void
    {
        Project::query()->create([
            'title' => 'Published Gallery Item',
            'slug' => 'published-gallery-item',
            'preview' => 'website',
            'type' => 'Concept Project',
            'technologies' => ['Web'],
            'short_description' => 'Visible on the public portfolio.',
            'status' => 'published',
            'sort_order' => 1,
            'is_featured' => false,
        ]);

        Project::query()->create([
            'title' => 'Draft Hidden Item',
            'slug' => 'draft-hidden-item',
            'preview' => 'website',
            'type' => 'Concept Project',
            'technologies' => ['Web'],
            'short_description' => 'Should not appear.',
            'status' => 'draft',
            'sort_order' => 2,
            'is_featured' => false,
        ]);

        $this->get(route('portfolio'))
            ->assertOk()
            ->assertSee('Published Gallery Item')
            ->assertDontSee('Draft Hidden Item');
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
            ->get(route('admin.projects.create'))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.projects.store'), [
                'title' => 'Should Fail',
                'status' => 'draft',
            ])
            ->assertForbidden();
    }
}
