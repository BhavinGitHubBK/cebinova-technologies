@extends('layouts.admin')
@section('title', $service->exists ? 'Edit service' : 'Create service')
@section('breadcrumb', 'Admin / Services / Form')
@section('heading', $service->exists ? 'Edit service' : 'Create service')
@section('content')
<form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" class="admin-card admin-form">
    @csrf
    @if($service->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Basics</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="service-name">Name</label>
                <input id="service-name" class="admin-input" name="name" value="{{ old('name', $service->name) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-slug">Slug</label>
                <input id="service-slug" class="admin-input" name="slug" value="{{ old('slug', $service->slug) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-category">Category</label>
                <input id="service-category" class="admin-input" name="category" value="{{ old('category', $service->category) }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-icon">Icon key</label>
                <input id="service-icon" class="admin-input" name="icon" value="{{ old('icon', $service->icon) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-order">Sort order</label>
                <input id="service-order" class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-image">Featured image path/URL</label>
                <input id="service-image" class="admin-input" name="featured_image" value="{{ old('featured_image', $service->featured_image) }}">
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Content</h2>
        <div class="form-grid">
            <div class="admin-field is-wide">
                <label class="admin-label" for="service-short">Short description</label>
                <textarea id="service-short" class="admin-textarea" name="short_description" rows="{{ admin_rows(old('short_description', $service->short_description), 2) }}" required>{{ old('short_description', $service->short_description) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-full">Full description</label>
                <textarea id="service-full" class="admin-textarea" name="full_description" rows="{{ admin_rows(old('full_description', $service->full_description), 6) }}">{{ old('full_description', $service->full_description) }}</textarea>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-features">Features</label>
                <textarea id="service-features" class="admin-textarea" name="features_text" rows="{{ admin_rows(old('features_text', implode("\n", $service->features ?? [])), 6) }}">{{ old('features_text', implode("\n", $service->features ?? [])) }}</textarea>
                <p class="form-hint">One feature per line.</p>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Button and search</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="service-cta">CTA label</label>
                <input id="service-cta" class="admin-input" name="cta_label" value="{{ old('cta_label', $service->cta_label) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-cta-url">CTA URL</label>
                <input id="service-cta-url" class="admin-input" name="cta_url" value="{{ old('cta_url', $service->cta_url) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="service-seo-title">SEO title</label>
                <input id="service-seo-title" class="admin-input" name="seo_title" value="{{ old('seo_title', $service->seo_title) }}">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="service-seo">SEO description</label>
                <textarea id="service-seo" class="admin-textarea" name="seo_description" rows="{{ admin_rows(old('seo_description', $service->seo_description), 2) }}">{{ old('seo_description', $service->seo_description) }}</textarea>
            </div>
        </div>
    </section>

    <div class="form-footer">
        <label class="admin-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $service->is_featured))> Featured</label>
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true))> Active</label>
        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
    </div>
</form>
@endsection
