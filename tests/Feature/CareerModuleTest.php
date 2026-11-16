<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Controllers\Admin\CareerApplicationController;
use App\Mail\CareerApplicationAcknowledgement;
use App\Mail\CareerApplicationReceived;
use App\Models\CareerApplication;
use App\Models\CareerDepartment;
use App\Models\CareerJob;
use App\Models\User;
use App\Support\SafeHtml;
use Database\Seeders\CareerSampleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sample_jobs_are_draft_and_public_page_has_empty_state(): void
    {
        $this->seed(CareerSampleSeeder::class);
        $this->assertSame(3, CareerJob::where('status', 'draft')->count());
        $this->get('/careers')->assertOk()->assertSee('No positions are open right now.')->assertDontSee('Android Developer');
    }

    public function test_published_jobs_are_filterable_and_closed_or_expired_jobs_reject_applications(): void
    {
        $department = CareerDepartment::create(['name' => 'Engineering', 'slug' => 'engineering', 'is_active' => true]);
        $job = $this->job($department, ['status' => 'published', 'published_at' => now()]);
        $this->get('/careers?department=engineering&employment_type=full-time&work_mode=remote')->assertOk()->assertSee($job->title);
        $this->get(route('careers.apply', $job))->assertOk();
        $job->update(['application_deadline' => now()->subMinute()]);
        $this->get(route('careers.apply', $job))->assertNotFound();
        $job->update(['application_deadline' => null, 'status' => 'closed']);
        $this->get(route('careers.apply', $job))->assertNotFound();
    }

    public function test_application_upload_is_private_validated_and_queues_both_messages(): void
    {
        Storage::fake('local'); Mail::fake();
        $job = $this->job(CareerDepartment::create(['name' => 'Engineering', 'slug' => 'engineering', 'is_active' => true]), ['status' => 'published', 'published_at' => now()]);
        $payload = ['name' => 'Candidate', 'email' => 'candidate@example.com', 'phone' => '+91 98765 43210', 'current_location' => 'Ahmedabad', 'experience' => '3 years', 'skills' => 'Laravel, MySQL', 'availability' => '30 days', 'consent' => '1'];
        $this->post(route('careers.store', $job), $payload + ['resume' => UploadedFile::fake()->create('resume.exe', 20, 'application/octet-stream')])->assertSessionHasErrors('resume');
        $this->post(route('careers.store', $job), $payload + ['resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf')])->assertRedirect(route('careers.show', $job));
        $application = CareerApplication::firstOrFail();
        Storage::disk('local')->assertExists($application->resume_path);
        $this->assertSame('new', $application->status);
        $this->assertNotNull($application->consent_at);
        Mail::assertQueued(CareerApplicationReceived::class);
        Mail::assertQueued(CareerApplicationAcknowledgement::class);
    }

    public function test_admin_permissions_status_history_and_resume_download(): void
    {
        Storage::fake('local');
        $job = $this->job(CareerDepartment::create(['name' => 'Engineering', 'slug' => 'engineering', 'is_active' => true]));
        Storage::disk('local')->put('career-resumes/test.pdf', 'resume');
        $application = CareerApplication::create(['career_job_id' => $job->id, 'name' => 'Candidate', 'email' => 'candidate@example.com', 'phone' => '9876543210', 'current_location' => 'Ahmedabad', 'experience' => '2 years', 'skills' => 'PHP', 'availability' => 'Now', 'resume_path' => 'career-resumes/test.pdf', 'resume_original_name' => 'resume.pdf', 'resume_mime' => 'application/pdf', 'resume_size' => 6, 'status' => 'new', 'consent_at' => now()]);
        $viewer = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);
        $this->actingAs($viewer)->get(route('admin.recruitment.applications.show', $application))->assertOk();
        $this->actingAs($viewer)->put(route('admin.recruitment.applications.update', $application), ['status' => 'shortlisted'])->assertForbidden();
        $editor = User::factory()->create(['role' => UserRole::Editor, 'is_active' => true]);
        $this->actingAs($editor)->put(route('admin.recruitment.applications.update', $application), ['status' => 'shortlisted', 'status_note' => 'Strong profile'])->assertRedirect();
        $this->assertDatabaseHas('career_application_status_histories', ['career_application_id' => $application->id, 'from_status' => 'new', 'to_status' => 'shortlisted']);
        $this->actingAs($editor)->get(route('admin.recruitment.applications.resume', $application))->assertOk();
    }

    public function test_html_and_csv_formula_injection_are_sanitized(): void
    {
        $html = SafeHtml::clean('<script>alert(1)</script><p onclick="bad()">Safe <strong>text</strong></p><a href="javascript:alert(1)">Bad</a>');
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertSame("'=SUM(A1:A2)", app(CareerApplicationController::class)->csvSafe('=SUM(A1:A2)'));
    }

    private function job(CareerDepartment $department, array $attributes = []): CareerJob
    {
        return CareerJob::create(array_merge(['career_department_id' => $department->id, 'title' => 'Laravel Developer', 'slug' => 'laravel-developer', 'location' => 'India', 'work_mode' => 'remote', 'employment_type' => 'full-time', 'summary' => 'Build useful software.', 'status' => 'draft'], $attributes));
    }
}
