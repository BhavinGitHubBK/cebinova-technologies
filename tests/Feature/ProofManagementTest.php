<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Demo;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProofManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_inventory_migration_is_idempotent_and_preserves_links_features_and_images(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);
        $this->assertSame(6, Demo::count());
        foreach (config('cebinova.business_solutions') as $item) {
            $demo = Demo::where('slug', $item['slug'])->firstOrFail();
            $this->assertSame($item['demo'], $demo->local_demo);
            $this->assertSame($item['capabilities'], array_column($demo->features, 'name'));
            $this->assertSame($item['preview'] ? 'assets/'.$item['preview'] : null, $demo->thumbnail);
            $this->assertCount(1, $demo->solutions);
        }
        foreach (['kirana', 'jewellery-retail', 'professional-services'] as $slug) {
            $this->get('/demos/'.$slug)->assertOk()->assertSee('Live solution demo');
        }
        $this->get('/demos/not-found')->assertNotFound();
        $this->get('/demos/kirana/../../.env')->assertNotFound();
    }

    public function test_public_demo_visibility_order_and_all_inactive_empty_state(): void
    {
        $this->seed(DemoSeeder::class);
        Demo::where('slug', 'kirana')->update(['name' => 'ZZZFirstDemo', 'sort_order' => 0]);
        Demo::where('slug', 'retail')->update(['name' => 'ZZZSecondDemo', 'sort_order' => 100]);
        Demo::where('slug', 'ai-automation')->update(['name' => 'ZZZHiddenDemo', 'is_active' => false]);
        $this->get('/demos')->assertOk()->assertSeeInOrder(['ZZZFirstDemo', 'ZZZSecondDemo'])->assertDontSee('ZZZHiddenDemo');
        Demo::query()->update(['is_active' => false]);
        $this->get('/demos')->assertOk()->assertSee('No demos are currently available');
        $this->get('/demos/kirana')->assertNotFound();
    }

    public function test_admin_permissions_forms_create_update_and_archive(): void
    {
        $this->get('/admin/demos')->assertRedirect('/admin/login');
        $this->get('/admin/case-studies')->assertNotFound();
        $this->get('/admin/testimonials')->assertNotFound();
        $viewer = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);
        $this->actingAs($viewer)->post('/admin/demos', [])->assertForbidden();
        $this->editor();
        $this->get('/admin/demos')->assertOk();
        $this->get('/admin/demos/create')->assertOk();
        $response = $this->post('/admin/demos', ['name' => 'Admin demo', 'short_description' => 'A sample shop demo', 'badge' => 'CONCEPT', 'is_active' => 1]);
        $demo = Demo::firstOrFail();
        $response->assertSessionHasNoErrors()->assertRedirect('/admin/demos/'.$demo->id.'/edit');
        $this->get('/admin/demos/'.$demo->id.'/edit')->assertOk();
        $this->put('/admin/demos/'.$demo->id, ['name' => 'Changed name', 'short_description' => 'A sample shop demo', 'badge' => 'CONCEPT', 'is_active' => 0])->assertSessionHasNoErrors();
        $this->assertSame('admin-demo', $demo->fresh()->slug);
        $this->assertFalse($demo->fresh()->is_active);
        $this->delete('/admin/demos/'.$demo->id)->assertRedirect();
        $this->assertSoftDeleted($demo);
    }

    public function test_unsafe_urls_duplicate_slugs_invalid_relations_and_executable_uploads_are_rejected(): void
    {
        $this->editor();
        $summary = ['short_description' => 'A sample shop demo'];
        $this->post('/admin/demos', ['name' => 'Unsafe', 'badge' => 'CONCEPT', 'live_url' => 'javascript:alert(1)'] + $summary)->assertSessionHasErrors('live_url');
        Demo::create(['name' => 'Existing', 'slug' => 'existing']);
        $this->post('/admin/demos', ['name' => 'Existing', 'badge' => 'CONCEPT'] + $summary)->assertSessionHasErrors('slug');
        $this->post('/admin/demos', ['name' => 'Relations', 'badge' => 'CONCEPT', 'service_ids' => [999999]] + $summary)->assertSessionHasErrors('service_ids.0');
        $this->post('/admin/demos', ['name' => 'Upload', 'badge' => 'CONCEPT', 'thumbnail_upload' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php')] + $summary)->assertSessionHasErrors('thumbnail_upload');
        Storage::fake('public');
        $this->post('/admin/demos', ['name' => 'Safe upload', 'badge' => 'CONCEPT', 'thumbnail_upload' => UploadedFile::fake()->image('image.png')] + $summary)->assertSessionHasNoErrors();
        $path = Demo::where('slug', 'safe-upload')->firstOrFail()->thumbnail;
        Storage::disk('public')->assertExists(substr($path, strlen('storage/')));
    }

    public function test_removed_case_study_and_testimonial_routes_are_gone(): void
    {
        $this->get('/case-studies')->assertNotFound();
        $this->get('/case-studies/test-fixture')->assertNotFound();
        $this->get('/reviews')->assertNotFound();
        $this->get('/about')->assertOk()->assertDontSee('What partners say.');
    }

    public function test_demo_enquiries_validate_ids_and_keep_archived_references(): void
    {
        $demo = Demo::create(['name' => 'Test demo', 'slug' => 'test-demo', 'is_active' => true]);
        $payload = ['name' => 'Test contact', 'phone' => '9876501234', 'service' => 'Business Solution', 'demo_id' => $demo->id];
        $this->get($demo->enquiryUrl())->assertOk()->assertSee('name="demo_id"', false);
        $this->postJson('/contact', $payload)->assertOk();
        $lead = Lead::firstOrFail();
        $this->assertSame('demo', $lead->source);
        $this->assertSame($demo->id, $lead->demo_id);
        $this->assertStringEndsWith('/demos#test-demo', $lead->source_page);
        $demo->delete();
        $this->assertSame('Test demo', $lead->fresh()->demo->name);
        $this->postJson('/contact', $payload)->assertUnprocessable()->assertJsonValidationErrors('demo_id');
    }

    private function editor(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Editor, 'is_active' => true]));
    }
}
