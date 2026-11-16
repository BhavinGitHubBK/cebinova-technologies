@extends('layouts.app')
@section('title', 'Apply for '.$job->title.' | CEBINOVA Careers')
@section('description', 'Submit your application for '.$job->title.' at CEBINOVA Technologies.')
@section('robots', 'noindex,follow')
@section('content')
<section class="career-application-section"><div class="container-wide career-application-grid">
    <aside><a class="career-back" href="{{ route('careers.show', $job) }}">← Back to role</a><p class="careers-kicker">Application</p><h1>{{ $job->title }}</h1><p>{{ $job->department->name }} · {{ \App\Models\CareerJob::WORK_MODES[$job->work_mode] }} · {{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$job->employment_type] }}</p><div class="career-privacy-note"><strong>Your information stays private.</strong><span>Applications and resumes are available only to authorized CEBINOVA administrators.</span></div></aside>
    <form class="career-application-form" method="POST" action="{{ route('careers.store', $job) }}" enctype="multipart/form-data">@csrf
        <div class="career-form-heading"><span>Candidate details</span><h2>Tell us about yourself.</h2><p>Fields marked * are required.</p></div>
        @if($errors->any())<div class="career-errors" role="alert"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="career-form-grid">
            <label>Name *<input name="name" value="{{ old('name') }}" required autocomplete="name"></label><label>Email *<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
            <label>Phone *<input name="phone" value="{{ old('phone') }}" required autocomplete="tel"></label><label>Current location *<input name="current_location" value="{{ old('current_location') }}" required></label>
            <label>Experience *<input name="experience" value="{{ old('experience') }}" placeholder="Example: 3 years" required></label><label>Availability / notice period *<input name="availability" value="{{ old('availability') }}" placeholder="Example: 30 days" required></label>
            <label class="is-wide">Relevant skills *<textarea name="skills" rows="4" required>{{ old('skills') }}</textarea></label>
            <label>Portfolio URL<input type="url" name="portfolio_url" value="{{ old('portfolio_url') }}"></label><label>GitHub URL<input type="url" name="github_url" value="{{ old('github_url') }}"></label>
            <label>LinkedIn URL<input type="url" name="linkedin_url" value="{{ old('linkedin_url') }}"></label><label>Resume *<input type="file" name="resume" accept=".pdf,.doc,.docx" required><small>PDF, DOC or DOCX. Maximum 5 MB.</small></label>
            @if($job->employment_type === 'freelance')<label>Engagement preference *<select name="freelance_preference" required><option value="">Choose one</option><option value="hourly" @selected(old('freelance_preference')==='hourly')>Hourly</option><option value="fixed-project" @selected(old('freelance_preference')==='fixed-project')>Fixed project</option></select></label><label>Expected rate in INR *<input type="number" min="1" name="expected_rate" value="{{ old('expected_rate') }}" required></label>@endif
            <label class="is-wide">Short message<textarea name="message" rows="5">{{ old('message') }}</textarea></label>
        </div>
        <div class="career-honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
        <label class="career-consent"><input type="checkbox" name="consent" value="1" required @checked(old('consent'))><span>I consent to CEBINOVA storing and processing my application details for recruitment purposes. *</span></label>
        <button type="submit">Submit application <span>→</span></button>
    </form>
</div></section>
@endsection
