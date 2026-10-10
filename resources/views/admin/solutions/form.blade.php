@extends('layouts.admin')
@section('title', $solution->exists ? 'Edit solution' : 'Create solution')
@section('breadcrumb', 'Admin / Solutions / Form')
@section('heading', $solution->exists ? 'Edit solution' : 'Create solution')
@php
    $sectionText = function (string $type) use ($solution) {
        $section = $solution->relationLoaded('sections') ? $solution->sections->firstWhere('type', $type) : null;
        return old($type.'_text', $section?->items?->map(fn ($item) => trim($item->title.($item->description ? ' | '.$item->description : '')))->implode("\n"));
    };
@endphp
@section('content')
<form method="POST" action="{{ $solution->exists ? route('admin.solutions.update', $solution) : route('admin.solutions.store') }}" class="admin-card admin-form">
    @csrf
    @if($solution->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Basics</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="solution-name">Name</label>
                <input id="solution-name" class="admin-input" name="name" value="{{ old('name', $solution->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-slug">Slug</label>
                <input id="solution-slug" class="admin-input" name="slug" value="{{ old('slug', $solution->slug) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-category">Category</label>
                <input id="solution-category" class="admin-input" name="category" value="{{ old('category', $solution->category) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-order">Sort order</label>
                <input id="solution-order" class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $solution->sort_order ?? 0) }}">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="solution-short">Short description</label>
                <textarea id="solution-short" class="admin-textarea" name="short_description" rows="{{ admin_rows(old('short_description', $solution->short_description), 2) }}" required>{{ old('short_description', $solution->short_description) }}</textarea>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="solution-description">Description</label>
                <textarea id="solution-description" class="admin-textarea" name="description" rows="{{ admin_rows(old('description', $solution->description), 3) }}">{{ old('description', $solution->description) }}</textarea>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Page content</h2>
        <p class="form-hint">One item per line. Add a note after a | when a line needs one.</p>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="solution-features">Features</label>
                <textarea id="solution-features" class="admin-textarea" name="features_text" rows="{{ admin_rows($sectionText('features'), 5) }}">{{ $sectionText('features') }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-modules">Modules</label>
                <textarea id="solution-modules" class="admin-textarea" name="modules_text" rows="{{ admin_rows($sectionText('modules'), 5) }}">{{ $sectionText('modules') }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-benefits">Benefits</label>
                <textarea id="solution-benefits" class="admin-textarea" name="benefits_text" rows="{{ admin_rows($sectionText('benefits'), 5) }}">{{ $sectionText('benefits') }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-process">Process</label>
                <textarea id="solution-process" class="admin-textarea" name="process_text" rows="{{ admin_rows($sectionText('process'), 5) }}">{{ $sectionText('process') }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-problem">Business problem</label>
                <textarea id="solution-problem" class="admin-textarea" name="business_problem" rows="{{ admin_rows(old('business_problem', $solution->sections->firstWhere('type', 'business_problem')?->content), 4) }}">{{ old('business_problem', $solution->sections->firstWhere('type', 'business_problem')?->content) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="solution-overview">Overview</label>
                <textarea id="solution-overview" class="admin-textarea" name="overview" rows="{{ admin_rows(old('overview', $solution->sections->firstWhere('type', 'overview')?->content), 4) }}">{{ old('overview', $solution->sections->firstWhere('type', 'overview')?->content) }}</textarea>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Links</h2>
        <div class="form-stack">
            <div class="admin-field">
                <span class="admin-label">Services</span>
                <div class="choice-grid">
                    @foreach($services as $service)
                        <label>
                            <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, old('service_ids', $solution->services->pluck('id')->all())))>
                            {{ $service->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="admin-field">
                <span class="admin-label">Industries</span>
                <div class="choice-grid">
                    @foreach($industries as $industry)
                        <label>
                            <input type="checkbox" name="industry_ids[]" value="{{ $industry->id }}" @checked(in_array($industry->id, old('industry_ids', $solution->industries->pluck('id')->all())))>
                            {{ $industry->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <div class="form-footer">
        <label class="admin-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $solution->is_featured))> Featured</label>
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $solution->is_active ?? true))> Active</label>
        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
    </div>
</form>
@endsection
