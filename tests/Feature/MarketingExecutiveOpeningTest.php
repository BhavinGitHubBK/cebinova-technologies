<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\CareerApplicationAcknowledgement;
use App\Mail\CareerApplicationReceived;
use App\Models\CareerApplication;
use App\Models\CareerJob;
use App\Models\User;
use Database\Seeders\MarketingExecutiveJobSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketingExecutiveOpeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_is_repeatable_and_preserves_admin_edits_closed_status_and_deletion(): void
    {
        $job = $this->seedOpening();
        $department = $job->department;

        $this->seed(MarketingExecutiveJobSeeder::class);
        $this->assertDatabaseCount('career_jobs', 1);
        $this->assertDatabaseCount('career_departments', 1);

        $job->update([
            'title' => 'Senior Marketing Executive',
            'summary' => 'An administrator revised this opening.',
            'status' => 'closed',
            'is_featured' => false,
            'published_at' => now()->subWeek(),
        ]);
        $department->update(['name' => 'Commercial Team', 'sort_order' => 42, 'is_active' => false]);
        $publishedAt = $job->fresh()->published_at->toDateTimeString();

        $this->seed(MarketingExecutiveJobSeeder::class);

        $job->refresh();
        $department->refresh();
        $this->assertDatabaseCount('career_jobs', 1);
        $this->assertDatabaseCount('career_departments', 1);
        $this->assertSame('Senior Marketing Executive', $job->title);
        $this->assertSame('An administrator revised this opening.', $job->summary);
        $this->assertSame('closed', $job->status);
        $this->assertFalse($job->is_featured);
        $this->assertSame($publishedAt, $job->published_at->toDateTimeString());
        $this->assertSame('Commercial Team', $department->name);
        $this->assertSame(42, $department->sort_order);
        $this->assertFalse($department->is_active);

        $job->delete();
        $this->seed(MarketingExecutiveJobSeeder::class);

        $this->assertDatabaseCount('career_jobs', 1);
        $this->assertDatabaseCount('career_departments', 1);
        $this->assertSoftDeleted('career_jobs', ['id' => $job->id]);
        $this->assertSame('closed', CareerJob::withTrashed()->findOrFail($job->id)->status);
        $this->assertSame(0, CareerJob::where('slug', MarketingExecutiveJobSeeder::SLUG)->count());
    }

    public function test_the_published_opening_displays_the_supplied_post_and_application_links(): void
    {
        $job = $this->seedOpening();

        $this->assertSame('Marketing Executive / Business Development Executive', $job->title);
        $this->assertSame('Sales & Marketing', $job->department->name);
        $this->assertSame('sales-marketing', $job->department->slug);
        $this->assertSame('Ahmedabad, Gujarat', $job->location);
        $this->assertSame('on-site', $job->work_mode);
        $this->assertSame('full-time', $job->employment_type);
        $this->assertSame(0, $job->experience_min);
        $this->assertNull($job->experience_max);
        $this->assertSame('published', $job->status);
        $this->assertTrue($job->is_featured);
        $this->assertTrue($job->acceptsApplications());
        $this->assertNull($job->application_deadline);
        $this->assertNull($job->compensation_min);
        $this->assertNull($job->compensation_max);
        $this->assertFalse($job->show_compensation);

        $this->get(route('careers.index'))
            ->assertOk()
            ->assertSeeText($job->title)
            ->assertSee('href="'.route('careers.show', $job).'"', false)
            ->assertSee('href="'.route('careers.apply', $job).'"', false);

        $response = $this->get(route('careers.show', $job))
            ->assertOk()
            ->assertSeeText($job->title)
            ->assertSeeText('Ahmedabad, Gujarat')
            ->assertSee('href="'.route('careers.apply', $job).'"', false);

        foreach ([
            'Generate new business leads and potential clients.',
            'Promote our IT services and digital solutions.',
            'Meet potential customers and understand their requirements.',
            'Build strong relationships with clients.',
            'Achieve monthly sales and marketing targets.',
            'Identify new business opportunities.',
            'Fixed salary + performance-based incentives.',
            'Attractive incentives on achieving assigned targets.',
            'Petrol allowance for official travel.',
            'Career growth opportunities.',
            'Supportive and professional work environment.',
            'Good communication and negotiation skills.',
            'Passionate about sales and marketing.',
            'Self-motivated and target-oriented.',
            'Basic understanding of IT services (preferred).',
            'Freshers and experienced candidates are welcome.',
            'Willingness to travel for client meetings.',
        ] as $detail) {
            $response->assertSeeText($detail);
        }

        $this->get(route('careers.apply', $job))
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee('action="'.route('careers.store', $job).'"', false)
            ->assertSee('name="resume"', false);
    }

    public function test_a_public_application_reaches_the_job_filtered_admin_list_with_a_private_pdf_resume(): void
    {
        Storage::fake('local');
        Mail::fake();
        $job = $this->seedOpening();
        $pdf = $this->resumePdf();
        $payload = [
            'name' => 'Aarav Shah',
            'email' => 'aarav.shah@example.com',
            'phone' => '+91 98765 43210',
            'current_location' => 'Ahmedabad, Gujarat',
            'experience' => 'Fresher with a business development internship',
            'skills' => 'Lead generation, client communication, negotiation and CRM',
            'availability' => 'Available immediately',
            'message' => 'I am interested in client meetings and helping businesses adopt IT services.',
            'consent' => '1',
            'resume' => UploadedFile::fake()->createWithContent('aarav-shah-resume.pdf', $pdf),
        ];

        $this->post(route('careers.store', $job), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('careers.show', $job))
            ->assertSessionHas('career_success');

        $this->assertDatabaseCount('career_applications', 1);
        $application = CareerApplication::firstOrFail();
        $this->assertSame($job->id, $application->career_job_id);
        $this->assertSame($payload['name'], $application->name);
        $this->assertSame($payload['email'], $application->email);
        $this->assertSame($payload['skills'], $application->skills);
        $this->assertSame('new', $application->status);
        $this->assertNotNull($application->consent_at);
        $this->assertSame('local', $application->resume_disk);
        $this->assertSame('aarav-shah-resume.pdf', $application->resume_original_name);
        $this->assertSame('application/pdf', $application->resume_mime);
        $this->assertSame(strlen($pdf), $application->resume_size);
        $this->assertStringStartsWith('career-resumes/', $application->resume_path);
        $this->assertStringNotContainsString(public_path(), Storage::disk('local')->path($application->resume_path));
        Storage::disk('local')->assertExists($application->resume_path);
        $this->assertSame($pdf, Storage::disk('local')->get($application->resume_path));
        $this->assertSame(1, $application->statusHistory()->count());
        $this->assertDatabaseHas('career_application_status_histories', [
            'career_application_id' => $application->id,
            'changed_by' => null,
            'from_status' => null,
            'to_status' => 'new',
            'note' => 'Application submitted',
        ]);
        Mail::assertQueued(CareerApplicationReceived::class);
        Mail::assertQueued(CareerApplicationAcknowledgement::class, fn ($mail) => $mail->hasTo($payload['email']));

        $this->get(route('admin.recruitment.applications.resume', $application))
            ->assertRedirect(route('admin.login'));

        // A general profile must not leak into the list filtered to this opening.
        $unrelated = $application->replicate();
        $unrelated->fill([
            'career_job_id' => null,
            'area_of_interest' => 'Graphic design',
            'name' => 'Unrelated Candidate',
            'email' => 'unrelated@example.com',
        ])->save();

        $viewer = User::factory()->create(['role' => UserRole::Viewer, 'is_active' => true]);
        $this->actingAs($viewer)
            ->get(route('admin.recruitment.applications.index', ['job' => $job->id]))
            ->assertOk()
            ->assertSeeText($payload['name'])
            ->assertSeeText($payload['email'])
            ->assertSeeText($job->title)
            ->assertSee('href="'.route('admin.recruitment.applications.show', $application).'"', false)
            ->assertDontSeeText('Unrelated Candidate');

        $this->get(route('admin.recruitment.applications.show', $application))
            ->assertOk()
            ->assertSeeText($payload['name'])
            ->assertSeeText($payload['email'])
            ->assertSeeText($payload['skills'])
            ->assertSeeText($payload['message'])
            ->assertSeeText($job->title)
            ->assertSeeText('Application submitted')
            ->assertSee('href="'.route('admin.recruitment.applications.resume', $application).'"', false);

        $download = $this->get(route('admin.recruitment.applications.resume', $application))
            ->assertOk()
            ->assertDownload('aarav-shah-resume.pdf')
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame($pdf, $download->streamedContent());
    }

    private function seedOpening(): CareerJob
    {
        $this->seed(MarketingExecutiveJobSeeder::class);

        return CareerJob::where('slug', MarketingExecutiveJobSeeder::SLUG)->firstOrFail();
    }

    private function resumePdf(): string
    {
        $stream = "BT\n/F1 14 Tf\n72 740 Td\n(Aarav Shah - Sales and Business Development) Tj\n"
            ."0 -24 Td\n/F1 11 Tf\n(Email: aarav.shah@example.com | Ahmedabad, Gujarat) Tj\n"
            ."0 -24 Td\n(BBA graduate with a business development internship.) Tj\n"
            ."0 -20 Td\n(Skills: lead generation, CRM and client communication.) Tj\nET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF\n";
    }
}
