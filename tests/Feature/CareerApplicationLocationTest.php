<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CareerApplication;
use App\Models\CareerJob;
use App\Models\User;
use Database\Seeders\MarketingExecutiveJobSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_ip_location_is_saved_and_shown_to_admin(): void
    {
        Http::fake(['https://ipwho.is/8.8.8.8' => Http::response(['success' => true, 'city' => 'Ahmedabad', 'region' => 'Gujarat', 'country' => 'India'])]);
        $this->submit([], '8.8.8.8');
        $application = CareerApplication::firstOrFail();
        $this->assertSame('Ahmedabad, Gujarat, India', $application->detectedPlace());
        $this->assertSame('ip', $application->location_source);
        $this->assertSame('not_requested', $application->location_permission);
        $this->assertNull($application->mapUrl());
        $this->assertNotNull($application->location_captured_at);
        $this->actingAs(User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]));
        $this->get(route('admin.recruitment.applications.index'))->assertOk()->assertSeeText('Ahmedabad, Gujarat, India')->assertSeeText('Approximate IP location');
        $this->get(route('admin.recruitment.applications.show', $application))->assertOk()->assertSeeText('Network location (approximate)')->assertSeeText('Ahmedabad, Gujarat, India')->assertSeeText('Automatic IP lookup');
    }

    public function test_client_coordinates_are_ignored_and_only_ip_location_is_recorded(): void
    {
        Http::fake();
        $this->submit(['location_permission' => 'shared', 'location_latitude' => 23.0225, 'location_longitude' => 72.5714, 'location_accuracy' => 30]);
        $application = CareerApplication::firstOrFail();
        $this->assertNull($application->latitude);
        $this->assertNull($application->longitude);
        $this->assertNull($application->accuracyLabel());
        $this->assertNull($application->mapUrl());
        $this->assertSame('not_requested', $application->location_permission);
        $this->assertSame('local_network', $application->location_source);
        Http::assertNothingSent();
        $this->actingAs(User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]));
        $this->get(route('admin.recruitment.applications.show', $application))->assertOk()->assertSeeText('Automatic IP lookup')->assertDontSee('https://www.google.com/maps?q=', false);
    }

    public function test_permission_denial_and_private_ip_do_not_block_applications_or_use_server_location(): void
    {
        Http::fake();
        $this->submit(['location_permission' => 'denied', 'location_city' => 'Forged city']);
        $application = CareerApplication::firstOrFail();
        $this->assertSame('not_requested', $application->location_permission);
        $this->assertSame('Local network', $application->detectedPlace());
        $this->assertNull($application->latitude);
        $this->assertNull($application->longitude);
        $this->assertNull($application->location_city);
        Http::assertNothingSent();
    }

    public function test_lookup_outage_does_not_prevent_a_saved_application(): void
    {
        Http::fake(fn () => throw new \RuntimeException('Lookup unavailable'));
        $this->submit(['location_permission' => 'unavailable'], '8.8.8.8');
        $application = CareerApplication::firstOrFail();
        $this->assertSame('unavailable', $application->location_source);
        $this->assertSame('Location unavailable', $application->detectedPlace());
        $this->assertDatabaseCount('career_applications', 1);
    }

    public function test_location_controls_are_absent_and_unused_client_metadata_does_not_block_submission(): void
    {
        $job = $this->prepareOpening();
        $this->get(route('careers.apply', $job))->assertOk()->assertDontSee('data-share-application-location', false)->assertDontSee('name="location_latitude"', false)->assertSee('"requestLocation":false', false);
        $this->post(route('careers.store', $job), $this->payload() + ['location_permission' => 'shared', 'location_latitude' => 91, 'location_longitude' => 181, 'location_accuracy' => -1], ['Accept' => 'application/json'])->assertCreated();
        $this->assertDatabaseCount('career_applications', 1);
        $this->assertNull(CareerApplication::firstOrFail()->mapUrl());
    }

    private function submit(array $location, string $ip = '127.0.0.1'): void
    {
        $job = $this->prepareOpening();
        $this->withServerVariables(['REMOTE_ADDR' => $ip])->post(route('careers.store', $job), $this->payload() + $location, ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('ok', true);
    }

    private function prepareOpening(): CareerJob
    {
        Storage::fake('local');
        Mail::fake();
        $this->seed(MarketingExecutiveJobSeeder::class);
        return CareerJob::where('slug', MarketingExecutiveJobSeeder::SLUG)->firstOrFail();
    }

    private function payload(): array
    {
        return ['name' => 'Test Candidate', 'email' => 'candidate@example.com', 'current_location' => 'Candidate stated location', 'experience' => 'Fresher', 'skills' => 'Communication', 'availability' => 'Immediately', 'consent' => '1', 'resume' => UploadedFile::fake()->create('resume.pdf', 10, 'application/pdf')];
    }
}
