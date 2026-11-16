<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('career_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_department_id')->constrained()->restrictOnDelete();
            $table->string('title', 190);
            $table->string('slug', 190)->unique();
            $table->string('location', 190);
            $table->string('work_mode', 30);
            $table->string('employment_type', 30);
            $table->unsignedTinyInteger('experience_min')->nullable();
            $table->unsignedTinyInteger('experience_max')->nullable();
            $table->string('compensation_type', 30)->nullable();
            $table->unsignedBigInteger('compensation_min')->nullable();
            $table->unsignedBigInteger('compensation_max')->nullable();
            $table->string('compensation_period', 30)->nullable();
            $table->boolean('show_compensation')->default(false);
            $table->text('summary');
            $table->longText('overview')->nullable();
            $table->longText('responsibilities')->nullable();
            $table->longText('required_skills')->nullable();
            $table->longText('qualifications')->nullable();
            $table->string('status', 30)->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('application_deadline')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('seo_title', 190)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'application_deadline', 'sort_order']);
            $table->index(['employment_type', 'work_mode']);
        });

        Schema::create('career_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_job_id')->constrained()->restrictOnDelete();
            $table->string('name', 190);
            $table->string('email', 190);
            $table->string('phone', 40);
            $table->string('current_location', 190);
            $table->string('experience', 120);
            $table->text('skills');
            $table->string('portfolio_url', 2048)->nullable();
            $table->string('github_url', 2048)->nullable();
            $table->string('linkedin_url', 2048)->nullable();
            $table->string('availability', 190);
            $table->text('message')->nullable();
            $table->string('freelance_preference', 30)->nullable();
            $table->unsignedBigInteger('expected_rate')->nullable();
            $table->string('resume_disk', 40)->default('local');
            $table->string('resume_path', 500);
            $table->string('resume_original_name', 255);
            $table->string('resume_mime', 120);
            $table->unsignedBigInteger('resume_size');
            $table->string('status', 40)->default('new');
            $table->text('internal_notes')->nullable();
            $table->dateTime('consent_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->index(['career_job_id', 'status', 'created_at']);
            $table->index(['email', 'created_at']);
        });

        Schema::create('career_application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_application_id');
            $table->foreign('career_application_id', 'career_app_status_application_fk')->references('id')->on('career_applications')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('careers_heading', 190)->nullable();
            $table->text('careers_introduction')->nullable();
            $table->string('recruitment_email', 190)->nullable();
            $table->text('application_confirmation_text')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['careers_heading', 'careers_introduction', 'recruitment_email', 'application_confirmation_text']);
        });
        Schema::dropIfExists('career_application_status_histories');
        Schema::dropIfExists('career_applications');
        Schema::dropIfExists('career_jobs');
        Schema::dropIfExists('career_departments');
    }
};
