@extends('layouts.app')
@section('title', $job->seo_title ?: $job->title.' | Careers at CEBINOVA')
@section('description', $job->seo_description ?: $job->summary)
@if(isset($preview)) @section('robots', 'noindex,nofollow') @endif
@section('content')
<section class="career-detail-hero"><div class="container-wide">
    @if(isset($preview))<div class="career-preview-banner">Admin preview — this role is not necessarily published.</div>@endif
    <a class="career-back" href="{{ isset($preview) ? route('admin.recruitment.jobs.edit', $job) : route('careers.index') }}">← {{ isset($preview) ? 'Back to admin' : 'All careers' }}</a>
    <p class="careers-kicker">{{ $job->department->name }}</p><h1>{{ $job->title }}</h1><p>{{ $job->summary }}</p>
    <div class="career-detail-meta"><span>{{ $job->location }}</span><span>{{ \App\Models\CareerJob::WORK_MODES[$job->work_mode] }}</span><span>{{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$job->employment_type] }}</span><span>{{ $job->experienceLabel() }}</span>@if($job->compensationLabel())<span>{{ $job->compensationLabel() }}</span>@endif</div>
</div></section>
<section class="career-detail-section"><div class="container-wide career-detail-grid">
    <article class="career-detail-content">
        @foreach(['overview' => 'Role overview', 'responsibilities' => 'Responsibilities', 'required_skills' => 'Required skills', 'qualifications' => 'Qualifications'] as $field => $heading)
            @if($job->$field)<section><h2>{{ $heading }}</h2><div class="career-rich">{!! $job->$field !!}</div></section>@endif
        @endforeach
    </article>
    <aside class="career-apply-card"><p>Interested in this role?</p><h2>Take the next step.</h2>
        @if($job->application_deadline)<span>Apply by {{ $job->application_deadline->format('d M Y') }}</span>@endif
        @if(session('career_success'))<div class="career-success">{{ session('career_success') }}</div>
        @elseif($job->acceptsApplications() && !isset($preview))<a href="{{ route('careers.apply', $job) }}">Apply now</a>
        @else<div class="career-closed">Applications are currently closed.</div>@endif
        <small>CEBINOVA is committed to reviewing applications fairly and protecting candidate information.</small>
    </aside>
</div></section>
@endsection
