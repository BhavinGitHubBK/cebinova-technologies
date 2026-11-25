<?php

namespace Tests\Feature;

use App\Models\CareerApplication;
use App\Models\CareerJob;
use Database\Seeders\MarketingExecutiveJobSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_success_confirms_a_saved_application(): void
    {
        Storage::fake('local');
        Mail::fake();
        $job = $this->opening();
        $this->post(route('careers.store', $job), $this->payload(), ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('ok', true)->assertJsonPath('title', 'Application received!')
            ->assertJsonFragment(['message' => 'Thank you for applying to CEBINOVA! Your application and resume have been received. Our recruitment team will review your profile and contact you if there is a suitable match.']);
        $this->assertDatabaseCount('career_applications', 1);
        $application = CareerApplication::firstOrFail();
        $this->assertSame($job->id, $application->career_job_id);
        Storage::disk('local')->assertExists($application->resume_path);
    }

    public function test_validation_returns_plain_language_field_errors_without_saving(): void
    {
        $job = $this->opening();
        $this->post(route('careers.store', $job), ['email' => 'wrong', 'phone' => 'letters', 'resume' => UploadedFile::fake()->create('resume.exe', 1)], ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors(['name', 'email', 'phone', 'current_location', 'experience', 'skills', 'availability', 'resume', 'consent'])
            ->assertJsonPath('errors.email.0', 'Please enter a valid email address, such as name@example.com.')
            ->assertJsonPath('errors.consent.0', 'Please tick the consent box so we can review and store your application.');
        $this->assertDatabaseCount('career_applications', 0);
    }

    public function test_upload_failure_returns_an_inline_resume_error(): void
    {
        $job = $this->opening();
        Storage::shouldReceive('disk')->with('local')->andThrow(new \RuntimeException('Simulated private storage failure'));
        $this->post(route('careers.store', $job), $this->payload(), ['Accept' => 'application/json'])
            ->assertUnprocessable()->assertJsonValidationErrors('resume')
            ->assertJsonPath('errors.resume.0', 'We could not save your resume. Please try again.');
        $this->assertDatabaseCount('career_applications', 0);
    }

    public function test_database_failure_cleans_up_resume_and_returns_an_inline_form_error(): void
    {
        Storage::fake('local');
        $job = $this->opening();
        $dispatcher = CareerApplication::getEventDispatcher();
        CareerApplication::setEventDispatcher(clone $dispatcher);
        CareerApplication::creating(fn () => throw new \RuntimeException('Simulated database failure'));
        try {
            $this->post(route('careers.store', $job), $this->payload(), ['Accept' => 'application/json'])
                ->assertUnprocessable()->assertJsonValidationErrors('application')
                ->assertJsonPath('errors.application.0', 'We could not save your application. Please try submitting it again.');
        } finally {
            CareerApplication::setEventDispatcher($dispatcher);
        }
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('career_applications', 0);
    }

    public function test_mail_failure_does_not_turn_a_saved_application_into_an_error(): void
    {
        Storage::fake('local');
        $job = $this->opening();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Simulated queue failure'));
        $this->post(route('careers.store', $job), $this->payload(), ['Accept' => 'application/json'])
            ->assertCreated()->assertJsonPath('ok', true);
        $this->assertDatabaseCount('career_applications', 1);
    }

    private function opening(): CareerJob
    {
        $this->seed(MarketingExecutiveJobSeeder::class);
        return CareerJob::where('slug', MarketingExecutiveJobSeeder::SLUG)->firstOrFail();
    }

    private function payload(): array
    {
        return ['name' => 'Test Candidate', 'email' => 'candidate@example.com', 'current_location' => 'Ahmedabad', 'experience' => 'Fresher', 'skills' => 'Communication and sales', 'availability' => 'Immediately', 'consent' => '1', 'resume' => UploadedFile::fake()->create('resume.pdf', 10, 'application/pdf')];
    }
}
