<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAuthTest extends TestCase
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

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_and_see_dashboard(): void
    {
        $user = $this->admin(['email' => 'boss@cebinova.test']);

        $this->post(route('admin.login.store'), [
            'email' => 'boss@cebinova.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $this->admin([
            'email' => 'inactive@cebinova.test',
            'is_active' => false,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'inactive@cebinova.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    public function test_viewer_cannot_manage_users(): void
    {
        $viewer = $this->admin(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_editor_can_update_lead_but_not_users(): void
    {
        $editor = $this->admin(['role' => UserRole::Editor]);
        $lead = Lead::factory()->create(['status' => 'New']);

        $this->actingAs($editor)
            ->put(route('admin.leads.update', $lead), [
                'status' => 'Contacted',
                'notes' => 'Called',
            ])
            ->assertRedirect();

        $this->assertSame('Contacted', $lead->fresh()->status);

        $this->actingAs($editor)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_cannot_delete_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHasErrors('user');
    }

    public function test_settings_update_persists(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'phone' => '+91 99999 00000',
                'email' => 'hello@cebinova.test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', [
            'phone' => '+91 99999 00000',
            'email' => 'hello@cebinova.test',
        ]);
    }

    public function test_service_crud_and_public_listing_shows_active_only(): void
    {
        $admin = $this->admin(['role' => UserRole::Editor]);

        $this->actingAs($admin)
            ->post(route('admin.services.store'), [
                'name' => 'Website Development',
                'slug' => 'web-development-test',
                'short_description' => 'Sites',
                'full_description' => 'Full',
                'features_text' => "A\nB",
                'is_active' => 1,
                'sort_order' => 1,
            ])
            ->assertRedirect(route('admin.services.index'));

        Service::query()->create([
            'name' => 'Hidden',
            'slug' => 'hidden-service',
            'is_active' => false,
            'sort_order' => 99,
        ]);

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee('Website Development')
            ->assertDontSee('Hidden');
    }

    public function test_media_upload_validation(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');

        $this->actingAs($admin)
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('logo.jpg'),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('media', 1);
    }

    public function test_package_seeder_shape_supports_marketing_helper(): void
    {
        $this->seed(\Database\Seeders\PackageSeeder::class);

        $this->assertTrue(Package::query()->marketing()->exists());
        $this->assertSame(4999, \App\Support\MarketingPackages::priceAmount('Regular Marketing', 'Monthly'));
    }
}
