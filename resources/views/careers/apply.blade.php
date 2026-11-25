@extends('layouts.app')
@section('title', ($job ? 'Apply for '.$job->title : 'Introduce yourself').' | CEBINOVA Careers')
@section('description', 'Share your experience with the CEBINOVA recruitment team.')
@section('robots', 'noindex,follow')
@section('content')
<div class="careers-listing recruitment-application-page">
    <section class="about-page-hero" aria-labelledby="application-title">
        <div class="pointer-events-none absolute inset-0 bg-dots opacity-45"></div>
        <div class="container-wide relative">
            <a class="recruitment-back" href="{{ $job ? route('careers.show', $job) : route('careers.index') }}">← Back to careers</a>
            <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>{{ $job ? 'Join our team' : 'Introduce yourself' }}</p>
            <h1 class="about-page-title" id="application-title">Your next chapter starts here.</h1>
            <p class="about-page-lead">{{ $job ? 'Share your experience and take the next step with CEBINOVA Technologies.' : 'Tell us about the work you enjoy and the skills you would bring to our team.' }}</p>
        </div>
    </section>
    <section class="section-pad-lg recruitment-application-section">
        <div class="container-wide recruitment-application-layout">
            <aside class="recruitment-application-sidebar" aria-label="Application overview">
                <div class="about-page-card recruitment-role-summary">
                    <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>{{ $job ? 'You are applying for' : 'Future opportunities' }}</p>
                    <h2 class="about-page-card-title">{{ $job?->title ?? 'A place for your skills and ideas.' }}</h2>
                    @if ($job)
                        <ul class="recruitment-application-facts">
                            <li><x-mark name="briefcase" /><span>{{ $job->department->name }}</span></li>
                            <li><x-mark name="pin" /><span>{{ $job->location }}</span></li>
                            <li><x-mark name="clock" /><span>{{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$job->employment_type] }} · {{ \App\Models\CareerJob::WORK_MODES[$job->work_mode] }}</span></li>
                            <li><x-mark name="users" /><span>{{ $job->experienceLabel() }}</span></li>
                        </ul>
                        <a class="recruitment-role-link" href="{{ route('careers.show', $job) }}">Read the full job description <span aria-hidden="true">↗</span></a>
                    @else
                        <p class="about-page-card-text">Share your profile for future opportunities. We would love to learn about your experience and interests.</p>
                    @endif
                </div>
                <div class="recruitment-application-guidance">
                    <span class="about-page-icon"><x-mark name="shield" /></span>
                    <h3 class="about-page-card-title">Your information stays private.</h3>
                    <p class="about-page-card-text">Applications and resumes are available only to authorized CEBINOVA administrators.</p>
                    <p class="about-page-card-text">Have your resume ready in PDF, DOC or DOCX format. Maximum file size: 5 MB.</p>
                </div>
            </aside>
            @if (session('career_success'))
                <div class="recruitment-candidate-form recruitment-submitted" role="status" data-career-success data-success-title="Profile received!" data-success-message="{{ session('career_success') }}">
                    <span class="about-page-icon"><x-mark name="check" /></span>
                    <p class="about-page-kicker">Profile received</p>
                    <h2 class="section-title">Thank you for reaching out.</h2>
                    <p class="section-support">{{ session('career_success') }}</p>
                    <x-button href="{{ route('careers.index') }}">Explore careers <span aria-hidden="true">→</span></x-button>
                </div>
            @else
                <form class="recruitment-candidate-form" novalidate data-career-application data-careers-url="{{ route('careers.index') }}" method="POST" action="{{ $job ? route('careers.store', $job) : route('careers.profile.store') }}" enctype="multipart/form-data" aria-labelledby="candidate-form-title">
                    @csrf
                    <div class="recruitment-form-heading">
                        <p class="about-page-kicker"><span class="about-page-dot" aria-hidden="true"></span>Candidate details</p>
                        <h2 class="section-title" id="candidate-form-title">Tell us about yourself.</h2>
                        <p class="section-support">We look forward to getting to know you. Fields marked * are required.</p>
                    </div>
                    <div class="recruitment-form-errors" data-application-error-summary role="alert" tabindex="-1" @unless($errors->any()) hidden @endunless><strong>Please review your application.</strong><p data-application-error-message>Correct the highlighted fields below and select your resume again before submitting.</p><ul data-application-error-list>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                    <fieldset class="recruitment-form-section">
                        <legend>Personal details</legend>
                        <div class="recruitment-form-grid">
                            @unless ($job)
                                <label class="is-wide" for="career-area_of_interest">Area of interest *
                                    <input id="career-area_of_interest" name="area_of_interest" maxlength="190" value="{{ old('area_of_interest') }}" placeholder="The kind of work you would like to do" required @error('area_of_interest') aria-describedby="career-area_of_interest-error" aria-invalid="true" @enderror>
                                    <x-career-field-error field="area_of_interest" />
                                </label>
                            @endunless
                            <label for="career-name">Full name *
                                <input id="career-name" name="name" maxlength="190" value="{{ old('name') }}" required autocomplete="name" @error('name') aria-describedby="career-name-error" aria-invalid="true" @enderror>
                                <x-career-field-error field="name" />
                            </label>
                            <label for="career-email">Email *
                                <input type="email" id="career-email" name="email" maxlength="190" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-describedby="career-email-error" aria-invalid="true" @enderror>
                                <x-career-field-error field="email" />
                            </label>
                            <label for="career-phone">Phone <span class="recruitment-optional">(optional)</span>
                                <input type="tel" id="career-phone" name="phone" maxlength="40" value="{{ old('phone') }}" autocomplete="tel" @error('phone') aria-describedby="career-phone-error" aria-invalid="true" @enderror>
                                <x-career-field-error field="phone" />
                            </label>
                            <label for="career-current_location">Current location *
                                <input id="career-current_location" name="current_location" maxlength="190" value="{{ old('current_location') }}" placeholder="City, state" required autocomplete="address-level2" @error('current_location') aria-describedby="career-current_location-error" aria-invalid="true" @enderror>
                                <x-career-field-error field="current_location" />
                            </label>
                        </div>
                    </fieldset>
                    <fieldset class="recruitment-form-section">
                        <legend>Experience &amp; availability</legend>
                        <div class="recruitment-form-grid">
                            <label for="career-experience">Experience *
                                <input id="career-experience" name="experience" maxlength="120" value="{{ old('experience') }}" placeholder="Fresher, internship or years of experience" required @error('experience') aria-describedby="career-experience-error" aria-invalid="true" @enderror>
                                <x-career-field-error field="experience" />
                            </label>
                            <label for="career-availability">Availability / notice period *
                                <input id="career-availability" name="availability" maxlength="190" value="{{ old('availability') }}" placeholder="Immediately, 15 days, 30 days…" required @error('availability') aria-describedby="career-availability-error" aria-invalid="true" @enderror>
                                <x-career-field-error field="availability" />
                            </label>
                            <label class="is-wide" for="career-skills">Relevant skills *
                                <textarea id="career-skills" name="skills" maxlength="3000" rows="4" placeholder="Tell us about the skills and strengths you would bring to this role." required @error('skills') aria-describedby="career-skills-error" aria-invalid="true" @enderror>{{ old('skills') }}</textarea>
                                <x-career-field-error field="skills" />
                            </label>
                            @if ($job?->employment_type === 'freelance')
                                <label for="career-freelance_preference">Engagement preference *
                                    <select id="career-freelance_preference" name="freelance_preference" required @error('freelance_preference') aria-describedby="career-freelance_preference-error" aria-invalid="true" @enderror><option value="">Choose one</option><option value="hourly" @selected(old('freelance_preference') === 'hourly')>Hourly</option><option value="fixed-project" @selected(old('freelance_preference') === 'fixed-project')>Fixed project</option></select>
                                    <x-career-field-error field="freelance_preference" />
                                </label>
                                <label for="career-expected_rate">Expected rate in INR *
                                    <input type="number" min="1" max="100000000" id="career-expected_rate" name="expected_rate" value="{{ old('expected_rate') }}" required @error('expected_rate') aria-describedby="career-expected_rate-error" aria-invalid="true" @enderror>
                                    <x-career-field-error field="expected_rate" />
                                </label>
                            @endif
                        </div>
                    </fieldset>
                    <fieldset class="recruitment-form-section">
                        <legend>Resume &amp; introduction</legend>
                        <div class="recruitment-form-grid">
                            <label class="is-wide recruitment-resume-upload" for="career-resume">
                                <span class="recruitment-upload-heading"><x-mark name="file" /><span>Your resume *</span></span>
                                <span class="recruitment-upload-hint" id="career-resume-help">Choose a PDF, DOC or DOCX file, up to 5 MB.</span>
                                <input type="file" id="career-resume" name="resume" accept=".pdf,.doc,.docx" required aria-describedby="career-resume-help{{ $errors->has('resume') ? ' career-resume-error' : '' }}" @error('resume') aria-invalid="true" @enderror>
                                <x-career-field-error field="resume" />
                            </label>
                            <label class="is-wide" for="career-message">Short introduction <span class="recruitment-optional">(optional)</span>
                                <textarea id="career-message" name="message" maxlength="5000" rows="4" placeholder="What interests you about joining CEBINOVA?" @error('message') aria-describedby="career-message-error" aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                                <x-career-field-error field="message" />
                            </label>
                        </div>
                    </fieldset>
                    <details class="recruitment-links" @if($errors->hasAny(['portfolio_url', 'github_url', 'linkedin_url']) || old('portfolio_url') || old('github_url') || old('linkedin_url')) open @endif>
                        <summary>Professional links <span>(optional)</span></summary>
                        <p>Add a LinkedIn profile, portfolio or other work you would like us to see.</p>
                        <div class="recruitment-form-grid">
                            <label for="career-linkedin_url">LinkedIn URL<input type="url" id="career-linkedin_url" name="linkedin_url" maxlength="2048" value="{{ old('linkedin_url') }}" placeholder="https://linkedin.com/in/your-profile" @error('linkedin_url') aria-describedby="career-linkedin_url-error" aria-invalid="true" @enderror><x-career-field-error field="linkedin_url" /></label>
                            <label for="career-portfolio_url">Portfolio URL<input type="url" id="career-portfolio_url" name="portfolio_url" maxlength="2048" value="{{ old('portfolio_url') }}" placeholder="https://your-portfolio.com" @error('portfolio_url') aria-describedby="career-portfolio_url-error" aria-invalid="true" @enderror><x-career-field-error field="portfolio_url" /></label>
                            <label class="is-wide" for="career-github_url">GitHub URL<input type="url" id="career-github_url" name="github_url" maxlength="2048" value="{{ old('github_url') }}" placeholder="https://github.com/your-profile" @error('github_url') aria-describedby="career-github_url-error" aria-invalid="true" @enderror><x-career-field-error field="github_url" /></label>
                        </div>
                    </details>
                    <div class="career-honeypot" aria-hidden="true"><label>Website<input id="career-website" name="website" tabindex="-1" autocomplete="off"></label></div>
                    @error('website')<p class="recruitment-field-error" role="alert">{{ $message }}</p>@enderror
                    <div class="recruitment-form-footer">
                        <label class="recruitment-consent" for="career-consent"><input type="checkbox" id="career-consent" name="consent" value="1" required @checked(old('consent')) @error('consent') aria-describedby="career-consent-error" aria-invalid="true" @enderror><span>I consent to CEBINOVA storing and processing my application details for recruitment purposes. *</span></label>
                        <x-career-field-error field="consent" />
                        <div class="recruitment-submit-row"><span><x-mark name="shield" /> Your details are shared with our recruitment team.</span><x-button type="submit">{{ $job ? 'Submit application' : 'Send your profile' }} <span aria-hidden="true">→</span></x-button></div>
                    </div>
                </form>
            @endif
        </div>
    </section>
</div>
@endsection
