@extends('layouts.admin')
@section('title', $job->exists ? 'Edit job' : 'New job')
@section('heading', $job->exists ? $job->title : 'New job')
@section('breadcrumb', 'Admin / Recruitment / Jobs / '.($job->exists ? 'Edit' : 'Create'))
@section('content')
<div class="recruitment-job-page">
    <nav class="recruitment-form-nav" aria-label="Recruitment">
        <a class="is-current" aria-current="page" href="{{ route('admin.recruitment.jobs.index') }}">Jobs</a>
        <a href="{{ route('admin.recruitment.applications.index') }}">Applications</a>
        <a href="{{ route('admin.recruitment.departments.index') }}">Departments</a>
        <a class="is-create" href="{{ route('admin.recruitment.jobs.create') }}">New job</a>
    </nav>
    @if ($job->exists)
        <div class="recruitment-job-status">
            <span class="recruitment-job-pill is-{{ $job->status }}">{{ \App\Models\CareerJob::STATUSES[$job->status] }}</span>
            <p>{{ $job->location }} · {{ \App\Models\CareerJob::EMPLOYMENT_TYPES[$job->employment_type] }} · {{ \App\Models\CareerJob::WORK_MODES[$job->work_mode] }}</p>
        </div>
        <div class="recruitment-job-actions">
            <a class="admin-btn" target="_blank" rel="noopener" href="{{ route('admin.recruitment.jobs.preview', $job) }}">Preview</a>
            <a class="admin-btn" href="{{ route('admin.recruitment.applications.index', ['job' => $job->id]) }}">View applications</a>
            @foreach (['published' => 'Publish', 'draft' => 'Move to draft', 'closed' => 'Close applications', 'archived' => 'Archive'] as $value => $label)
                @if ($value !== $job->status)
                    <form method="POST" action="{{ route('admin.recruitment.jobs.status', $job) }}" data-skip-ajax="1">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $value }}">
                        <button class="admin-btn {{ $value === 'published' ? 'admin-btn-accent' : '' }}" type="submit">{{ $label }}</button>
                    </form>
                @endif
            @endforeach
        </div>
    @endif
    <form class="admin-card admin-form recruitment-job-form" method="POST" action="{{ $job->exists ? route('admin.recruitment.jobs.update', $job) : route('admin.recruitment.jobs.store') }}">
        @csrf
        @if ($job->exists) @method('PUT') @endif
        <section class="form-block">
            <h2>Position details</h2>
            <p class="form-hint">Name the role and tell candidates where and how they will work.</p>
            <div class="form-grid">
                <div class="admin-field">
                    <label class="admin-label" for="job-title">Job title</label>
                    <input class="admin-input" id="job-title" name="title" maxlength="190" value="{{ old('title', $job->title) }}" required>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-slug">Slug</label>
                    <input class="admin-input" id="job-slug" name="slug" maxlength="190" value="{{ old('slug', $job->slug) }}">
                    <p class="form-hint">Filled from the title if empty. Used in the job page link.</p>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-department">Department</label>
                    <select class="admin-select" id="job-department" name="career_department_id" required>
                        <option value="">Choose department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) old('career_department_id', $job->career_department_id) === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-location">Location</label>
                    <input class="admin-input" id="job-location" name="location" maxlength="190" value="{{ old('location', $job->location) }}" required>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-employment">Employment type</label>
                    <select class="admin-select" id="job-employment" name="employment_type" required>
                        @foreach (\App\Models\CareerJob::EMPLOYMENT_TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('employment_type', $job->employment_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-work-mode">Work mode</label>
                    <select class="admin-select" id="job-work-mode" name="work_mode" required>
                        @foreach (\App\Models\CareerJob::WORK_MODES as $value => $label)
                            <option value="{{ $value }}" @selected(old('work_mode', $job->work_mode) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-experience-min">Minimum experience (years)</label>
                    <input class="admin-input" id="job-experience-min" type="number" min="0" max="60" name="experience_min" value="{{ old('experience_min', $job->experience_min) }}">
                    <p class="form-hint">Use 0 if freshers can apply.</p>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-experience-max">Maximum experience (years)</label>
                    <input class="admin-input" id="job-experience-max" type="number" min="0" max="60" name="experience_max" value="{{ old('experience_max', $job->experience_max) }}">
                    <p class="form-hint">Leave empty if there is no upper limit.</p>
                </div>
                <div class="admin-field is-wide">
                    <label class="admin-label" for="job-summary">Summary</label>
                    <textarea class="admin-input" id="job-summary" name="summary" rows="3" maxlength="2000" required>{{ old('summary', $job->summary) }}</textarea>
                    <p class="form-hint">A short introduction shown on the careers listing.</p>
                </div>
            </div>
        </section>
        <section class="form-block">
            <h2>Role content</h2>
            <p class="form-hint">Describe the work, benefits and requirements. Use the toolbar to format each section.</p>
            <div class="form-grid">
                @foreach (['overview' => 'Overview', 'responsibilities' => 'Responsibilities', 'required_skills' => 'Required skills', 'qualifications' => 'Qualifications'] as $field => $label)
                    <div class="admin-field career-editor-field is-wide">
                        <label class="admin-label" id="job-{{ $field }}-label">{{ $label }}</label>
                        <div class="career-editor-toolbar" role="toolbar" aria-label="{{ $label }} formatting">
                            <button type="button" data-command="bold" aria-label="Bold"><b>B</b></button>
                            <button type="button" data-command="italic" aria-label="Italic"><i>I</i></button>
                            <button type="button" data-command="insertUnorderedList">• List</button>
                            <button type="button" data-command="formatBlock" data-value="h3">Heading</button>
                        </div>
                        <div class="career-editor" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="job-{{ $field }}-label" data-editor="{{ $field }}">{!! \App\Support\SafeHtml::clean(old($field, $job->$field)) !!}</div>
                        <textarea hidden name="{{ $field }}" data-editor-input="{{ $field }}">{{ old($field, $job->$field) }}</textarea>
                    </div>
                @endforeach
            </div>
        </section>
        <section class="form-block">
            <h2>Compensation</h2>
            <p class="form-hint">Add a salary or rate range when available. You control whether it appears publicly.</p>
            <div class="form-grid">
                <div class="admin-field">
                    <label class="admin-label" for="job-compensation-type">Compensation type</label>
                    <select class="admin-select" id="job-compensation-type" name="compensation_type">
                        <option value="">Not specified</option>
                        @foreach (['salary' => 'Salary', 'hourly' => 'Hourly', 'fixed-project' => 'Fixed project'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('compensation_type', $job->compensation_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-compensation-period">Period</label>
                    <select class="admin-select" id="job-compensation-period" name="compensation_period">
                        <option value="">None</option>
                        @foreach (['hour' => 'Hour', 'month' => 'Month', 'year' => 'Year', 'project' => 'Project'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('compensation_period', $job->compensation_period) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-compensation-min">Minimum INR</label>
                    <input class="admin-input" id="job-compensation-min" type="number" min="0" name="compensation_min" value="{{ old('compensation_min', $job->compensation_min) }}">
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-compensation-max">Maximum INR</label>
                    <input class="admin-input" id="job-compensation-max" type="number" min="0" name="compensation_max" value="{{ old('compensation_max', $job->compensation_max) }}">
                </div>
            </div>
            <div class="recruitment-job-picks">
                <label class="recruitment-job-pick"><input type="hidden" name="show_compensation" value="0"><input type="checkbox" name="show_compensation" value="1" @checked(old('show_compensation', $job->show_compensation))><span><strong>Show compensation publicly</strong><small>Display the range on the careers pages.</small></span></label>
            </div>
        </section>
        <section class="form-block">
            <h2>Publishing</h2>
            <p class="form-hint">Set an optional closing date and choose how prominently the role appears.</p>
            <div class="form-grid">
                <div class="admin-field">
                    <label class="admin-label" for="job-deadline">Application deadline</label>
                    <input class="admin-input" id="job-deadline" type="datetime-local" name="application_deadline" value="{{ old('application_deadline', $job->application_deadline?->format('Y-m-d\TH:i')) }}">
                    <p class="form-hint">Leave empty to keep accepting applications while published.</p>
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-sort-order">Display order</label>
                    <input class="admin-input" id="job-sort-order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $job->sort_order ?? 0) }}">
                </div>
            </div>
            <div class="recruitment-job-picks">
                <label class="recruitment-job-pick"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $job->is_featured))><span><strong>Featured opening</strong><small>Highlight this role in the careers listing.</small></span></label>
            </div>
            @unless ($job->exists)<p class="form-hint recruitment-draft-note">New jobs are saved as drafts. Review and publish the role after saving.</p>@endunless
        </section>
        <section class="form-block">
            <h2>Search</h2>
            <p class="form-hint">Optional wording for search engines and shared job links.</p>
            <div class="form-grid">
                <div class="admin-field">
                    <label class="admin-label" for="job-seo-title">SEO title</label>
                    <input class="admin-input" id="job-seo-title" name="seo_title" maxlength="190" value="{{ old('seo_title', $job->seo_title) }}">
                </div>
                <div class="admin-field">
                    <label class="admin-label" for="job-seo-description">SEO description</label>
                    <textarea class="admin-input" id="job-seo-description" name="seo_description" rows="2" maxlength="500">{{ old('seo_description', $job->seo_description) }}</textarea>
                </div>
            </div>
        </section>
        <div class="form-footer">
            <a class="admin-btn" href="{{ route('admin.recruitment.jobs.index') }}">Back to jobs</a>
            <button class="admin-btn admin-btn-accent" type="submit">Save job</button>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.recruitment-job-form [data-editor]').forEach((editor) => {
    const field = editor.closest('.career-editor-field');
    const input = field.querySelector('[data-editor-input]');
    const sync = () => { input.value = editor.innerHTML; };
    editor.addEventListener('input', sync);
    field.querySelectorAll('[data-command]').forEach((button) => {
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            editor.focus();
            document.execCommand(button.dataset.command, false, button.dataset.value || null);
            sync();
        });
    });
    editor.closest('form').addEventListener('submit', sync);
});
</script>
@endpush