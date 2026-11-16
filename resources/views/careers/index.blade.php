@extends('layouts.app')
@section('title', 'Careers at CEBINOVA Technologies')
@section('description', 'Explore technology careers and current job openings at CEBINOVA Technologies.')
@section('content')
<section class="careers-hero careers-hero-v2">
    <div class="container-wide careers-hero-grid">
        <div class="careers-hero-copy">
            <p class="careers-kicker">Careers at CEBINOVA</p>
            <h1>{{ $settings->careers_heading ?: 'Build technology that helps businesses grow.' }}</h1>
            <p>{{ $settings->careers_introduction ?: 'Join a practical, ambitious technology team creating websites, software, mobile products and automation for real businesses.' }}</p>
            <div class="careers-hero-actions">
                <a href="#open-roles">View open roles <span>→</span></a>
                <span>Ahmedabad · Remote · Hybrid</span>
            </div>
        </div>
        <div class="careers-visual" aria-label="CEBINOVA careers overview">
            <div class="careers-visual-orbit" aria-hidden="true"><i></i><i></i><i></i></div>
            <div class="careers-visual-core"><x-cebinova-logo variant="compact" tone="dark" /><strong>Build with purpose.</strong><span>Technology · Design · Growth</span></div>
            <div class="careers-open-count"><strong>{{ $jobs->total() }}</strong><span>{{ Str::plural('open role', $jobs->total()) }}</span></div>
            <span class="careers-role-chip is-one">Web & Software</span><span class="careers-role-chip is-two">Mobile</span><span class="careers-role-chip is-three">AI & Automation</span>
        </div>
    </div>
</section>
<section class="careers-list-section">
    <div class="container-wide" id="open-roles">
        @if($jobs->total())
        <form class="career-filters" method="GET" action="{{ route('careers.index') }}">
            <label>Department<select name="department"><option value="">All departments</option>@foreach($departments as $department)<option value="{{ $department->slug }}" @selected(request('department') === $department->slug)>{{ $department->name }}</option>@endforeach</select></label>
            <label>Employment type<select name="employment_type"><option value="">All types</option>@foreach(\App\Models\CareerJob::EMPLOYMENT_TYPES as $value => $label)<option value="{{ $value }}" @selected(request('employment_type') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label>Work mode<select name="work_mode"><option value="">All modes</option>@foreach(\App\Models\CareerJob::WORK_MODES as $value => $label)<option value="{{ $value }}" @selected(request('work_mode') === $value)>{{ $label }}</option>@endforeach</select></label>
            <button type="submit">Find roles</button>@if(request()->hasAny(['department','employment_type','work_mode']))<a href="{{ route('careers.index') }}">Clear</a>@endif
        </form>
        @endif
        <div class="career-section-head"><div><p class="careers-kicker">Open positions</p><h2>Find your next opportunity.</h2></div><p>Every published role includes transparent details about the team, working model and expectations.</p></div>
        <div class="career-job-grid">
            @forelse($jobs as $job)
                <article class="career-job-card {{ $job->is_featured ? 'is-featured' : '' }}">
                    <div class="career-card-top"><span>{{ $job->department->name }}</span>@if($job->is_featured)<b>Featured</b>@endif</div>
                    <h3><a href="{{ route('careers.show', $job) }}">{{ $job->title }}</a></h3><p>{{ $job->summary }}</p>
                    <ul><li>{{ $job->location }}</li><li>{{ \App\Models\CareerJob::WORK_MODES[$job->work_mode] }}</li><li>{{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$job->employment_type] }}</li><li>{{ $job->experienceLabel() }}</li>@if($job->compensationLabel())<li>{{ $job->compensationLabel() }}</li>@endif</ul>
                    <a class="career-card-link" href="{{ route('careers.show', $job) }}">View position <span>→</span></a>
                </article>
            @empty
                <div class="career-empty career-empty-v2"><div class="career-empty-mark" aria-hidden="true"><span></span><span></span><span></span></div><span>Our next opportunity is being shaped</span><h2>No positions are open right now.</h2><p>We hire thoughtfully when the right role and challenge come together. Check back soon, or introduce yourself so we know what you can bring to CEBINOVA.</p><div><a class="career-empty-primary" href="{{ route('contact') }}">Introduce yourself <b>→</b></a><a class="career-empty-secondary" href="{{ config('cebinova.contact.social.linkedin') }}" target="_blank" rel="noopener">Follow on LinkedIn</a></div></div>
            @endforelse
        </div>
        {{ $jobs->links() }}
    </div>
</section>
<section class="careers-values"><div class="container-wide"><div class="career-section-head"><div><p class="careers-kicker">Why CEBINOVA</p><h2>Do meaningful work. Keep growing.</h2></div><p>We are building a team that values clear thinking, useful technology and long-term client impact.</p></div><div class="careers-value-grid">
    <article><span>01</span><h3>Own real outcomes</h3><p>Work directly on products and systems that help businesses operate, sell and grow.</p></article>
    <article><span>02</span><h3>Learn across disciplines</h3><p>Collaborate across web, mobile, software, automation, design and digital growth.</p></article>
    <article><span>03</span><h3>Build with clarity</h3><p>We value thoughtful decisions, clean execution and communication without unnecessary complexity.</p></article>
    <article><span>04</span><h3>Grow with the company</h3><p>Take responsibility early, strengthen your craft and help shape how our team works.</p></article>
</div></div></section>
<section class="careers-process"><div class="container-wide careers-process-grid"><div><p class="careers-kicker">Hiring process</p><h2>Clear steps. Honest conversations.</h2><p>We keep recruitment focused and respectful. The exact process may vary by role, but you will always know what comes next.</p></div><ol><li><span>1</span><div><strong>Application review</strong><p>We review your experience, skills and the work you choose to share.</p></div></li><li><span>2</span><div><strong>Conversation</strong><p>A practical discussion about the role, your goals and how you approach problems.</p></div></li><li><span>3</span><div><strong>Skill discussion</strong><p>A relevant exercise or technical conversation—never unnecessary rounds.</p></div></li><li><span>4</span><div><strong>Decision</strong><p>Clear feedback and next steps from the CEBINOVA team.</p></div></li></ol></div></section>
@endsection
