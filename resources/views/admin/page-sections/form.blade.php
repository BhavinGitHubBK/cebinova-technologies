@extends('layouts.admin')
@section('title', $section->exists ? 'Edit section' : 'Create section')
@section('breadcrumb', 'Admin / Page sections / Form')
@section('heading', $section->exists ? 'Edit section' : 'Create section')
@section('content')
<form method="POST" action="{{ $section->exists ? route('admin.page-sections.update', $section) : route('admin.page-sections.store') }}" class="admin-card admin-form">
    @csrf
    @if($section->exists) @method('PUT') @endif

    <section class="form-block">
        <h2>Section</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="section-page">Page</label>
                <input id="section-page" class="admin-input" name="page" value="{{ old('page', $section->page ?? 'home') }}" required>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-key">Key</label>
                <input id="section-key" class="admin-input" name="key" value="{{ old('key', $section->key) }}" required placeholder="hero, cta, about...">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-heading">Heading</label>
                <input id="section-heading" class="admin-input" name="heading" value="{{ old('heading', $section->heading) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-subheading">Subheading</label>
                <input id="section-subheading" class="admin-input" name="subheading" value="{{ old('subheading', $section->subheading) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-image">Image path</label>
                <input id="section-image" class="admin-input" name="image_path" value="{{ old('image_path', $section->image_path) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-order">Sort order</label>
                <input id="section-order" class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order ?? 0) }}">
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="section-body">Body</label>
                <textarea id="section-body" class="admin-textarea" name="body" rows="{{ admin_rows(old('body', $section->body), 5) }}">{{ old('body', $section->body) }}</textarea>
            </div>
        </div>
    </section>

    <section class="form-block">
        <h2>Buttons</h2>
        <div class="form-grid">
            <div class="admin-field">
                <label class="admin-label" for="section-cta">CTA label</label>
                <input id="section-cta" class="admin-input" name="cta_label" value="{{ old('cta_label', $section->cta_label) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-cta-url">CTA URL</label>
                <input id="section-cta-url" class="admin-input" name="cta_url" value="{{ old('cta_url', $section->cta_url) }}" placeholder="/contact">
                <p class="form-hint">Site path such as /contact, or a full https link.</p>
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-cta2">Secondary CTA label</label>
                <input id="section-cta2" class="admin-input" name="secondary_cta_label" value="{{ old('secondary_cta_label', $section->secondary_cta_label) }}">
            </div>
            <div class="admin-field">
                <label class="admin-label" for="section-cta2-url">Secondary CTA URL</label>
                <input id="section-cta2-url" class="admin-input" name="secondary_cta_url" value="{{ old('secondary_cta_url', $section->secondary_cta_url) }}" placeholder="/solutions">
                <p class="form-hint">Site path such as /solutions, or a full https link.</p>
            </div>
            <div class="admin-field is-wide">
                <label class="admin-label" for="section-settings">Settings (JSON)</label>
                <textarea id="section-settings" class="admin-textarea" name="settings" rows="{{ admin_rows(old('settings', $section->settings ? json_encode($section->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '{}'), 6) }}">{{ old('settings', $section->settings ? json_encode($section->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '{}') }}</textarea>
                <p class="form-hint">Proof stats show only the first 4. Example proof: {"proof":[{"value":"ONE","label":"Technology partner"}]}</p>
            </div>
        </div>
    </section>

    <div class="form-footer">
        <label class="admin-check"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $section->is_visible ?? true))> Visible</label>
        <button class="admin-btn admin-btn-primary" type="submit">Save</button>
    </div>
</form>
@endsection
