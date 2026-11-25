@extends('layouts.app')
@section('title', 'Careers at CEBINOVA Technologies')
@section('description', 'Explore open positions and share your profile with CEBINOVA Technologies in Ahmedabad.')
@section('content')
<div class="careers-listing">
<section class="about-page-hero" aria-labelledby="careers-title">
    <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
    <div class="container-wide relative">
        <div class="about-page-hero-copy" data-reveal>
            <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>Careers at CEBINOVA</p>
            <h1 class="about-page-title" id="careers-title">{{ $settings->careers_heading ?: 'Build your career. Create real impact.' }}</h1>
            <p class="about-page-lead">{{ $settings->careers_introduction ?: 'Bring your skills and ideas to a team building websites, mobile apps, software and automation for real businesses.' }}</p>
            <ul class="about-page-proof" aria-label="Careers at a glance">
                <li><span class="about-page-proof-value">{{ $openCount ?: 'Let’s talk' }}</span><span class="about-page-proof-label">{{ $openCount ? Str::plural('Open position', $openCount) : 'Open to introductions' }}</span></li>
                <li><span class="about-page-proof-value">Ahmedabad</span><span class="about-page-proof-label">Team location</span></li>
                <li><span class="about-page-proof-value">Web &amp; mobile</span><span class="about-page-proof-label">Digital products</span></li>
                <li><span class="about-page-proof-value">Software &amp; automation</span><span class="about-page-proof-label">Business solutions</span></li>
            </ul>
            <div class="recruitment-hero-actions">
                <x-button href="#open-roles" class="group">View open positions <span class="arrow-shift" aria-hidden="true">→</span></x-button>
                <x-button href="{{ route('careers.profile') }}" variant="outline">Introduce yourself <span aria-hidden="true">↗</span></x-button>
            </div>
            @if(collect($workModes)->contains(fn ($mode) => $mode !== 'on-site'))
                <p class="recruitment-work-modes">@foreach($workModes as $mode)@if($mode !== 'on-site')<span>{{ \App\Models\CareerJob::WORK_MODES[$mode] ?? $mode }} roles available</span>@endif @endforeach</p>
            @endif
        </div>
    </div>
</section>
<section class="section-pad-lg bg-white" aria-labelledby="positions-title">
    <div class="container-wide" id="open-roles">
        <div class="about-page-head" data-reveal>
            <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>Open positions</p>
            <h2 class="section-title" id="positions-title">Find your next opportunity.</h2>
            <p class="section-support">Explore the role, the work and what you could bring to the team.</p>
        </div>
        @if($openCount)
        <form class="recruitment-filters" method="GET" action="{{ route('careers.index') }}#open-roles">
            <label>Search roles<input type="search" name="q" value="{{ request('q') }}" maxlength="120" placeholder="Job title or skill"></label>
            @if($departments->count() > 1)<label>Department<select name="department"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->slug }}" @selected(request('department') === $department->slug)>{{ $department->name }}</option>@endforeach</select></label>@endif
            @if(count($employmentTypes) > 1)<label>Employment type<select name="employment_type"><option value="">All types</option>@foreach($employmentTypes as $value)<option value="{{ $value }}" @selected(request('employment_type') === $value)>{{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$value] ?? $value }}</option>@endforeach</select></label>@endif
            @if(count($workModes) > 1)<label>Work arrangement<select name="work_mode"><option value="">All arrangements</option>@foreach($workModes as $value)<option value="{{ $value }}" @selected(request('work_mode') === $value)>{{ \App\Models\CareerJob::WORK_MODES[$value] ?? $value }}</option>@endforeach</select></label>@endif
            <x-button type="submit">Find roles</x-button>@if(request()->hasAny(['q','department','employment_type','work_mode']))<a class="recruitment-clear-filters" href="{{ route('careers.index') }}#open-roles">Clear filters</a>@endif
        </form>
        @error('q')<p role="alert">{{ $message }}</p>@enderror
        <p class="recruitment-results" role="status">{{ $jobs->total() }} {{ Str::plural('position', $jobs->total()) }} found</p>
        @endif
        <div class="recruitment-job-grid">
            @forelse($jobs as $job)
                <article class="about-page-card recruitment-job-card">
                    <div class="recruitment-card-top"><span>{{ $job->department->name }}</span>@if($job->is_featured)<b class="recruitment-featured">Featured</b>@endif</div>
                    <h3 class="about-page-card-title"><a href="{{ route('careers.show', $job) }}">{{ $job->title }}</a></h3>
                    <p class="about-page-card-text">{{ $job->summary }}</p>
                    <ul class="recruitment-job-meta" aria-label="Employment details"><li>{{ $job->location }}</li><li>{{ \App\Models\CareerJob::WORK_MODES[$job->work_mode] }}</li><li>{{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$job->employment_type] }}</li><li>{{ $job->experienceLabel() }}</li>@if($job->compensationLabel())<li>{{ $job->compensationLabel() }}</li>@endif</ul>
                    @if($job->skillTags())<div class="recruitment-skills" aria-label="Relevant skills">@foreach($job->skillTags() as $skill)<span>{{ $skill }}</span>@endforeach</div>@endif
                    <div class="recruitment-role-actions"><a href="{{ route('careers.show', $job) }}">View role <span class="sr-only">: {{ $job->title }}</span><span aria-hidden="true">↗</span></a><x-button href="{{ route('careers.apply', $job) }}">Apply now<span class="sr-only">: {{ $job->title }}</span></x-button></div>
                </article>
            @empty
                <div class="about-page-philosophy recruitment-welcome">
                    <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>{{ $openCount ? 'Try a different search' : 'Good people are always worth knowing' }}</p>
                    <h3 class="about-page-philosophy-title">{{ $openCount ? 'No roles match these filters.' : 'We’re not hiring for a specific role right now, but we’d still love to know you.' }}</h3>
                    <p class="about-page-philosophy-text">{{ $openCount ? 'Clear your filters to explore all current openings.' : 'Share your experience and the kind of work you’re interested in. We’ll keep your profile for future opportunities.' }}</p>
                    <div class="recruitment-welcome-actions">
                        @if($openCount)<x-button href="{{ route('careers.index') }}#open-roles">Clear filters</x-button>@else<x-button href="{{ route('careers.profile') }}">Send your profile <span aria-hidden="true">→</span></x-button>@endif
                        @if($linkedin)<x-button href="{{ $linkedin }}" variant="outline" target="_blank" rel="noopener noreferrer">CEBINOVA on LinkedIn <span aria-hidden="true">↗</span></x-button>@endif
                    </div>
                </div>
            @endforelse
        </div>
        {{ $jobs->links() }}
    </div>
</section>
<section class="about-page-why section-pad-lg" aria-labelledby="careers-values-title">
    <div class="container-wide">
        <div class="about-page-head" data-reveal>
            <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>Why join CEBINOVA</p>
            <h2 class="section-title" id="careers-values-title">Work with purpose. Keep moving forward.</h2>
            <p class="section-support">Our approach is simple: solve useful problems, share what we learn and make room for good ideas.</p>
        </div>
        <div class="recruitment-value-grid" data-stagger>
            <article class="about-page-card"><span class="about-page-icon"><x-mark name="briefcase" class="h-5 w-5" /></span><h3 class="about-page-card-title">Meaningful work</h3><p class="about-page-card-text">Help solve real business problems through useful products and thoughtful technology.</p></article>
            <article class="about-page-card"><span class="about-page-icon"><x-mark name="layers" class="h-5 w-5" /></span><h3 class="about-page-card-title">Learning across technologies</h3><p class="about-page-card-text">Explore how web, mobile, software and automation come together to solve a challenge.</p></article>
            <article class="about-page-card"><span class="about-page-icon"><x-mark name="nodes" class="h-5 w-5" /></span><h3 class="about-page-card-title">Ownership and collaboration</h3><p class="about-page-card-text">Bring care to your work, ask questions and build solutions with others.</p></article>
            <article class="about-page-card"><span class="about-page-icon"><x-mark name="trend" class="h-5 w-5" /></span><h3 class="about-page-card-title">Space for your ideas</h3><p class="about-page-card-text">Strengthen your skills and contribute your perspective as we work through new problems.</p></article>
        </div>
    </div>
</section>
<section class="about-page-journey section-pad-lg bg-white" id="hiring-process" aria-labelledby="careers-process-title">
    <div class="container-wide">
        <div class="about-page-head" data-reveal>
            <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>How we hire</p>
            <h2 class="section-title" id="careers-process-title">A conversation. A clear next step.</h2>
            <p class="section-support">We want to understand your strengths and what you’re looking for. The process may vary by role.</p>
        </div>
        <ol class="recruitment-process-grid" data-stagger>
            <li class="about-page-journey-card"><p class="about-page-journey-step">Step 01</p><h3 class="about-page-journey-title">Apply or share your profile</h3><p class="about-page-card-text">Tell us about your experience, interests and relevant work.</p></li>
            <li class="about-page-journey-card"><p class="about-page-journey-step">Step 02</p><h3 class="about-page-journey-title">Initial conversation</h3><p class="about-page-card-text">Discuss the opportunity, your goals and how we could work together.</p></li>
            <li class="about-page-journey-card"><p class="about-page-journey-step">Step 03</p><h3 class="about-page-journey-title">Role-specific skill discussion</h3><p class="about-page-card-text">Explore the skills and problem-solving approach relevant to the role.</p></li>
            <li class="about-page-journey-card"><p class="about-page-journey-step">Step 04</p><h3 class="about-page-journey-title">Decision and next steps</h3><p class="about-page-card-text">Hear from our team about the outcome and what comes next.</p></li>
        </ol>
        <div class="recruitment-process-actions"><x-button href="{{ route('careers.profile') }}">Introduce yourself <span aria-hidden="true">→</span></x-button></div>
    </div>
</section>
</div>
@endsection
