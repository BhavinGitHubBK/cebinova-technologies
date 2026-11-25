@extends('layouts.admin')
@section('title', 'Application: '.$application->name)
@section('breadcrumb', 'Admin / Recruitment / Applications / Detail')
@section('heading', $application->name)
@section('content')
@php
    $role = $application->job?->title ?? 'General profile: '.$application->area_of_interest;
    $phone = preg_replace('/[^0-9+]/', '', (string) $application->phone);
    $statusClass = match ($application->status) {
        'new' => 'is-new',
        'reviewing', 'shortlisted', 'interview_scheduled' => 'is-follow',
        'selected' => 'is-won',
        'rejected', 'withdrawn' => 'is-off',
        default => '',
    };
    $when = fn ($date) => $date?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? 'Not recorded';
@endphp
<div class="recruitment-review-page">
    <nav class="recruitment-form-nav" aria-label="Recruitment">
        <a href="{{ route('admin.recruitment.jobs.index') }}">Jobs</a>
        <a class="is-current" aria-current="page" href="{{ route('admin.recruitment.applications.index') }}">Applications</a>
        <a href="{{ route('admin.recruitment.departments.index') }}">Departments</a>
        @can('admin.recruitment.manage')<a class="is-create" href="{{ route('admin.recruitment.jobs.create') }}">New job</a>@endcan
    </nav>
    <div class="admin-card recruitment-review-header">
        <div class="recruitment-review-meta">
            <span class="admin-badge {{ $statusClass }}">{{ \App\Models\CareerApplication::STATUSES[$application->status] }}</span>
            <span>Application #{{ $application->id }}</span>
            <span>Applied {{ $when($application->created_at) }}</span>
        </div>
        <div class="recruitment-review-actions">
            <a class="admin-btn" href="{{ route('admin.recruitment.applications.index') }}"><x-mark name="list" class="h-4 w-4" /> All applications</a>
            <a class="admin-btn" href="mailto:{{ $application->email }}"><x-mark name="mail" class="h-4 w-4" /> Email candidate</a>
            @if ($phone)<a class="admin-btn" href="tel:{{ $phone }}"><x-mark name="phone" class="h-4 w-4" /> Call candidate</a>@endif
        </div>
    </div>
    <div class="recruitment-review-layout">
        <div class="recruitment-review-main">
            <article class="admin-card recruitment-review-card">
                <h2>Candidate details</h2>
                <div class="recruitment-applied-role"><span>Applied for</span><h3>{{ $role }}</h3>@if($application->job)<p>{{ $application->job->department?->name }} · {{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$application->job->employment_type] ?? $application->job->employment_type }}</p>@endif</div>
                <dl class="recruitment-review-facts">
                    <div><dt>Email</dt><dd><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></dd></div>
                    <div><dt>Phone</dt><dd>@if($phone)<a href="tel:{{ $phone }}">{{ $application->phone }}</a>@else<span class="recruitment-empty-value">Not provided</span>@endif</dd></div>
                    <div><dt>Current location</dt><dd>{{ $application->current_location }}</dd></div>
                    <div><dt>Experience</dt><dd>{{ $application->experience }}</dd></div>
                    <div><dt>Availability / notice period</dt><dd>{{ $application->availability }}</dd></div>
                    @if ($application->freelance_preference)
                        <div><dt>Engagement preference</dt><dd>{{ ucfirst(str_replace('-', ' ', $application->freelance_preference)) }}</dd></div>
                        <div><dt>Expected rate</dt><dd>{{ $application->expected_rate !== null ? '₹'.number_format($application->expected_rate) : 'Not provided' }}</dd></div>
                    @endif
                </dl>
                <section class="recruitment-review-section"><h3>Relevant skills</h3><p class="recruitment-candidate-text">{{ $application->skills }}</p></section>
                <section class="recruitment-review-section"><h3>Candidate introduction</h3><p class="recruitment-candidate-text {{ blank($application->message) ? 'recruitment-empty-value' : '' }}">{{ $application->message ?: 'No introduction was included with this application.' }}</p></section>
            </article>
            <section class="admin-card recruitment-review-card" aria-labelledby="candidate-location-title">
                <h2 id="candidate-location-title">Submission location</h2>
                <dl class="recruitment-review-facts">
                    <div><dt>Network location (approximate)</dt><dd>{{ $application->detectedPlace() }}</dd></div>
                    <div><dt>IP address</dt><dd>{{ $application->ip_address ?: 'Not recorded' }}</dd></div>
                    <div><dt>Location capture</dt><dd>{{ match($application->location_permission) { 'not_requested' => 'Automatic IP lookup', 'shared' => 'Device location previously shared by candidate', 'denied' => 'Permission not granted', 'unavailable' => 'Device location unavailable', 'not_shared' => 'Device location not shared', default => 'Not captured for this application' } }}</dd></div>
                    <div><dt>Captured with application</dt><dd>{{ $when($application->location_captured_at) }}</dd></div>
                    @if ($application->mapUrl())
                        <div><dt>Browser-reported coordinates</dt><dd>{{ $application->latitude }}, {{ $application->longitude }}</dd></div>
                        <div><dt>Reported accuracy</dt><dd>{{ $application->accuracyLabel() ?: 'Not provided' }}</dd></div>
                    @endif
                </dl>
                @if ($application->mapUrl())<a class="admin-btn" href="{{ $application->mapUrl() }}" target="_blank" rel="noopener noreferrer"><x-mark name="pin" class="h-4 w-4" /> View shared location on map</a>@endif
                <p class="form-hint recruitment-link-note">Network location is estimated from the submission IP and can differ from the candidate's stated location. Device coordinates are shown only when shared with this application.</p>
            </section>
            <section class="admin-card recruitment-review-card" aria-labelledby="candidate-resume-title">
                <h2 id="candidate-resume-title">Resume &amp; professional links</h2>
                <div class="recruitment-resume-document">
                    <span class="recruitment-document-icon"><x-mark name="file" class="h-5 w-5" /></span>
                    <div><strong>{{ $application->resume_original_name }}</strong><span>{{ strtoupper(pathinfo($application->resume_original_name, PATHINFO_EXTENSION)) }} · {{ number_format($application->resume_size / 1024) }} KB</span></div>
                    <a class="admin-btn admin-btn-accent" href="{{ route('admin.recruitment.applications.resume', $application) }}"><x-mark name="file" class="h-4 w-4" /> Download resume</a>
                </div>
                @if ($application->portfolio_url || $application->github_url || $application->linkedin_url)
                    <div class="recruitment-review-links">
                        @foreach (['linkedin_url' => 'LinkedIn', 'portfolio_url' => 'Portfolio', 'github_url' => 'GitHub'] as $field => $label)
                            @if ($application->$field)<a class="admin-btn" target="_blank" rel="noopener noreferrer" href="{{ $application->$field }}">{{ $label }} <span aria-hidden="true">↗</span></a>@endif
                        @endforeach
                    </div>
                @else
                    <p class="form-hint recruitment-link-note">No optional professional links were provided.</p>
                @endif
            </section>
            <details class="admin-card recruitment-record-details">
                <summary>Submission details</summary>
                <dl class="recruitment-review-facts">
                    <div><dt>Consent recorded</dt><dd>{{ $when($application->consent_at) }}</dd></div>
                    <div><dt>IP address</dt><dd>{{ $application->ip_address ?: 'Not recorded' }}</dd></div>
                </dl>
                <p class="form-hint">Candidate information and resumes are available only to authorized administrators.</p>
            </details>
        </div>
        <aside class="recruitment-review-sidebar">
            <section class="admin-card recruitment-review-card">
                <h2>Review application</h2>
                @can('admin.recruitment.manage')
                    <p class="form-hint">Update the stage and keep a clear record of the review.</p>
                    <form class="admin-form recruitment-review-form" method="POST" action="{{ route('admin.recruitment.applications.update', $application) }}" data-skip-ajax="1">
                        @csrf @method('PUT')
                        <div class="admin-field">
                            <label class="admin-label" for="application-status">Status</label>
                            <select class="admin-select" id="application-status" name="status" required @error('status') aria-invalid="true" aria-describedby="application-status-error" @enderror>
                                @foreach (\App\Models\CareerApplication::STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('status', $application->status) === $value)>{{ $label }}</option>@endforeach
                            </select>
                            @error('status')<p class="recruitment-review-error" id="application-status-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="application-internal-notes">Internal notes</label>
                            <textarea class="admin-input" id="application-internal-notes" name="internal_notes" rows="5" maxlength="10000" placeholder="Add observations for the recruitment team." @error('internal_notes') aria-invalid="true" aria-describedby="application-internal-notes-error" @enderror>{{ old('internal_notes', $application->internal_notes) }}</textarea>
                            <p class="form-hint">For the recruitment team. These notes are not sent to the candidate.</p>
                            @error('internal_notes')<p class="recruitment-review-error" id="application-internal-notes-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="admin-field">
                            <label class="admin-label" for="application-status-note">Status change note <span>(optional)</span></label>
                            <textarea class="admin-input" id="application-status-note" name="status_note" rows="3" maxlength="2000" placeholder="Explain this update for the history." @error('status_note') aria-invalid="true" aria-describedby="application-status-note-error" @enderror>{{ old('status_note') }}</textarea>
                            @error('status_note')<p class="recruitment-review-error" id="application-status-note-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-footer"><button class="admin-btn admin-btn-accent" type="submit">Save review</button></div>
                    </form>
                @else
                    <p class="form-hint">You have view-only access to this application.</p>
                    <div class="recruitment-review-section"><h3>Internal notes</h3><p class="recruitment-candidate-text">{{ $application->internal_notes ?: 'No internal notes yet.' }}</p></div>
                @endcan
            </section>
            <section class="admin-card recruitment-review-card">
                <h2>Status history</h2>
                <ol class="recruitment-review-timeline">
                    @forelse ($application->statusHistory as $history)
                        <li>
                            <span class="recruitment-history-status">{{ \App\Models\CareerApplication::STATUSES[$history->to_status] ?? $history->to_status }}</span>
                            <time datetime="{{ $history->created_at?->toIso8601String() }}">{{ $when($history->created_at) }}</time>
                            <span class="recruitment-history-author">{{ $history->user?->name ?? 'System' }}</span>
                            @if ($history->note)<p class="recruitment-candidate-text">{{ $history->note }}</p>@endif
                        </li>
                    @empty
                        <li class="recruitment-history-empty">No status changes have been recorded yet.</li>
                    @endforelse
                </ol>
            </section>
            @can('admin.recruitment.manage')
                <section class="admin-card recruitment-review-delete">
                    <h2>Delete application</h2>
                    <p class="form-hint">Permanently removes this application and its resume. This cannot be undone.</p>
                    <form method="POST" action="{{ route('admin.recruitment.applications.destroy', $application) }}" data-skip-ajax="1">
                        @csrf @method('DELETE')
                        <button class="admin-btn admin-btn-danger" type="submit" data-confirm="Permanently delete this application and resume? This cannot be undone.">Delete application and resume</button>
                    </form>
                </section>
            @endcan
        </aside>
    </div>
</div>
@endsection
