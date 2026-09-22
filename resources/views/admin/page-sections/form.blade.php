@extends('layouts.admin')
@section('title', $section->exists ? 'Edit section' : 'Create section')
@section('breadcrumb', 'Admin / Page sections / Form')
@section('heading', $section->exists ? 'Edit section' : 'Create section')
@section('content')
<form method="POST" action="{{ $section->exists ? route('admin.page-sections.update', $section) : route('admin.page-sections.store') }}" class="admin-card">
@csrf
@if($section->exists) @method('PUT') @endif
<div class="admin-field"><label class="admin-label">Page</label><input class="admin-input" name="page" value="{{ old('page', $section->page ?? 'home') }}" required></div>
<div class="admin-field"><label class="admin-label">Key</label><input class="admin-input" name="key" value="{{ old('key', $section->key) }}" required placeholder="hero, cta, about..."></div>
<div class="admin-field"><label class="admin-label">Heading</label><input class="admin-input" name="heading" value="{{ old('heading', $section->heading) }}"></div>
<div class="admin-field"><label class="admin-label">Subheading</label><input class="admin-input" name="subheading" value="{{ old('subheading', $section->subheading) }}"></div>
<div class="admin-field"><label class="admin-label">Body</label><textarea class="admin-textarea" name="body" rows="5">{{ old('body', $section->body) }}</textarea></div>
<div class="admin-field"><label class="admin-label">Image path</label><input class="admin-input" name="image_path" value="{{ old('image_path', $section->image_path) }}"></div>
<div class="admin-field"><label class="admin-label">CTA label</label><input class="admin-input" name="cta_label" value="{{ old('cta_label', $section->cta_label) }}"></div>
<div class="admin-field"><label class="admin-label">CTA URL</label><input class="admin-input" name="cta_url" value="{{ old('cta_url', $section->cta_url) }}"></div>
<div class="admin-field"><label class="admin-label">Secondary CTA label</label><input class="admin-input" name="secondary_cta_label" value="{{ old('secondary_cta_label', $section->secondary_cta_label) }}"></div>
<div class="admin-field"><label class="admin-label">Secondary CTA URL</label><input class="admin-input" name="secondary_cta_url" value="{{ old('secondary_cta_url', $section->secondary_cta_url) }}"></div>
<div class="admin-field"><label class="admin-label">Sort order</label><input class="admin-input" type="number" name="sort_order" value="{{ old('sort_order', $section->sort_order ?? 0) }}"></div>
<label style="display:flex;gap:.4rem;align-items:center;margin-bottom:.6rem;"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $section->is_visible ?? true))> Visible</label>
<button class="admin-btn admin-btn-primary" type="submit">Save</button>
</form>
@endsection
