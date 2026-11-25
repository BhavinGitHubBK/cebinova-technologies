@extends('layouts.app')
@section('title', $job->seo_title ?: $job->title.' | Careers at CEBINOVA')
@section('description', $job->seo_description ?: $job->summary)
@if(isset($preview)) @section('robots', 'noindex,nofollow') @endif
@section('content')
@php
    $canApply = $job->acceptsApplications() && !isset($preview);
    $received = session('career_success');
    $sections = ['overview' => 'Role overview', 'responsibilities' => 'What you will do', 'required_skills' => 'Skills you will bring', 'qualifications' => 'Who can apply'];
@endphp
<div class="careers-listing recruitment-job-detail">
    <section class="about-page-hero" aria-labelledby="job-title">
        <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
        <div class="container-wide relative">
            @if(isset($preview))<div class="recruitment-role-preview">Admin preview — this role is not necessarily published.</div>@endif
            <a class="recruitment-back" href="{{ isset($preview) ? route('admin.recruitment.jobs.edit', $job) : route('careers.index') }}">← {{ isset($preview) ? 'Back to admin' : 'All careers' }}</a>
            <div class="recruitment-role-eyebrow">
                <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>{{ $job->department->name }}</p>
                @unless(isset($preview))<span class="recruitment-role-status {{ $canApply ? '' : 'is-closed' }}">{{ $canApply ? 'Now hiring' : 'Applications closed' }}</span>@endunless
            </div>
            <h1 class="about-page-title recruitment-job-title" id="job-title">{{ $job->title }}</h1>
            <p class="about-page-lead">{{ $job->summary }}</p>
            <ul class="about-page-proof" aria-label="Role at a glance">
                <li><span class="about-page-proof-value">{{ $job->location }}</span><span class="about-page-proof-label">Location</span></li>
                <li><span class="about-page-proof-value">{{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$job->employment_type] }}</span><span class="about-page-proof-label">Employment type</span></li>
                <li><span class="about-page-proof-value">{{ \App\Models\CareerJob::WORK_MODES[$job->work_mode] }}</span><span class="about-page-proof-label">Work arrangement</span></li>
                <li><span class="about-page-proof-value">{{ $job->experienceLabel() }}</span><span class="about-page-proof-label">Experience</span></li>
            </ul>
            <div class="recruitment-hero-actions">
                @if($canApply && !$received)<x-button href="{{ route('careers.apply', $job) }}">Apply now <span aria-hidden="true">→</span></x-button>@endif
                <x-button href="{{ route('careers.index') }}" variant="outline">Explore all opportunities</x-button>
            </div>
        </div>
    </section>
    <section class="section-pad-lg recruitment-role-body">
        <div class="container-wide recruitment-detail-grid">
            <div class="recruitment-detail-content">
                <nav class="recruitment-role-nav" aria-label="Job description sections">
                    @foreach($sections as $field => $heading)
                        @if($job->$field)<a href="#role-{{ $field }}">{{ $heading }}</a>@endif
                    @endforeach
                </nav>
                @foreach($sections as $field => $heading)
                    @if($job->$field)
                        <section class="recruitment-role-section" id="role-{{ $field }}" aria-labelledby="role-heading-{{ $field }}">
                            <div class="recruitment-role-section-head">
                                <span class="about-page-icon"><x-mark :name="match($field) { 'overview' => 'briefcase', 'responsibilities' => 'list', 'required_skills' => 'layers', default => 'users' }" /></span>
                                <h2 class="section-title" id="role-heading-{{ $field }}">{{ $heading }}</h2>
                            </div>
                            <div class="career-rich">{!! $job->$field !!}</div>
                        </section>
                    @endif
                @endforeach
                @if($canApply && !$received)
                    <div class="about-page-philosophy recruitment-role-bottom-cta">
                        <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>Your next step</p>
                        <h2 class="about-page-philosophy-title">Bring your skills. Build what comes next.</h2>
                        <p class="about-page-philosophy-text">Interested in this opportunity? Tell us about yourself and share your resume with our recruitment team.</p>
                        <x-button href="{{ route('careers.apply', $job) }}">Apply for this role <span aria-hidden="true">→</span></x-button>
                    </div>
                @endif
            </div>
            <aside class="recruitment-role-sidebar" aria-label="Apply for this role">
                <div class="recruitment-role-apply-panel">
                    <span class="about-page-icon"><x-mark name="briefcase" /></span>
                    <p class="about-page-kicker">Your next chapter</p>
                    <h2>Make your next move.</h2>
                    <p>Join CEBINOVA and help businesses grow through technology.</p>
                    @if($job->compensationLabel())<div class="recruitment-role-compensation"><span>Compensation</span><strong>{{ $job->compensationLabel() }}</strong></div>@endif
                    @if($job->application_deadline)<p class="recruitment-role-deadline"><x-mark name="clock" /> Apply by {{ $job->application_deadline->format('d M Y') }}</p>@endif
                    @if($received)
                        <div class="recruitment-application-notice" role="status" data-career-success data-success-message="{{ $received }}">{{ $received }}</div>
                    @elseif($canApply)
                        <x-button href="{{ route('careers.apply', $job) }}">Apply now <span aria-hidden="true">→</span></x-button>
                        <small>Have your resume ready: PDF, DOC or DOCX, up to 5 MB.</small>
                    @elseif(isset($preview))
                        <div class="recruitment-application-notice">This is an admin preview. Candidate applications are available on the published job page.</div>
                    @else
                        <div class="recruitment-application-notice">This role is no longer accepting applications.</div>
                        <x-button href="{{ route('careers.index') }}" variant="outline">View other opportunities</x-button>
                    @endif
                </div>
                <div class="recruitment-role-next">
                    <h3>What happens next?</h3>
                    <ol>
                        <li><span>1</span><div><strong>Share your profile</strong><p>Send your details and resume through our application form.</p></div></li>
                        <li><span>2</span><div><strong>We review your application</strong><p>Our recruitment team considers your experience and skills.</p></div></li>
                        <li><span>3</span><div><strong>Continue the conversation</strong><p>We will contact you if your profile matches the opportunity.</p></div></li>
                    </ol>
                    <p class="recruitment-role-privacy"><x-mark name="shield" /><span>Your application and resume stay private and are reviewed only by authorized administrators.</span></p>
                </div>
            </aside>
        </div>
    </section>
</div>
@endsection