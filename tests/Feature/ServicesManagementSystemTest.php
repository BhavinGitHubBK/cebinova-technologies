<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesManagementSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ServiceSeeder::class);
    }

    public function test_services_page_uses_active_stable_database_order(): void
    {
        $web = Service::query()->where('slug', 'web-development')->firstOrFail();
        $commerce = Service::query()->where('slug', 'ecommerce')->firstOrFail();
        $web->update(['sort_order' => 50]);
        $commerce->update(['sort_order' => 1]);
        Service::query()->where('slug', 'digital-growth')->update(['is_active' => false]);

        $response = $this->get(route('services.index'))->assertOk()->assertDontSee('Digital Growth & Marketing');
        $this->assertLessThan(
            strpos($response->getContent(), 'Website Development'),
            strpos($response->getContent(), 'eCommerce Solutions')
        );
    }

    public function test_existing_service_urls_load_and_invalid_or_inactive_slugs_return_404(): void
    {
        foreach (['web-development', 'ecommerce', 'custom-software', 'ai-automation', 'digital-business', 'digital-growth'] as $slug) {
            $this->get(route('services.show', $slug))->assertOk();
        }

        $this->get('/services/not-a-service')->assertNotFound();
        Service::query()->where('slug', 'ecommerce')->update(['is_active' => false]);
        $this->get(route('services.show', 'ecommerce'))->assertNotFound();
    }

    public function test_detail_page_renders_database_features_process_and_faq(): void
    {
        $service = Service::query()->where('slug', 'custom-software')->firstOrFail();
        $features = $service->sections()->where('type', 'features')->firstOrFail();
        $features->items()->delete();
        $features->items()->create(['title' => 'Dynamic CRM', 'is_active' => true]);
        $process = $service->sections()->where('type', 'process')->firstOrFail();
        $process->items()->delete();
        $process->items()->create(['title' => 'Dynamic Discovery', 'description' => 'Map the workflow.', 'is_active' => true]);
        $service->faqs()->delete();
        $service->faqs()->create(['page' => 'service', 'question' => 'Dynamic question?', 'answer' => 'Dynamic answer.', 'is_active' => true]);

        $this->get(route('services.show', $service->slug))
            ->assertOk()
            ->assertSee('Dynamic CRM')
            ->assertSee('Dynamic Discovery')
            ->assertSee('Dynamic question?');
    }

    public function test_admin_authentication_and_authorization_are_enforced(): void
    {
        $this->get(route('admin.services.index'))->assertRedirect(route('admin.login'));
        $viewer = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);
        $this->actingAs($viewer)->post(route('admin.services.store'), [])->assertForbidden();
    }

    public function test_editor_can_create_update_reorder_and_deactivate_service_without_slug_drift(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor, 'is_active' => true]);
        $this->actingAs($editor)->post(route('admin.services.store'), $this->payload())->assertRedirect(route('admin.services.index'));

        $service = Service::query()->where('slug', 'managed-service')->firstOrFail();
        $this->assertDatabaseHas('service_section_items', ['title' => 'Managed feature']);
        $this->assertDatabaseHas('faqs', ['service_id' => $service->id, 'question' => 'Managed question?']);

        $payload = $this->payload(['name' => 'Renamed Managed Service', 'slug' => '', 'sort_order' => 99, 'is_active' => 0]);
        $this->actingAs($editor)->put(route('admin.services.update', $service), $payload)->assertRedirect(route('admin.services.index'));

        $service->refresh();
        $this->assertSame('managed-service', $service->slug);
        $this->assertSame(99, $service->sort_order);
        $this->assertFalse($service->is_active);
    }

    public function test_service_enquiry_uses_server_service_and_stores_snapshot(): void
    {
        $service = Service::query()->where('slug', 'web-development')->firstOrFail();

        $this->postJson(route('contact.store'), [
            'name' => 'Service Customer',
            'phone' => '9876501234',
            'service' => 'Tampered Service',
            'service_id' => $service->id,
        ])->assertOk();

        $lead = Lead::query()->firstOrFail();
        $this->assertSame($service->id, $lead->service_id);
        $this->assertSame('Website Development', $lead->service);
        $this->assertSame('Service', $lead->source);
        $this->assertSame('web-development', $lead->service_snapshot['slug']);

        $service->update(['name' => 'Changed Later']);
        $this->assertSame('Website Development', $lead->fresh()->service_snapshot['name']);
    }

    public function test_inactive_service_cannot_be_submitted(): void
    {
        $service = Service::query()->where('slug', 'web-development')->firstOrFail();
        $service->update(['is_active' => false]);

        $this->postJson(route('contact.store'), [
            'name' => 'Service Customer',
            'phone' => '9876501234',
            'service' => $service->name,
            'service_id' => $service->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('service_id');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Managed Service',
            'slug' => 'managed-service',
            'category' => 'Managed Category',
            'short_description' => 'Managed short description.',
            'full_description' => 'Managed full description.',
            'hero_title' => 'Managed Hero',
            'hero_subtitle' => 'Managed hero description.',
            'features_text' => 'Managed feature',
            'process_title' => 'Managed process',
            'process_text' => 'Managed step | Managed description',
            'faqs_text' => 'Managed question? | Managed answer.',
            'sort_order' => 8,
            'is_featured' => 1,
            'is_active' => 1,
        ], $overrides);
    }
}
